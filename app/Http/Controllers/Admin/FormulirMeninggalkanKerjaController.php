<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FormulirMeninggalkanKerjaExport;
use App\Helpers\NotifHelper;
use App\Http\Controllers\Admin\ApprovalSayaController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FormulirMeninggalkanKerja;
use App\Models\Karyawan;
use App\Models\Pengaturan;
use App\Notifications\FmkPengajuanNotification;
use App\Notifications\FmkStatusNotification;
use App\Support\FmkStageFlow;
use App\Traits\HasApprovalStages;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class FormulirMeninggalkanKerjaController extends Controller
{
    use HasApprovalStages;

    private $statusAktif = ['kontrak','tetap','magang','training','outsourcing','harian'];

    public function index(Request $request)
    {
        $query = FormulirMeninggalkanKerja::with(['karyawan.department'])
            ->orderByDesc('tanggal')->orderByDesc('created_at');

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null) {
            $query->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0]));
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHas('karyawan', fn($w) => $w->where('nama','like',"%$q%")->orWhere('nik','like',"%$q%"));
        }
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('alasan'))  $query->where('alasan', $request->alasan);
        if ($request->filled('bulan'))   $query->whereMonth('tanggal', $request->bulan);
        if ($request->filled('tahun'))   $query->whereYear('tanggal', $request->tahun);

        $formulir  = $query->paginate(20)->withQueryString();
        $tahunList = range(now()->year, 2024);

        return view('admin.formulir.meninggalkan-kerja.index', compact('formulir','tahunList'));
    }

    public function create()
    {
        $karyawan  = Karyawan::whereIn('status', $this->statusAktif)->orderBy('nama')->get();
        $nomorFMK  = FormulirMeninggalkanKerja::generateNomor();
        return view('admin.formulir.meninggalkan-kerja.create', compact('karyawan','nomorFMK'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'    => 'required|exists:karyawan,id',
            'bagian'         => 'nullable|string|max:100',
            'tanggal'        => 'required|date',
            'jam_keluar'     => 'required|date_format:H:i',
            'jam_kembali'    => 'nullable|date_format:H:i',
            'kembali_bekerja'=> 'required|in:kembali,tidak_kembali',
            'alasan'         => 'required|in:sakit,urusan_keluarga,urusan_pribadi,keperluan_dinas,lainnya',
            'keterangan'     => 'nullable|string|max:300',
            'spv_nama'       => 'nullable|string|max:150',
            'hrd_nama'       => 'nullable|string|max:150',
        ]);

        $karyawan = Karyawan::find($request->karyawan_id);

        // Cek bentrok lintas jenis pengajuan (cuti/izin) di tanggal yang sama
        $tglFmk = Carbon::parse($request->tanggal);
        $bentrok = $this->cekBentrokPengajuan($karyawan->id, $tglFmk, $tglFmk);
        if ($bentrok) {
            return back()->withInput()->with('error', "Karyawan sudah punya pengajuan lain yang aktif di tanggal tersebut: {$bentrok}.");
        }

        $fmk = FormulirMeninggalkanKerja::createWithNomor([
            ...$request->only('karyawan_id','bagian','tanggal','jam_keluar','jam_kembali',
                              'kembali_bekerja','alasan','keterangan','spv_nama','hrd_nama'),
            'status'    => $this->tentukanStatusAwalBerjenjang('pending_spv', $karyawan, FmkStageFlow::MAP),
        ]);

        AuditLog::catat('create', "Buat FMK {$fmk->nomor_fmk}", $fmk);
        $this->notifikasiTahapAktifFmk($fmk);

        ApprovalSayaController::invalidasiCache($karyawan->department);

        return redirect()->route('admin.formulir.meninggalkan-kerja.show', $fmk)
            ->with('success', "Formulir {$fmk->nomor_fmk} berhasil dibuat.");
    }

    public function show(FormulirMeninggalkanKerja $meninggalkanKerja)
    {
        $meninggalkanKerja->load('karyawan.department','karyawan.position','approver');
        $pengaturan = Pengaturan::all()->pluck('value','key');
        return view('admin.formulir.meninggalkan-kerja.show', compact('meninggalkanKerja','pengaturan'));
    }

    public function edit(FormulirMeninggalkanKerja $meninggalkanKerja)
    {
        $karyawan = Karyawan::whereIn('status', $this->statusAktif)->orderBy('nama')->get();
        return view('admin.formulir.meninggalkan-kerja.edit', compact('meninggalkanKerja','karyawan'));
    }

    public function update(Request $request, FormulirMeninggalkanKerja $meninggalkanKerja)
    {
        if ($meninggalkanKerja->isFinal()) {
            return back()->with('error', 'Formulir yang sudah disetujui/ditolak tidak dapat diedit lagi.');
        }

        $request->validate([
            'karyawan_id'    => 'required|exists:karyawan,id',
            'bagian'         => 'nullable|string|max:100',
            'tanggal'        => 'required|date',
            'jam_keluar'     => 'required|date_format:H:i',
            'jam_kembali'    => 'nullable|date_format:H:i',
            'kembali_bekerja'=> 'required|in:kembali,tidak_kembali',
            'alasan'         => 'required|in:sakit,urusan_keluarga,urusan_pribadi,keperluan_dinas,lainnya',
            'keterangan'     => 'nullable|string|max:300',
            'spv_nama'       => 'nullable|string|max:150',
            'hrd_nama'       => 'nullable|string|max:150',
        ]);

        $meninggalkanKerja->update(
            $request->only('karyawan_id','bagian','tanggal','jam_keluar','jam_kembali',
                           'kembali_bekerja','alasan','keterangan','spv_nama','hrd_nama')
        );

        AuditLog::catat('update', "Edit FMK {$meninggalkanKerja->nomor_fmk}", $meninggalkanKerja);

        return redirect()->route('admin.formulir.meninggalkan-kerja.show', $meninggalkanKerja)
            ->with('success', "Formulir {$meninggalkanKerja->nomor_fmk} berhasil diperbarui.");
    }

    public function approve(Request $request, FormulirMeninggalkanKerja $meninggalkanKerja)
    {
        $user = auth()->user();
        $note = $request->input('note');

        $hasil = DB::transaction(function () use ($meninggalkanKerja, $user, $note) {
            $fmk = FormulirMeninggalkanKerja::where('id', $meninggalkanKerja->id)->lockForUpdate()->first();
            $fmk->setRelation('karyawan', $meninggalkanKerja->karyawan);
            if ($fmk->isFinal() || Gate::denies('approve-fmk', $fmk)) {
                return null;
            }

            $stage = $fmk->currentStage();
            $nextStatus = $this->approveSatuTahapBerjenjang($fmk, $stage, $user, $note, FmkStageFlow::MAP);

            if ($nextStatus === 'disetujui') {
                $fmk->update([
                    'status'         => 'disetujui',
                    'disetujui_oleh' => $user->id,
                    'disetujui_at'   => now(),
                ]);
                AuditLog::catat('approve', "Approve final (tahap {$stage}) FMK {$fmk->nomor_fmk}", $fmk);
                if ($fmk->karyawan?->user) {
                    $fmk->karyawan->user->notify(new FmkStatusNotification($fmk));
                }

                ApprovalSayaController::invalidasiCache($fmk->karyawan?->department);

                return 'Formulir disetujui final. Karyawan dapat absen pergi di kiosk.';
            }

            $fmk->update(['status' => $nextStatus]);
            AuditLog::catat('approve', "Approve tahap {$stage} FMK {$fmk->nomor_fmk}, lanjut ke {$nextStatus}", $fmk);
            $this->notifikasiTahapAktifFmk($fmk);

            ApprovalSayaController::invalidasiCache($fmk->karyawan?->department);

            return 'Disetujui di tahap ini, diteruskan ke tahap approval berikutnya.';
        });

        if ($hasil === null) {
            return back()->with('error', 'Formulir ini sudah diproses atau Anda bukan approver untuk tahap ini.');
        }

        return back()->with('success', $hasil);
    }

    public function tolak(Request $request, FormulirMeninggalkanKerja $meninggalkanKerja)
    {
        $user = auth()->user();
        $catatan = $request->catatan_approver;

        $ditolak = DB::transaction(function () use ($meninggalkanKerja, $user, $catatan) {
            $fmk = FormulirMeninggalkanKerja::where('id', $meninggalkanKerja->id)->lockForUpdate()->first();
            $fmk->setRelation('karyawan', $meninggalkanKerja->karyawan);
            if ($fmk->isFinal() || Gate::denies('approve-fmk', $fmk)) {
                return false;
            }

            $stage = $fmk->currentStage();
            $this->rejectSatuTahapBerjenjang($fmk, $stage, $user, $catatan);
            $fmk->update([
                'disetujui_oleh'   => $user->id,
                'disetujui_at'     => now(),
                'catatan_approver' => $catatan,
            ]);

            AuditLog::catat('reject', "Tolak FMK {$fmk->nomor_fmk} di tahap {$stage}", $fmk);
            if ($fmk->karyawan?->user) {
                $fmk->karyawan->user->notify(new FmkStatusNotification($fmk));
            }

            ApprovalSayaController::invalidasiCache($fmk->karyawan?->department);

            return true;
        });

        if (!$ditolak) {
            return back()->with('error', 'Formulir ini sudah diproses atau Anda bukan approver untuk tahap ini.');
        }

        return back()->with('success', 'Formulir ditolak.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $list = FormulirMeninggalkanKerja::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->whereNotIn('status', ['disetujui', 'ditolak'])->get();
        $count = 0;
        $skipped = 0;

        foreach ($list as $fmk) {
            // Pakai logic approve() satu-tahap yang sama persis dengan approve manual —
            // aman dipanggil berulang karena setiap panggilan mengunci row & cek status
            // 'pending' di dalam transaksi sebelum mengubah apapun. Flash session
            // dibersihkan sebelum & sesudah tiap panggilan supaya status iterasi
            // sebelumnya tidak bocor ke pengecekan berikutnya.
            session()->forget(['error', 'success']);
            $this->approve(new Request(['note' => 'Bulk approve']), $fmk);
            if (session('error')) {
                $skipped++;
            } else {
                $count++;
            }
        }

        session()->forget(['error', 'success']);
        AuditLog::catat('approve', "Bulk approve {$count} FMK");
        $pesan = "{$count} formulir berhasil disetujui (di tahap approval masing-masing).";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda atau sudah diproses.";
        return back()->with('success', $pesan);
    }

    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $list = FormulirMeninggalkanKerja::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->whereNotIn('status', ['disetujui', 'ditolak'])->get();
        $count = 0;
        $skipped = 0;

        foreach ($list as $fmk) {
            session()->forget(['error', 'success']);
            $this->tolak(new Request(['catatan_approver' => 'Bulk reject']), $fmk);
            if (session('error')) {
                $skipped++;
            } else {
                $count++;
            }
        }

        session()->forget(['error', 'success']);
        AuditLog::catat('reject', "Bulk reject {$count} FMK");
        $pesan = "{$count} formulir berhasil ditolak.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda atau sudah diproses.";
        return back()->with('success', $pesan);
    }

    private function notifikasiTahapAktifFmk(FormulirMeninggalkanKerja $fmk): void
    {
        $dept = $fmk->karyawan->department;
        match ($fmk->status) {
            'pending_spv'     => NotifHelper::keUser($dept?->spvUser, new FmkPengajuanNotification($fmk)),
            'pending_manajer' => NotifHelper::keUser($dept?->managerUser, new FmkPengajuanNotification($fmk)),
            'pending_hrd'     => NotifHelper::keHrd(new FmkPengajuanNotification($fmk)),
            default           => null,
        };
    }

    public function destroy(FormulirMeninggalkanKerja $meninggalkanKerja)
    {
        if ($meninggalkanKerja->status === 'disetujui') {
            return back()->with('error', 'Formulir yang sudah disetujui tidak dapat dihapus.');
        }

        $meninggalkanKerja->load('karyawan');
        $nomor = $meninggalkanKerja->nomor_fmk;
        $meninggalkanKerja->delete();
        AuditLog::catat('delete', "Hapus FMK {$nomor}");

        ApprovalSayaController::invalidasiCache($meninggalkanKerja->karyawan?->department);

        return redirect()->route('admin.formulir.meninggalkan-kerja.index')
            ->with('success', "Formulir {$nomor} dihapus.");
    }

    public function pdf(FormulirMeninggalkanKerja $meninggalkanKerja)
    {
        $meninggalkanKerja->load('karyawan.department','karyawan.position','karyawan.shift','approver');
        $pengaturan = Pengaturan::all()->pluck('value','key');
        return view('admin.formulir.meninggalkan-kerja.pdf', compact('meninggalkanKerja','pengaturan'));
    }

    public function exportExcel(Request $request)
    {
        // Wajibkan bulan/tahun (default bulan berjalan) — tanpa ini export bisa
        // menarik SELURUH riwayat FMK sekaligus, berisiko timeout/memory.
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        return Excel::download(
            new FormulirMeninggalkanKerjaExport($bulan, $tahun, $request->status),
            'formulir-meninggalkan-kerja-' . now()->format('Y-m') . '.xlsx'
        );
    }
}
