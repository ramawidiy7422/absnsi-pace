<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Admin\ApprovalSayaController;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Karyawan;
use App\Models\Leaverequest;
use App\Models\CutiSaldo;
use App\Models\AuditLog;
use App\Helpers\NotifHelper;
use App\Notifications\CutiPengajuanNotification;
use App\Notifications\CutiStatusNotification;
use App\Traits\HasApprovalStages;
use Illuminate\Http\Request;
use App\Exports\LeaverequestExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LeaverequestController extends Controller
{
    use HasApprovalStages;

    /** Urutan tahap approval: status pending saat ini => [field approver di department, status berikutnya] */
    private const STAGE_FLOW = [
        'pending_spv'     => ['stage' => 'spv',     'field' => 'spv_user_id',     'next' => 'pending_manajer'],
        'pending_manajer' => ['stage' => 'manajer', 'field' => 'manager_user_id', 'next' => 'pending_hrd'],
        'pending_hrd'     => ['stage' => 'hrd',     'field' => null,              'next' => 'disetujui'],
    ];

    public function index(Request $request)
    {
        $query = Leaverequest::with('karyawan.department', 'approver');

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null) {
            $query->whereHas('karyawan', fn($q) => $q->whereIn('department_id', $deptIds ?: [0]));
        }

        if ($request->search) {
            $query->whereHas('karyawan', fn($q) => $q->where('nama', 'like', "%{$request->search}%")
                ->orWhere('nik', 'like', "%{$request->search}%"));
        }
        if ($request->status) $query->where('status', $request->status);
        if ($request->type)   $query->where('type', $request->type);
        if ($request->bulan)  $query->whereMonth('start_date', $request->bulan);

        $leaverequests = $query->latest()->paginate(15)->withQueryString();
        return view('admin.leaverequest.index', compact('leaverequests'));
    }

    public function detail(Leaverequest $leaverequest)
    {
        $leaverequest->load('karyawan.department', 'approver', 'spvApprover', 'manajerApprover', 'hrdApprover');
        $k      = $leaverequest->karyawan;
        $saldo  = CutiSaldo::getSaldo($k->id);
        $durasi = $leaverequest->start_date->diffInDays($leaverequest->end_date) + 1;

        return response()->json([
            'id'            => $leaverequest->id,
            'type'          => $leaverequest->type,
            'start_date'    => $leaverequest->start_date->format('d/m/Y'),
            'end_date'      => $leaverequest->end_date->format('d/m/Y'),
            'durasi'        => $durasi,
            'keterangan'    => $leaverequest->keterangan,
            'status'        => $leaverequest->status,
            'current_stage' => $leaverequest->currentStage(),
            'rejected_stage'=> $leaverequest->rejected_stage,
            'boleh_approve' => !$leaverequest->isFinal() && auth()->user()->can('approve-leaverequest', $leaverequest),
            'stage_progress'=> [
                ['label' => 'SPV',     'by' => $leaverequest->spvApprover?->name,     'at' => $leaverequest->spv_at?->format('d/m/Y H:i'),     'note' => $leaverequest->spv_note],
                ['label' => 'Manajer', 'by' => $leaverequest->manajerApprover?->name, 'at' => $leaverequest->manajer_at?->format('d/m/Y H:i'), 'note' => $leaverequest->manajer_note],
                ['label' => 'HRD',     'by' => $leaverequest->hrdApprover?->name,     'at' => $leaverequest->hrd_at?->format('d/m/Y H:i'),     'note' => $leaverequest->hrd_note],
            ],
            'approved_by'=> $leaverequest->approver?->name,
            'approved_at'=> $leaverequest->approved_at?->format('d/m/Y H:i'),
            'karyawan'   => [
                'id'         => $k->id,
                'nama'       => $k->nama,
                'nik'        => $k->nik,
                'jabatan'    => $k->jabatan,
                'departemen' => $k->department?->name ?? $k->departemen,
                'status'     => $k->status,
                'foto'       => $k->foto_wajah ? asset('storage/'.$k->foto_wajah) : null,
            ],
            'saldo'      => [
                'jatah'    => $saldo->jatah,
                'terpakai' => $saldo->terpakai,
                'sisa'     => $saldo->sisa,
            ],
        ]);
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|in:cuti,izin,sakit',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Validasi overlap: tidak boleh ada pengajuan aktif (menunggu/disetujui) di rentang yang sama
        $overlap = $karyawan->leaverequests()
            ->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd', 'disetujui'])
            ->where('start_date', '<=', $request->end_date)
            ->where('end_date', '>=', $request->start_date)
            ->first();
        if ($overlap) {
            return back()->withInput()->with('error',
                "Sudah ada pengajuan {$overlap->type} ({$overlap->start_date->format('d/m/Y')} – {$overlap->end_date->format('d/m/Y')}) yang masih aktif di rentang tanggal tersebut."
            );
        }

        // Cek bentrok lintas jenis pengajuan (izin/FMK) di rentang tanggal yang sama
        $bentrok = $this->cekBentrokPengajuan($karyawan->id, Carbon::parse($request->start_date), Carbon::parse($request->end_date));
        if ($bentrok) {
            return back()->withInput()->with('error', "Karyawan sudah punya pengajuan lain yang aktif di rentang tanggal tersebut: {$bentrok}.");
        }

        // Cek saldo cuti jika tipe = cuti — dicek PER TAHUN KALENDER supaya cuti
        // yang menyeberang tahun (mis. 29 Des–3 Jan) divalidasi terhadap jatah
        // masing-masing tahun, bukan cuma jatah tahun berjalan saat submit.
        if ($request->type === 'cuti') {
            $mulai = Carbon::parse($request->start_date);
            $akhir = Carbon::parse($request->end_date);
            $saldoSekarang = CutiSaldo::getSaldo($karyawan->id);
            if ($saldoSekarang->jatah === 0) {
                $masuk = $karyawan->tanggal_masuk;
                $info  = $masuk ? "Tanggal masuk: {$masuk->format('d/m/Y')}. Berhak cuti setelah {$masuk->addYear()->format('d/m/Y')}." : '';
                return back()->withInput()->with('error', "Karyawan belum berhak mengambil cuti (masa kerja belum 1 tahun). {$info}");
            }
            foreach ($this->hitungHariKerjaPerTahun($mulai, $akhir) as $tahun => $hari) {
                $saldoTahun = CutiSaldo::getSaldo($karyawan->id, $tahun);
                if ($saldoTahun->sisa < $hari) {
                    return back()->withInput()->with('error', "Saldo cuti tahun {$tahun} tidak cukup. Sisa: {$saldoTahun->sisa} hari, dibutuhkan: {$hari} hari.");
                }
            }
        }

        $lr = $karyawan->leaverequests()->create(array_merge(
            $request->only('start_date', 'end_date', 'type', 'keterangan'),
            // Cuti tahunan wajib berjenjang SPV->Manajer->HRD; Izin/Sakit langsung ke HRD.
            ['status' => $request->type === 'cuti' ? $this->tentukanStatusAwal($karyawan) : 'pending_hrd']
        ));

        $lr->load('karyawan.department');
        AuditLog::catat('create', "Pengajuan {$lr->type} karyawan {$karyawan->nama} ({$lr->start_date->format('d/m/Y')} - {$lr->end_date->format('d/m/Y')})", $lr);
        $this->notifikasiTahapAktif($lr);

        ApprovalSayaController::invalidasiCache($karyawan->department);

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Pengajuan cuti/izin berhasil ditambahkan!')
                         ->withFragment('tab-ketidakhadiran');
    }

    public function update(Request $request, Leaverequest $leaverequest)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|in:cuti,izin,sakit',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Jika sudah disetujui dan range/type berubah, hapus absensi lama lalu buat ulang
        if ($leaverequest->status === 'disetujui') {
            $this->hapusAbsensiOtomatis($leaverequest);

            // Kembalikan saldo lama dulu (akan dihitung ulang) — per tahun kalender
            if ($leaverequest->type === 'cuti') {
                $this->kembalikanSaldoCutiPerTahun($leaverequest->karyawan_id, $leaverequest->start_date, $leaverequest->end_date);
            }

            $leaverequest->update($request->only('start_date', 'end_date', 'type', 'keterangan'));

            // Buat absensi baru dengan data yang diupdate
            $jumlahDibuat = $this->buatAbsensiOtomatis($leaverequest);

            // Potong saldo baru — per tahun kalender
            if ($leaverequest->type === 'cuti') {
                $this->potongSaldoCutiPerTahun($leaverequest->karyawan_id, $leaverequest->start_date, $leaverequest->end_date);
            }

            AuditLog::catat('update', "Update {$leaverequest->type} yang sudah disetujui, buat ulang {$jumlahDibuat} absensi", $leaverequest);
        } else {
            $leaverequest->update($request->only('start_date', 'end_date', 'type', 'keterangan'));
        }

        return redirect()->route('admin.karyawan.show', $leaverequest->karyawan_id)
                         ->with('success', 'Pengajuan cuti/izin berhasil diupdate!')
                         ->withFragment('tab-ketidakhadiran');
    }

    public function destroy(Leaverequest $leaverequest)
    {
        // Kembalikan saldo dan hapus absensi otomatis jika sudah approved
        if ($leaverequest->status === 'disetujui') {
            $this->hapusAbsensiOtomatis($leaverequest);

            if ($leaverequest->type === 'cuti') {
                $this->kembalikanSaldoCutiPerTahun($leaverequest->karyawan_id, $leaverequest->start_date, $leaverequest->end_date);
            }
        }

        $karyawanId = $leaverequest->karyawan_id;
        AuditLog::catat('delete', "Hapus pengajuan {$leaverequest->type}", $leaverequest);
        $leaverequest->delete();

        ApprovalSayaController::invalidasiCache($leaverequest->karyawan?->department);

        return redirect()->route('admin.karyawan.show', $karyawanId)
                         ->with('success', 'Pengajuan cuti/izin berhasil dihapus!')
                         ->withFragment('tab-ketidakhadiran');
    }

    public function approve(Request $request, Leaverequest $leaverequest)
    {
        $user = auth()->user();
        $note = $request->input('note');

        try {
            $jumlahDibuat = DB::transaction(function () use ($leaverequest, $user, $note) {
                // Lock row supaya 2 klik approve bersamaan (double-submit/network retry) tidak dobel-eksekusi efek final.
                $lr = Leaverequest::where('id', $leaverequest->id)->lockForUpdate()->first();
                if ($lr->isFinal() || Gate::denies('approve-leaverequest', $lr)) {
                    return null;
                }
                return $this->approveSatu($lr, $user, $note);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($jumlahDibuat === null) {
            return back()->with('error', 'Pengajuan ini sudah diproses atau Anda bukan approver untuk tahap ini.');
        }

        $leaverequest->refresh();

        $redirectBack = request()->has('from_karyawan')
            ? redirect()->route('admin.karyawan.show', $leaverequest->karyawan_id)->withFragment('tab-ketidakhadiran')
            : redirect()->route('admin.leaverequest.index');

        if ($leaverequest->status === 'disetujui') {
            $msg = 'Pengajuan cuti/izin berhasil disetujui!';
            if ($jumlahDibuat > 0) {
                $msg .= " ({$jumlahDibuat} hari kerja dicatat otomatis di absensi)";
            }
        } else {
            $msg = 'Disetujui di tahap ini, diteruskan ke tahap approval berikutnya.';
        }

        ApprovalSayaController::invalidasiCache($leaverequest->karyawan?->department);

        return $redirectBack->with('success', $msg);
    }

    public function reject(Request $request, Leaverequest $leaverequest)
    {
        $user = auth()->user();
        $note = $request->input('note');

        $ditolak = DB::transaction(function () use ($leaverequest, $user, $note) {
            $lr = Leaverequest::where('id', $leaverequest->id)->lockForUpdate()->first();
            if ($lr->isFinal() || Gate::denies('approve-leaverequest', $lr)) {
                return false;
            }
            $this->rejectSatu($lr, $user, $note);
            return true;
        });

        if (!$ditolak) {
            return back()->with('error', 'Pengajuan ini sudah diproses atau Anda bukan approver untuk tahap ini.');
        }

        $redirectBack = request()->has('from_karyawan')
            ? redirect()->route('admin.karyawan.show', $leaverequest->karyawan_id)->withFragment('tab-ketidakhadiran')
            : redirect()->route('admin.leaverequest.index');

        ApprovalSayaController::invalidasiCache($leaverequest->karyawan?->department);

        return $redirectBack->with('success', 'Pengajuan cuti/izin ditolak.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $user = auth()->user();
        $leaverequests = Leaverequest::with('karyawan.department')
            ->whereIn('id', $ids)
            ->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd'])
            ->get();
        $count = 0;
        $skipped = 0;
        $totalAbsensi = 0;

        foreach ($leaverequests as $lr) {
            try {
                $jumlahDibuat = DB::transaction(function () use ($lr, $user) {
                    $locked = Leaverequest::where('id', $lr->id)->lockForUpdate()->first();
                    $locked->setRelation('karyawan', $lr->karyawan);
                    if ($locked->isFinal() || Gate::denies('approve-leaverequest', $locked)) {
                        return null;
                    }
                    return $this->approveSatu($locked, $user, null);
                });
            } catch (\RuntimeException $e) {
                $jumlahDibuat = null;
            }

            if ($jumlahDibuat === null) {
                $skipped++;
                continue;
            }
            $totalAbsensi += $jumlahDibuat;
            $count++;
        }

        AuditLog::catat('approve', "Bulk approve {$count} pengajuan cuti/izin, {$totalAbsensi} absensi dibuat otomatis, {$skipped} dilewati (bukan approver tahap ini)");
        $msg = "{$count} pengajuan berhasil diproses. {$totalAbsensi} hari kerja dicatat di absensi.";
        if ($skipped > 0) $msg .= " {$skipped} dilewati karena Anda bukan approver tahap tersebut.";

        if ($count > 0) {
            ApprovalSayaController::invalidasiCache();
        }

        return back()->with('success', $msg);
    }

    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $user = auth()->user();
        $leaverequests = Leaverequest::with('karyawan.department')
            ->whereIn('id', $ids)
            ->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd'])
            ->get();
        $count = 0;
        $skipped = 0;

        foreach ($leaverequests as $lr) {
            $ditolak = DB::transaction(function () use ($lr, $user) {
                $locked = Leaverequest::where('id', $lr->id)->lockForUpdate()->first();
                $locked->setRelation('karyawan', $lr->karyawan);
                if ($locked->isFinal() || Gate::denies('approve-leaverequest', $locked)) {
                    return false;
                }
                $this->rejectSatu($locked, $user, null);
                return true;
            });

            if (!$ditolak) {
                $skipped++;
                continue;
            }
            $count++;
        }

        AuditLog::catat('reject', "Bulk reject {$count} pengajuan cuti/izin, {$skipped} dilewati (bukan approver tahap ini)");
        $msg = "{$count} pengajuan berhasil ditolak.";
        if ($skipped > 0) $msg .= " {$skipped} dilewati karena Anda bukan approver tahap tersebut.";

        if ($count > 0) {
            ApprovalSayaController::invalidasiCache();
        }

        return back()->with('success', $msg);
    }

    // ── HELPER: State machine approval berjenjang (SPV → Manajer → HRD) ───────

    /** Status awal saat submit, otomatis skip tahap yang approver-nya belum diisi admin */
    private function tentukanStatusAwal(Karyawan $karyawan): string
    {
        return $this->skipJikaKosongBerjenjang('pending_spv', $karyawan, self::STAGE_FLOW);
    }

    /** Setujui satu tahap; jika tahap terakhir (HRD), jalankan efek final. Return jumlah absensi dibuat (0 jika belum final). */
    private function approveSatu(Leaverequest $lr, $user, ?string $note): int
    {
        $stage = $lr->currentStage();

        $lr->update([
            "{$stage}_by"   => $user->id,
            "{$stage}_at"   => now(),
            "{$stage}_note" => $note,
        ]);

        $nextStatus = $this->skipJikaKosongBerjenjang(self::STAGE_FLOW[$lr->status]['next'], $lr->karyawan, self::STAGE_FLOW);

        if ($nextStatus === 'disetujui') {
            // Re-check saldo tepat sebelum dipotong final — mencegah 2 pengajuan
            // terpisah yang masing-masing lolos validasi submit tapi totalnya
            // melebihi jatah kalau sama-sama disetujui belakangan.
            if ($lr->type === 'cuti' && !$this->saldoCutiCukup($lr->karyawan_id, $lr->start_date, $lr->end_date)) {
                $lr->update([
                    "{$stage}_by"   => null,
                    "{$stage}_at"   => null,
                    "{$stage}_note" => null,
                ]);
                throw new \RuntimeException('Saldo cuti karyawan tidak lagi mencukupi (kemungkinan terpakai oleh pengajuan lain yang disetujui lebih dulu).');
            }

            $lr->update([
                'status'      => 'disetujui',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            if ($lr->type === 'cuti') {
                $this->potongSaldoCutiPerTahun($lr->karyawan_id, $lr->start_date, $lr->end_date);
            }
            $jumlahDibuat = $this->buatAbsensiOtomatis($lr);

            AuditLog::catat('approve', "Approve final (tahap {$stage}) {$lr->type} karyawan ID {$lr->karyawan_id}, buat {$jumlahDibuat} absensi otomatis", $lr);
            NotifHelper::keKaryawan($lr->karyawan_id, new CutiStatusNotification($lr));

            return $jumlahDibuat;
        }

        $lr->update(['status' => $nextStatus]);
        AuditLog::catat('approve', "Approve tahap {$stage} {$lr->type} karyawan ID {$lr->karyawan_id}, lanjut ke {$nextStatus}", $lr);
        $this->notifikasiTahapAktif($lr);

        return 0;
    }

    /** Tolak di tahap manapun — langsung final, tidak lanjut ke tahap berikutnya */
    private function rejectSatu(Leaverequest $lr, $user, ?string $note): void
    {
        $stage = $lr->currentStage();

        // Jika sebelumnya sudah disetujui lalu di-reject ulang, hapus absensi otomatis & kembalikan saldo
        if ($lr->status === 'disetujui') {
            $this->hapusAbsensiOtomatis($lr);
            if ($lr->type === 'cuti') {
                $this->kembalikanSaldoCutiPerTahun($lr->karyawan_id, $lr->start_date, $lr->end_date);
            }
        }

        $lr->update([
            "{$stage}_by"    => $user->id,
            "{$stage}_at"    => now(),
            "{$stage}_note"  => $note,
            'status'         => 'ditolak',
            'rejected_stage' => $stage,
            'rejected_note'  => $note,
            'approved_by'    => $user->id,
            'approved_at'    => now(),
        ]);

        AuditLog::catat('reject', "Tolak {$lr->type} karyawan ID {$lr->karyawan_id} di tahap {$stage}", $lr);
        NotifHelper::keKaryawan($lr->karyawan_id, new CutiStatusNotification($lr));
    }

    /** Kirim notifikasi pengajuan ke approver tahap yang sedang aktif */
    private function notifikasiTahapAktif(Leaverequest $lr): void
    {
        $dept = $lr->karyawan->department;
        match ($lr->status) {
            'pending_spv'     => NotifHelper::keUser($dept?->spvUser, new CutiPengajuanNotification($lr)),
            'pending_manajer' => NotifHelper::keUser($dept?->managerUser, new CutiPengajuanNotification($lr)),
            'pending_hrd'     => NotifHelper::keHrd(new CutiPengajuanNotification($lr)),
            default           => null,
        };
    }

    // ── HELPER: Hitung hari kerja nyata (skip Sabtu, Minggu, libur nasional) ───

    private function hitungHariKerja(Carbon $start, Carbon $end): int
    {
        $hari    = 0;
        $current = $start->copy()->startOfDay();
        $selesai = $end->copy()->startOfDay();

        while ($current->lte($selesai)) {
            if (!$current->isWeekend() && !HariLibur::isLibur($current)) {
                $hari++;
            }
            $current->addDay();
        }

        return $hari;
    }

    /**
     * Sama seperti hitungHariKerja(), tapi dipecah per TAHUN KALENDER —
     * dibutuhkan untuk cuti yang menyeberang tahun (mis. 29 Des–3 Jan) supaya
     * saldo yang dipotong/dikembalikan sesuai jatah tahun yang benar, bukan
     * seluruhnya dibebankan ke tahun start_date saja. Return: [tahun => hari].
     */
    private function hitungHariKerjaPerTahun(Carbon $start, Carbon $end): array
    {
        $hasil   = [];
        $current = $start->copy()->startOfDay();
        $selesai = $end->copy()->startOfDay();

        while ($current->lte($selesai)) {
            if (!$current->isWeekend() && !HariLibur::isLibur($current)) {
                $tahun = $current->year;
                $hasil[$tahun] = ($hasil[$tahun] ?? 0) + 1;
            }
            $current->addDay();
        }

        return $hasil;
    }

    /** Potong saldo cuti per tahun sesuai hari kerja di masing-masing tahun. */
    private function potongSaldoCutiPerTahun(int $karyawanId, Carbon $start, Carbon $end): void
    {
        foreach ($this->hitungHariKerjaPerTahun($start, $end) as $tahun => $hari) {
            CutiSaldo::getSaldo($karyawanId, $tahun)->tambahTerpakai($hari);
        }
    }

    /** Kembalikan saldo cuti per tahun sesuai hari kerja di masing-masing tahun. */
    private function kembalikanSaldoCutiPerTahun(int $karyawanId, Carbon $start, Carbon $end): void
    {
        foreach ($this->hitungHariKerjaPerTahun($start, $end) as $tahun => $hari) {
            CutiSaldo::getSaldo($karyawanId, $tahun)->kurangiTerpakai($hari);
        }
    }

    /**
     * Cek apakah saldo cuti di SEMUA tahun yang terlibat rentang tanggal ini
     * masih cukup. Dipakai sebagai re-check tepat sebelum approval final
     * memotong saldo — mencegah 2 pengajuan terpisah yang lolos validasi
     * submit sendiri-sendiri tapi totalnya melebihi jatah saat sama-sama disetujui.
     */
    private function saldoCutiCukup(int $karyawanId, Carbon $start, Carbon $end): bool
    {
        foreach ($this->hitungHariKerjaPerTahun($start, $end) as $tahun => $hari) {
            if (CutiSaldo::getSaldo($karyawanId, $tahun)->sisa < $hari) {
                return false;
            }
        }
        return true;
    }

    // ── HELPER: Buat absensi otomatis untuk setiap hari kerja dalam range ─────

    public function exportCsv(Request $request)
    {
        // Wajibkan bulan/tahun (default bulan berjalan) — tanpa ini export bisa
        // menarik SELURUH riwayat cuti/izin sekaligus, berisiko timeout/memory.
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        return Excel::download(
            new LeaverequestExport($bulan, $tahun, $request->status, $request->type, $this->scopedDepartmentIds()),
            'cuti-' . now()->format('Y-m') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $query = Leaverequest::with('karyawan.department', 'approver');

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null) {
            $query->whereHas('karyawan', fn($q) => $q->whereIn('department_id', $deptIds ?: [0]));
        }

        if ($request->search) {
            $query->whereHas('karyawan', fn($q) => $q->where('nama', 'like', "%{$request->search}%")
                ->orWhere('nik', 'like', "%{$request->search}%"));
        }
        if ($request->status) $query->where('status', $request->status);
        if ($request->type)   $query->where('type', $request->type);
        if ($request->bulan)  $query->whereMonth('start_date', $request->bulan);

        $leaverequests    = $query->latest()->get();
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $filterLabel = '';
        if ($request->bulan) $filterLabel .= 'Bulan: ' . Carbon::create()->month($request->bulan)->translatedFormat('F') . ' | ';
        if ($request->status) $filterLabel .= 'Status: ' . ucfirst($request->status) . ' | ';
        if ($request->type)   $filterLabel .= 'Tipe: ' . ucfirst($request->type);
        $filterLabel = rtrim($filterLabel, ' | ');

        $pdf = Pdf::loadView('admin.leaverequest.export-pdf', compact(
            'leaverequests', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan', 'filterLabel'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('cuti-izin-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buatAbsensiOtomatis(Leaverequest $lr): int
    {
        $statusAbsensi = match($lr->type) {
            'cuti'  => 'cuti',
            'sakit' => 'sakit',
            default => 'izin',
        };

        $keterangan = match($lr->type) {
            'cuti'  => 'Cuti tahunan (disetujui)',
            'sakit' => 'Sakit (disetujui)',
            default => 'Izin (disetujui)',
        };
        if ($lr->keterangan) {
            $keterangan .= ': ' . $lr->keterangan;
        }

        $karyawan = $lr->karyawan ?? Karyawan::find($lr->karyawan_id);
        $dibuat   = 0;

        $current = $lr->start_date->copy();
        $end     = $lr->end_date->copy();

        while ($current->lte($end)) {
            // Skip Sabtu, Minggu, dan hari libur nasional
            if (!$current->isWeekend() && !HariLibur::isLibur($current)) {
                // Cek tidak ada absensi yang sudah ada (hadir/terlambat/manual) di hari itu
                $existing = Absensi::where('karyawan_id', $lr->karyawan_id)
                    ->whereDate('tanggal', $current)
                    ->first();

                if (!$existing) {
                    Absensi::create([
                        'karyawan_id'     => $lr->karyawan_id,
                        'shift_id'        => $karyawan->shift_id,
                        'tanggal'         => $current->toDateString(),
                        'jam_masuk'       => null,
                        'jam_keluar'      => null,
                        'status'          => $statusAbsensi,
                        'metode'          => 'sistem',
                        'keterangan'      => $keterangan,
                        'leaverequest_id' => $lr->id,
                    ]);
                    $dibuat++;
                }
            }

            $current->addDay();
        }

        return $dibuat;
    }

    // ── HELPER: Hapus absensi otomatis saat reject/delete ─────────────────────

    private function hapusAbsensiOtomatis(Leaverequest $lr): void
    {
        Absensi::where('leaverequest_id', $lr->id)->delete();
    }
}
