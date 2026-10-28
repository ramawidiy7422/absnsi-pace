<?php
namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CutiSaldo;
use App\Models\Employeshift;
use App\Models\FormulirMeninggalkanKerja;
use App\Models\Izin;
use App\Models\Leaverequest;
use App\Models\Lemburan;
use App\Models\HariLibur;
use App\Models\Karyawan;
use App\Models\Pengumuman;
use App\Models\Shift;
use App\Models\Warninglettter;
use App\Models\Workhistory;
use App\Helpers\NotifHelper;
use App\Notifications\CutiPengajuanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PortalKaryawanController extends Controller
{
    use \App\Traits\HasApprovalStages;

    private function karyawan(): ?Karyawan
    {
        return Karyawan::find(auth()->user()->karyawan_id);
    }

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
     * Sama seperti hitungHariKerja(), tapi dipecah per TAHUN KALENDER — dibutuhkan
     * untuk cuti yang menyeberang tahun (mis. 29 Des–3 Jan) supaya validasi saldo
     * dicek terhadap jatah masing-masing tahun, bukan seluruhnya dibebankan ke
     * tahun start_date saja. Return: [tahun => hari].
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

    public function index()
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('admin.dashboard')->with('error', 'Akun Anda belum terhubung ke data karyawan.');

        $bulan         = now()->month;
        $tahun         = now()->year;
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)->whereDate('tanggal', today())->first();
        $absensi7Hari  = Absensi::where('karyawan_id', $karyawan->id)
                            ->whereBetween('tanggal', [now()->subDays(6)->toDateString(), today()->toDateString()])
                            ->orderBy('tanggal')->get();
        $statBulanIni  = Absensi::where('karyawan_id', $karyawan->id)
                            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();
        $saldoCuti     = CutiSaldo::getSaldo($karyawan->id);
        $cutiPending   = Leaverequest::where('karyawan_id', $karyawan->id)
                            ->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd'])->count();
        $lemburPending = Lemburan::where('karyawan_id', $karyawan->id)->where('status','pending')->count();
        $izinPending   = Izin::where('karyawan_id', $karyawan->id)->where('status','pending')->count();
        $spAktif       = Warninglettter::where('karyawan_id', $karyawan->id)->where('status','aktif')->count();
        $isLiburHariIni = HariLibur::isLibur(today());
        $namaLibur      = $isLiburHariIni ? HariLibur::whereDate('tanggal', today())->value('nama') : null;
        $karyawan->load(['shift', 'department']);
        $pengumuman = Pengumuman::aktifSekarang()
            ->where(fn($q) => $q->whereNull('target_departemen')->orWhere('target_departemen', $karyawan->departemen))
            ->orderByRaw("FIELD(prioritas,'mendesak','penting','normal')")
            ->orderByDesc('created_at')
            ->limit(5)->get();

        $popupPengumuman = Pengumuman::aktifSekarang()
            ->where('tampil_popup', true)
            ->where(fn($q) => $q->whereNull('target_departemen')->orWhere('target_departemen', $karyawan->departemen))
            ->orderByRaw("FIELD(prioritas,'mendesak','penting','normal')")
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'judul'    => $p->judul,
                'isi'      => $p->isi,
                'prioritas'=> $p->prioritas,
                'tanggal'  => $p->tanggal_mulai->format('d/m/Y'),
                'lampiran' => $p->lampiran ? asset('storage/'.$p->lampiran) : null,
            ])->values();

        // Kalender absensi bulan ini
        $startBulan = Carbon::create($tahun, $bulan, 1);
        $endBulan   = $startBulan->copy()->endOfMonth();
        $absensiBulan = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->get()->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
        $hariLiburBulan = HariLibur::whereBetween('tanggal', [$startBulan, $endBulan])
            ->get()->pluck('nama', 'tanggal')
            ->mapWithKeys(fn($v, $k) => [$k instanceof \Carbon\Carbon ? $k->format('Y-m-d') : $k => $v]);

        $lemburJamBulanIni = Lemburan::where('karyawan_id', $karyawan->id)
            ->where('status', 'disetujui')
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->sum('total_jam');

        $pengajuanTerkini = $this->pengajuanTerkini($karyawan->id, 5);

        return view('portal.index', compact(
            'karyawan','absensiHariIni','absensi7Hari','statBulanIni',
            'saldoCuti','cutiPending','lemburPending','izinPending','spAktif',
            'isLiburHariIni','namaLibur','pengumuman','popupPengumuman',
            'absensiBulan','hariLiburBulan','startBulan','endBulan',
            'lemburJamBulanIni','pengajuanTerkini'
        ));
    }

    /** Gabungan pengajuan cuti/izin/lembur/FMK terbaru, diurut created_at desc, dibatasi $limit */
    private function pengajuanTerkini(int $karyawanId, int $limit = 5)
    {
        $semua = collect();

        Leaverequest::where('karyawan_id', $karyawanId)->latest()->limit($limit)->get()
            ->each(function ($r) use (&$semua) {
                $semua->push([
                    'jenis'      => 'Cuti',
                    'icon'       => 'fa-umbrella-beach',
                    'warna'      => ['#553c9a', '#e9d8fd'],
                    'keterangan' => ucfirst($r->type) . ' — ' . $r->start_date->format('d/m') . ' s/d ' . $r->end_date->format('d/m/Y'),
                    'status'     => in_array($r->status, ['pending_spv', 'pending_manajer', 'pending_hrd']) ? 'pending' : $r->status,
                    'created_at' => $r->created_at,
                ]);
            });

        Izin::where('karyawan_id', $karyawanId)->latest()->limit($limit)->get()
            ->each(function ($r) use (&$semua) {
                $semua->push([
                    'jenis'      => 'Izin/Sakit',
                    'icon'       => 'fa-user-times',
                    'warna'      => ['#c05621', '#fef3c7'],
                    'keterangan' => ucfirst($r->jenis) . ' — ' . ($r->keterangan ?: '-'),
                    'status'     => $r->status,
                    'created_at' => $r->created_at,
                ]);
            });

        Lemburan::where('karyawan_id', $karyawanId)->latest()->limit($limit)->get()
            ->each(function ($r) use (&$semua) {
                $semua->push([
                    'jenis'      => 'Lembur',
                    'icon'       => 'fa-business-time',
                    'warna'      => ['#276749', '#c6f6d5'],
                    'keterangan' => $r->tanggal->translatedFormat('d M Y') . ' — ' . ($r->total_jam ?? '-') . ' jam',
                    'status'     => $r->status,
                    'created_at' => $r->created_at,
                ]);
            });

        FormulirMeninggalkanKerja::where('karyawan_id', $karyawanId)->latest()->limit($limit)->get()
            ->each(function ($r) use (&$semua) {
                $semua->push([
                    'jenis'      => 'Izin Keluar',
                    'icon'       => 'fa-walking',
                    'warna'      => ['#c05621', '#fef9c3'],
                    'keterangan' => $r->nomor_fmk . ' — ' . ucfirst(str_replace('_', ' ', $r->alasan)),
                    'status'     => in_array($r->status, ['pending_spv', 'pending_manajer', 'pending_hrd']) ? 'pending' : $r->status,
                    'created_at' => $r->created_at,
                ]);
            });

        return $semua->sortByDesc('created_at')->take($limit)->values();
    }

    public function absensi(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $absensiAll = Absensi::where('karyawan_id', $karyawan->id)
                    ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                    ->orderBy('tanggal')->get();

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
                    ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                    ->orderBy('tanggal')->paginate(31)->withQueryString();

        $stat = [
            'hadir'     => $absensiAll->where('status','hadir')->count(),
            'terlambat' => $absensiAll->where('status','terlambat')->count(),
            'izin'      => $absensiAll->where('status','izin')->count(),
            'sakit'     => $absensiAll->where('status','sakit')->count(),
            'alpha'     => $absensiAll->where('status','alpha')->count(),
            'cuti'      => $absensiAll->where('status','cuti')->count(),
        ];

        // Data kalender — map tanggal ke status
        $kalenderData = $absensiAll->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
        $jumlahHari   = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $hariPertama  = Carbon::createFromDate($tahun, $bulan, 1)->dayOfWeek; // 0=Minggu

        // Hari libur bulan ini
        $hariLibur = \App\Models\HariLibur::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
            ->flip();

        $bulanList = collect(range(1,12))->mapWithKeys(fn($m) => [$m => Carbon::create(null,$m)->translatedFormat('F')]);

        // Rekap tahunan (tab "Rekap Kehadiran")
        $tahunRekap = (int) $request->get('tahun_rekap', $tahun);
        $rekapBulanan = collect(range(1, 12))->map(function ($b) use ($karyawan, $tahunRekap) {
            $abs = Absensi::where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $b)->whereYear('tanggal', $tahunRekap)->get();

            $start = Carbon::createFromDate($tahunRekap, $b, 1);
            $end   = $start->copy()->endOfMonth();
            if ($end->gt(now())) $end = now();
            $hariKerja = 0;
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if ($d->dayOfWeek !== Carbon::SUNDAY && !HariLibur::isLibur($d)) $hariKerja++;
            }

            $hadir     = $abs->whereIn('status', ['hadir', 'terlambat'])->count();
            $terlambat = $abs->where('status', 'terlambat')->count();
            $izinBln   = $abs->where('status', 'izin')->count();
            $sakitBln  = $abs->where('status', 'sakit')->count();
            $cutiBln   = $abs->where('status', 'cuti')->count();
            $alphaBln  = $abs->where('status', 'alpha')->count();

            return [
                'bulan'      => $b,
                'nama_bulan' => Carbon::createFromDate($tahunRekap, $b, 1)->translatedFormat('M'),
                'hari_kerja' => $hariKerja,
                'hadir'      => $hadir,
                'terlambat'  => $terlambat,
                'izin'       => $izinBln,
                'sakit'      => $sakitBln,
                'cuti'       => $cutiBln,
                'alpha'      => $alphaBln,
                'persen'     => $hariKerja > 0 ? round($hadir / $hariKerja * 100) : 0,
            ];
        });

        $allAbsTahun = Absensi::where('karyawan_id', $karyawan->id)->whereYear('tanggal', $tahunRekap)->get();
        $totalRekap = [
            'hadir'     => $allAbsTahun->whereIn('status', ['hadir', 'terlambat'])->count(),
            'terlambat' => $allAbsTahun->where('status', 'terlambat')->count(),
            'izin'      => $allAbsTahun->where('status', 'izin')->count(),
            'sakit'     => $allAbsTahun->where('status', 'sakit')->count(),
            'cuti'      => $allAbsTahun->where('status', 'cuti')->count(),
            'alpha'     => $allAbsTahun->where('status', 'alpha')->count(),
        ];

        $tahunList = range(now()->year, max(now()->year - 3, 2022));

        return view('portal.absensi', compact(
            'karyawan','absensi','stat','bulan','tahun','bulanList','kalenderData','jumlahHari','hariPertama','hariLibur',
            'rekapBulanan','totalRekap','tahunRekap','tahunList'
        ));
    }

    public function cuti(Request $request)
    {
        $karyawan  = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $saldoCuti = CutiSaldo::getSaldo($karyawan->id);
        $riwayat   = Leaverequest::where('karyawan_id', $karyawan->id)->latest()->paginate(10)->withQueryString();
        $riwayatIzinTerlambat = \App\Models\IzinTerlambat::where('karyawan_id', $karyawan->id)
            ->with('inputOleh')->latest('tanggal')->limit(10)->get();

        return view('portal.cuti', compact('karyawan','saldoCuti','riwayat','riwayatIzinTerlambat'));
    }

    public function storeCuti(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $request->validate([
            // Cuti harus direncanakan (>= hari ini); Izin/Sakit boleh untuk tanggal lampau (lapor telat, misal baru sakit kemarin).
            'start_date' => $request->type === 'cuti' ? 'required|date|after_or_equal:today' : 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|in:cuti,izin,sakit',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Validasi overlap: tidak boleh ada pengajuan aktif di rentang yang sama
        $overlap = Leaverequest::where('karyawan_id', $karyawan->id)
            ->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd', 'disetujui'])
            ->where('start_date', '<=', $request->end_date)
            ->where('end_date', '>=', $request->start_date)
            ->first();
        if ($overlap) {
            return back()->withInput()->with('error',
                "Anda sudah memiliki pengajuan {$overlap->type} pada periode tersebut ({$overlap->start_date->format('d/m/Y')} – {$overlap->end_date->format('d/m/Y')})."
            );
        }

        // Cek bentrok lintas jenis pengajuan (izin/FMK) di rentang tanggal yang sama
        $bentrok = $this->cekBentrokPengajuan($karyawan->id, Carbon::parse($request->start_date), Carbon::parse($request->end_date));
        if ($bentrok) {
            return back()->withInput()->with('error', "Anda sudah punya pengajuan lain yang aktif di rentang tanggal tersebut: {$bentrok}.");
        }

        if ($request->type === 'cuti') {
            $mulai = Carbon::parse($request->start_date);
            $akhir = Carbon::parse($request->end_date);
            $saldo = CutiSaldo::getSaldo($karyawan->id);
            if ($saldo->jatah === 0) {
                $tglBerhak = $karyawan->tanggal_masuk ? $karyawan->tanggal_masuk->addYear()->format('d/m/Y') : '-';
                return back()->withInput()->with('error', "Anda belum berhak mengambil cuti. Masa kerja harus minimal 1 tahun. Hak cuti dimulai per {$tglBerhak}.");
            }
            // Dicek PER TAHUN KALENDER — cuti yang menyeberang tahun (mis. 29 Des-3 Jan)
            // divalidasi terhadap jatah masing-masing tahun, bukan cuma tahun berjalan.
            foreach ($this->hitungHariKerjaPerTahun($mulai, $akhir) as $tahun => $hari) {
                $saldoTahun = CutiSaldo::getSaldo($karyawan->id, $tahun);
                if ($saldoTahun->sisa < $hari) {
                    return back()->withInput()->with('error', "Saldo cuti tahun {$tahun} tidak cukup. Sisa: {$saldoTahun->sisa} hari, dibutuhkan: {$hari} hari.");
                }
            }
        }

        $lr = Leaverequest::create([
            'karyawan_id' => $karyawan->id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'type'        => $request->type,
            'keterangan'  => $request->keterangan,
            // Cuti tahunan wajib berjenjang SPV->Manajer->HRD karena berdampak ke jadwal kerja tim.
            // Izin/Sakit langsung ke HRD (bisa di-ACC langsung HRD/superadmin tanpa menunggu atasan).
            'status'      => $request->type === 'cuti' ? $this->tentukanStatusAwalCuti($karyawan) : 'pending_hrd',
        ]);

        $lr->load('karyawan.department');
        $dept = $lr->karyawan->department;
        match ($lr->status) {
            'pending_spv'     => NotifHelper::keUser($dept?->spvUser, new CutiPengajuanNotification($lr)),
            'pending_manajer' => NotifHelper::keUser($dept?->managerUser, new CutiPengajuanNotification($lr)),
            default           => NotifHelper::keHrd(new CutiPengajuanNotification($lr)),
        };

        return redirect()->route('portal.cuti')->with('success', 'Pengajuan berhasil dikirim, menunggu persetujuan.');
    }

    /** Status awal pengajuan cuti, otomatis skip tahap SPV/Manajer jika departemen belum punya approver-nya */
    private function tentukanStatusAwalCuti(Karyawan $karyawan): string
    {
        return $this->skipJikaKosongBerjenjang('pending_spv', $karyawan, \App\Support\FmkStageFlow::MAP);
    }

    /**
     * Halaman ini dihilangkan dari portal karyawan — pengelolaan lembur
     * sepenuhnya lewat admin/SPV/Manajer. Redirect ke dashboard.
     */
    public function lembur(Request $request)
    {
        return redirect()->route('portal.index');
    }

    public function storeLembur(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $request->validate([
            'tanggal'        => 'required|date',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i',
            'alasan'         => 'required|string|max:300',
            'deskripsi_kerja'=> 'nullable|string|max:500',
            'is_mendadak'    => 'nullable|boolean',
        ]);

        // Cross-midnight safe
        $mulai    = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
        $selesai  = Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);
        if ($selesai->lte($mulai)) $selesai->addDay();
        $totalJam = $mulai->diffInMinutes($selesai) / 60;

        // Cek apakah ada SPK aktif untuk tanggal lembur
        $spk   = \App\Models\SpkLembur::spkAktifUntuk($karyawan->id);
        $jenis = $spk ? 'spk' : 'mandiri';

        // Validasi: lembur wajib SPK
        if (\App\Models\Pengaturan::get('lembur_wajib_spk', '0') === '1' && !$spk) {
            return back()->withErrors(['alasan' => 'Pengajuan lembur ditolak. Sistem mengharuskan adanya Surat Perintah Kerja (SPK) aktif. Hubungi HRD untuk pembuatan SPK.'])->withInput();
        }

        // Validasi: batas jam pengajuan mendadak
        $batasJam = (int) \App\Models\Pengaturan::get('batas_pengajuan_lembur_jam', 0);
        if ($batasJam > 0 && !$spk) {
            $jamMulaiLembur    = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
            $batasPengajuan    = $jamMulaiLembur->copy()->subHours($batasJam);
            if (now()->isAfter($batasPengajuan)) {
                return back()->withErrors(['jam_mulai' => "Pengajuan lembur mendadak harus diajukan minimal {$batasJam} jam sebelum mulai. Batas pengajuan: {$batasPengajuan->format('d/m H:i')}."])->withInput();
            }
        }

        // Validasi: maksimal jam lembur per hari
        $maxJam = (int) \App\Models\Pengaturan::get('max_jam_lembur_harian', 0);
        if ($maxJam > 0 && $totalJam > $maxJam) {
            return back()->withErrors(['jam_selesai' => "Durasi lembur ({$totalJam} jam) melebihi batas maksimal yang diizinkan sistem ({$maxJam} jam/hari)."])->withInput();
        }

        Lemburan::create([
            'karyawan_id'    => $karyawan->id,
            'spk_lembur_id'  => $spk?->id,
            'jenis'          => $jenis,
            'tanggal'        => $request->tanggal,
            'jam_mulai'      => $request->jam_mulai,
            'jam_selesai'    => $request->jam_selesai,
            'total_jam'      => round($totalJam, 2),
            'alasan'         => $request->alasan,
            'deskripsi_kerja'=> $request->deskripsi_kerja,
            'is_mendadak'    => $request->boolean('is_mendadak'),
            'status'         => 'pending',
        ]);

        return redirect()->route('portal.index')->with('success', 'Pengajuan lembur berhasil dikirim.');
    }

    public function cancelLembur(\App\Models\Lemburan $lemburan)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        // Pastikan milik karyawan ini dan masih pending
        if ($lemburan->karyawan_id !== $karyawan->id || $lemburan->status !== 'pending') {
            return back()->with('error', 'Pengajuan tidak dapat dibatalkan.');
        }

        $lemburan->delete();
        return redirect()->route('portal.index')->with('success', 'Pengajuan lembur berhasil dibatalkan.');
    }

    public function storeAlasanTerlambat(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $request->validate([
            'absensi_id'           => 'required|exists:absensi,id',
            'keterangan_terlambat' => 'required|string|max:500',
        ]);

        Absensi::where('id', $request->absensi_id)
            ->where('karyawan_id', $karyawan->id)
            ->where('status', 'terlambat')
            ->whereDate('tanggal', today())
            ->firstOrFail()
            ->update(['keterangan_terlambat' => $request->keterangan_terlambat]);

        return redirect()->route('portal.index')->with('success', 'Alasan keterlambatan berhasil dikirim.');
    }

    // ── Slip Absensi PDF (portal karyawan sendiri) ─────────────────────
    public function slipPdf(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->with('shift')->orderBy('tanggal')->get();

        // Hitung hari kerja bulan itu (kecuali Minggu dan hari libur nasional)
        $start     = Carbon::createFromDate($tahun, $bulan, 1);
        $end       = $start->copy()->endOfMonth();
        $hariKerja = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->dayOfWeek !== Carbon::SUNDAY && !HariLibur::isLibur($d)) $hariKerja++;
        }

        $rekap = [
            'hadir'      => $absensi->whereIn('status', ['hadir'])->count(),
            'terlambat'  => $absensi->where('status', 'terlambat')->count(),
            'izin'       => $absensi->where('status', 'izin')->count(),
            'sakit'      => $absensi->where('status', 'sakit')->count(),
            'cuti'       => $absensi->where('status', 'cuti')->count(),
            'alpha'      => $absensi->where('status', 'alpha')->count(),
            'total'      => $absensi->count(),
            'hari_kerja' => $hariKerja,
        ];
        $rekap['persen'] = $hariKerja > 0
            ? round(($rekap['hadir'] + $rekap['terlambat']) / $hariKerja * 100) : 0;

        $namaBulan        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');
        $slipFooter       = \App\Models\Pengaturan::get('slip_footer', '');

        ini_set('memory_limit', '256M');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.laporan.slip-pdf', compact(
            'karyawan', 'absensi', 'rekap', 'bulan', 'tahun', 'namaBulan',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan', 'slipFooter'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('slip-absensi-' . $karyawan->nik . "-{$bulan}-{$tahun}.pdf");
    }

    // ── Status Pelatihan & Sertifikasi karyawan sendiri ────────────────
    public function pelatihan()
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $pelatihans  = \App\Models\Pelatihan::where('karyawan_id', $karyawan->id)
            ->orderByDesc('tanggal_mulai')->get();

        $jenisLabel  = \App\Models\Pelatihan::jenisLabel();
        $jenisWajib  = ['cpob', 'k3', 'hygiene'];
        $grouped     = $pelatihans->groupBy('jenis');

        $statusWajib = [];
        foreach ($jenisWajib as $j) {
            $items = $grouped->get($j, collect());
            $valid = $items->filter(fn($p) => $p->status === 'lulus' && !$p->is_expired)->count();
            $statusWajib[$j] = $valid > 0 ? 'ok' : ($items->count() > 0 ? 'expired' : 'missing');
        }

        return view('portal.pelatihan', compact('karyawan', 'pelatihans', 'jenisLabel', 'statusWajib', 'jenisWajib'));
    }

    public function riwayatKerja()
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $riwayat = Workhistory::where('karyawan_id', $karyawan->id)
            ->orderByDesc('start_date')->get();

        return view('portal.riwayat-kerja', compact('karyawan', 'riwayat'));
    }

    /**
     * Halaman ini dihilangkan dari portal karyawan. Redirect ke dashboard.
     */
    public function jadwalShift(Request $request)
    {
        return redirect()->route('portal.index');
    }

    public function gantiPassword()
    {
        return view('portal.ganti-password');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'password_lama'     => 'required|string',
            'password_baru'     => 'required|string|min:8|confirmed|different:password_lama',
        ], [
            'password_baru.min'       => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_baru.different' => 'Password baru tidak boleh sama dengan password lama.',
        ]);

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return back()->with('success', 'Password berhasil diubah. Silakan login ulang jika diperlukan.');
    }

    public function notifikasi()
    {
        $user = auth()->user();
        $notifikasi = $user->notifications()->latest()->paginate(30);
        $user->unreadNotifications()->update(['read_at' => now()]);
        return view('portal.notifikasi', compact('notifikasi'));
    }

    public function hapusNotifikasi(Request $request)
    {
        $user = auth()->user();
        if ($request->hapus_semua) {
            $user->notifications()->delete();
            return redirect()->route('portal.notifikasi')->with('success', 'Semua notifikasi dihapus.');
        }
        $user->notifications()->where('id', $request->id)->delete();
        return back();
    }

    public function notifikasiFetch()
    {
        $user  = auth()->user();
        $items = $user->notifications()->latest()->take(15)->get()->map(fn($n) => [
            'id'     => $n->id,
            'data'   => $n->data,
            'unread' => is_null($n->read_at),
            'time'   => $n->created_at->diffForHumans(),
        ]);
        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $items,
        ]);
    }

    public function notifikasiMarkRead(Request $request)
    {
        $user = auth()->user();
        if ($request->id) {
            $user->notifications()->where('id', $request->id)->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }
        return response()->json(['ok' => true]);
    }
    public function profil()
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $karyawan->load(['department', 'devision', 'position', 'shift', 'user', 'educations', 'legaldoc']);
        $saldoCuti   = \App\Models\CutiSaldo::getSaldo($karyawan->id);
        $spAktif     = $karyawan->warningletters()->where('status', 'aktif')->count();
        $totalAbsensi = \App\Models\Absensi::where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal', now()->year)->count();

        return view('portal.profil', compact('karyawan', 'saldoCuti', 'spAktif', 'totalAbsensi'));
    }

    /**
     * Halaman gabungan 1 tempat: semua data & form aksi milik karyawan sendiri, dalam bentuk tab,
     * setara pengalaman admin di /admin/karyawan/{id} tapi otomatis scoped ke akun yang login.
     */
    public function profilLengkap(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $karyawan->load(['department', 'devision', 'position', 'shift', 'user', 'educations', 'legaldoc']);

        // Biodata
        $saldoCuti    = CutiSaldo::getSaldo($karyawan->id);
        $spAktif      = $karyawan->warningletters()->where('status', 'aktif')->count();
        $totalAbsensi = Absensi::where('karyawan_id', $karyawan->id)->whereYear('tanggal', now()->year)->count();

        // Cuti & Izin (+ Izin Terlambat)
        $riwayatCuti = Leaverequest::where('karyawan_id', $karyawan->id)->latest()->paginate(10, ['*'], 'cuti_page')->withQueryString();
        $riwayatIzinTerlambat = \App\Models\IzinTerlambat::where('karyawan_id', $karyawan->id)
            ->with('inputOleh')->latest('tanggal')->limit(10)->get();

        // FMK (Izin Keluar)
        $riwayatFmk = FormulirMeninggalkanKerja::where('karyawan_id', $karyawan->id)
            ->with('spvApprover', 'manajerApprover', 'hrdApprover')
            ->latest('tanggal')->paginate(10, ['*'], 'fmk_page')->withQueryString();
        $nomorFMK = FormulirMeninggalkanKerja::generateNomor();

        // Lembur
        $riwayatLembur = Lemburan::where('karyawan_id', $karyawan->id)
            ->latest('tanggal')->limit(10)->get();

        // Pelatihan & Sertifikasi
        $pelatihans = \App\Models\Pelatihan::where('karyawan_id', $karyawan->id)->orderByDesc('tanggal_mulai')->get();
        $jenisLabel = \App\Models\Pelatihan::jenisLabel();
        $jenisWajib = ['cpob', 'k3', 'hygiene'];
        $groupedPelatihan = $pelatihans->groupBy('jenis');
        $statusWajib = [];
        foreach ($jenisWajib as $j) {
            $items = $groupedPelatihan->get($j, collect());
            $valid = $items->filter(fn($p) => $p->status === 'lulus' && !$p->is_expired)->count();
            $statusWajib[$j] = $valid > 0 ? 'ok' : ($items->count() > 0 ? 'expired' : 'missing');
        }

        // Riwayat Kerja
        $riwayatKerja = Workhistory::where('karyawan_id', $karyawan->id)->orderByDesc('start_date')->get();

        // Surat Peringatan
        $suratPeringatan = $karyawan->warningletters()->latest()->paginate(15, ['*'], 'sp_page')->withQueryString();

        return view('portal.profil-lengkap', compact(
            'karyawan', 'saldoCuti', 'spAktif', 'totalAbsensi',
            'riwayatCuti', 'riwayatIzinTerlambat',
            'riwayatFmk', 'nomorFMK', 'riwayatLembur',
            'pelatihans', 'jenisLabel', 'jenisWajib', 'statusWajib',
            'riwayatKerja', 'suratPeringatan'
        ));
    }

    public function updateProfil(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $request->validate([
            'no_hp'                   => 'nullable|string|max:20',
            'email'                   => 'nullable|email|max:100',
            'alamat'                  => 'nullable|string|max:300',
            'kontak_darurat_nama'     => 'nullable|string|max:100',
            'kontak_darurat_hubungan' => 'nullable|string|max:50',
            'kontak_darurat_no_hp'    => 'nullable|string|max:20',
            'bank_nama'               => 'nullable|string|max:50',
            'bank_no_rekening'        => 'nullable|string|max:30',
            'bank_atas_nama'          => 'nullable|string|max:100',
            'foto_wajah'              => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'no_hp', 'email', 'alamat',
            'kontak_darurat_nama', 'kontak_darurat_hubungan', 'kontak_darurat_no_hp',
            'bank_nama', 'bank_no_rekening', 'bank_atas_nama',
        ]);

        if ($request->hasFile('foto_wajah')) {
            // Hapus foto lama
            if ($karyawan->foto_wajah) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($karyawan->foto_wajah);
            }
            $path = $request->file('foto_wajah')->store('foto-karyawan', 'public');
            $data['foto_wajah'] = $path;
        }

        $karyawan->update($data);

        return redirect()->route('portal.profil')->with('success', 'Data profil berhasil diperbarui.');
    }

    public function suratPeringatan()
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $sp = $karyawan->warningletters()->latest()->paginate(15)->withQueryString();

        return view('portal.surat-peringatan', compact('karyawan', 'sp'));
    }

    public function riwayatPengajuan(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $tahun = (int) ($request->tahun ?? now()->year);
        $jenis = $request->jenis ?? 'semua'; // cuti|izin|lembur|fmk|semua

        // Kumpulkan semua pengajuan dalam satu koleksi
        $semua = collect();

        if (in_array($jenis, ['semua', 'cuti'])) {
            Leaverequest::where('karyawan_id', $karyawan->id)
                ->whereYear('start_date', $tahun)
                ->get()
                ->each(function ($r) use (&$semua) {
                    $semua->push([
                        'jenis'      => 'Cuti',
                        'jenis_slug' => 'cuti',
                        'warna'      => ['#553c9a', '#e9d8fd'],
                        'icon'       => 'fa-umbrella-beach',
                        'tanggal'    => $r->start_date,
                        'keterangan' => ucfirst($r->type) . ' — ' . $r->start_date->format('d/m') . ' s/d ' . $r->end_date->format('d/m/Y'),
                        'status'     => in_array($r->status, ['pending_spv', 'pending_manajer', 'pending_hrd']) ? 'pending' : $r->status,
                        'created_at' => $r->created_at,
                        'nomor'      => null,
                    ]);
                });
        }

        if (in_array($jenis, ['semua', 'izin'])) {
            Izin::where('karyawan_id', $karyawan->id)
                ->whereYear('tanggal', $tahun)
                ->get()
                ->each(function ($r) use (&$semua) {
                    $semua->push([
                        'jenis'      => 'Izin/Sakit',
                        'jenis_slug' => 'izin',
                        'warna'      => ['#c05621', '#fef3c7'],
                        'icon'       => 'fa-user-times',
                        'tanggal'    => $r->tanggal,
                        'keterangan' => ucfirst($r->jenis) . ' — ' . ($r->keterangan ?: '-'),
                        'status'     => $r->status,
                        'created_at' => $r->created_at,
                        'nomor'      => null,
                    ]);
                });
        }

        if (in_array($jenis, ['semua', 'lembur'])) {
            Lemburan::where('karyawan_id', $karyawan->id)
                ->whereYear('tanggal', $tahun)
                ->get()
                ->each(function ($r) use (&$semua) {
                    $semua->push([
                        'jenis'      => 'Lembur',
                        'jenis_slug' => 'lembur',
                        'warna'      => ['#276749', '#c6f6d5'],
                        'icon'       => 'fa-business-time',
                        'tanggal'    => $r->tanggal,
                        'keterangan' => $r->tanggal->translatedFormat('d M Y') . ' — ' . ($r->total_jam ?? '-') . ' jam',
                        'status'     => $r->status,
                        'created_at' => $r->created_at,
                        'nomor'      => null,
                    ]);
                });
        }

        if (in_array($jenis, ['semua', 'fmk'])) {
            FormulirMeninggalkanKerja::where('karyawan_id', $karyawan->id)
                ->whereYear('tanggal', $tahun)
                ->get()
                ->each(function ($r) use (&$semua) {
                    $semua->push([
                        'jenis'      => 'Izin Keluar',
                        'jenis_slug' => 'fmk',
                        'warna'      => ['#c05621', '#fef9c3'],
                        'icon'       => 'fa-walking',
                        'tanggal'    => $r->tanggal,
                        'keterangan' => $r->nomor_fmk . ' — ' . ucfirst(str_replace('_', ' ', $r->alasan)),
                        'status'     => in_array($r->status, ['menunggu', 'pending_spv', 'pending_manajer', 'pending_hrd']) ? 'pending' : $r->status,
                        'created_at' => $r->created_at,
                        'nomor'      => $r->nomor_fmk,
                    ]);
                });
        }

        $semua = $semua->sortByDesc('tanggal')->values();

        // Hitung ringkasan status
        $ringkasan = [
            'total'     => $semua->count(),
            'pending'   => $semua->where('status', 'pending')->count(),
            'disetujui' => $semua->where('status', 'disetujui')->count(),
            'ditolak'   => $semua->where('status', 'ditolak')->count(),
        ];

        $tahunList = range(now()->year, max(now()->year - 3, 2022));

        return view('portal.riwayat-pengajuan', compact('karyawan', 'semua', 'ringkasan', 'tahun', 'tahunList', 'jenis'));
    }

    /**
     * Halaman ini digabung ke portal.absensi (tab "Rekap Kehadiran").
     * Redirect supaya link/bookmark lama tetap berfungsi.
     */
    public function rekapKehadiran(Request $request)
    {
        return redirect()->route('portal.absensi', [
            'tahun_rekap' => $request->tahun,
        ] + ['_tab' => 'tab-rekap']);
    }

    public function fmk(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $riwayat = FormulirMeninggalkanKerja::where('karyawan_id', $karyawan->id)
            ->with('spvApprover', 'manajerApprover', 'hrdApprover')
            ->latest('tanggal')
            ->paginate(10)->withQueryString();

        $nomorFMK = FormulirMeninggalkanKerja::generateNomor();

        return view('portal.fmk', compact('karyawan', 'riwayat', 'nomorFMK'));
    }

    public function storeFmk(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $request->validate([
            'tanggal'        => 'required|date',
            'jam_keluar'     => 'required|date_format:H:i',
            'jam_kembali'    => 'nullable|date_format:H:i',
            'kembali_bekerja'=> 'required|in:kembali,tidak_kembali',
            'alasan'         => 'required|in:sakit,urusan_keluarga,urusan_pribadi,keperluan_dinas,lainnya',
            'keterangan'     => 'nullable|string|max:300',
        ]);

        $fmk = FormulirMeninggalkanKerja::createWithNomor([
            'karyawan_id'    => $karyawan->id,
            'tanggal'        => $request->tanggal,
            'jam_keluar'     => $request->jam_keluar,
            'jam_kembali'    => $request->jam_kembali,
            'kembali_bekerja'=> $request->kembali_bekerja,
            'alasan'         => $request->alasan,
            'keterangan'     => $request->keterangan,
            'status'         => $this->tentukanStatusAwalBerjenjang('pending_spv', $karyawan, \App\Support\FmkStageFlow::MAP),
        ]);

        $fmk->load('karyawan.department');
        $dept = $fmk->karyawan->department;
        match ($fmk->status) {
            'pending_spv'     => NotifHelper::keUser($dept?->spvUser, new \App\Notifications\FmkPengajuanNotification($fmk)),
            'pending_manajer' => NotifHelper::keUser($dept?->managerUser, new \App\Notifications\FmkPengajuanNotification($fmk)),
            default           => NotifHelper::keHrd(new \App\Notifications\FmkPengajuanNotification($fmk)),
        };

        return redirect()->route('portal.fmk')->with('success', "Formulir {$fmk->nomor_fmk} berhasil diajukan. Menunggu persetujuan.");
    }

    public function pengumuman(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $pengumuman = Pengumuman::aktifSekarang()
            ->orderByRaw("FIELD(prioritas,'mendesak','penting','normal')")
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('portal.pengumuman', compact('karyawan', 'pengumuman'));
    }

    public function kalenderCuti(Request $request)
    {
        $karyawan = $this->karyawan();
        if (!$karyawan) return redirect()->route('portal.index');

        $tahun = (int) ($request->tahun ?? now()->year);

        // Hari libur seluruh tahun
        $hariLiburTahun = HariLibur::whereYear('tanggal', $tahun)
            ->where('is_active', true)
            ->where('jenis', '!=', 'kerja_pengganti')
            ->orderBy('tanggal')
            ->get();

        // Cuti bersama khusus
        $cutiBersama = $hariLiburTahun->where('jenis', 'cuti_bersama');
        $liburNasional = $hariLiburTahun->where('jenis', 'nasional');
        $liburInternal = $hariLiburTahun->where('jenis', 'internal');

        // Bangun data bulan: array 12 bulan dengan daftar hari libur per bulan
        $kalenderBulanan = collect(range(1, 12))->map(function ($bulan) use ($tahun, $hariLiburTahun) {
            $liburBulan = $hariLiburTahun->filter(
                fn($h) => $h->tanggal->month === $bulan
            );
            return [
                'bulan'      => $bulan,
                'nama_bulan' => \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F'),
                'libur'      => $liburBulan->values(),
            ];
        });

        // Pengajuan cuti karyawan sendiri tahun ini
        $cutiKaryawan = \App\Models\Leaverequest::where('karyawan_id', $karyawan->id)
            ->whereYear('start_date', $tahun)
            ->orderBy('start_date')
            ->get();

        return view('portal.kalender-cuti', compact(
            'karyawan', 'tahun',
            'hariLiburTahun', 'cutiBersama', 'liburNasional', 'liburInternal',
            'kalenderBulanan', 'cutiKaryawan'
        ));
    }

}
