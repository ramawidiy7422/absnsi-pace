<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Leaverequest;
use App\Models\Lemburan;
use App\Models\SpkLembur;
use App\Models\Pelatihan;
use App\Models\Pengumuman;
use App\Models\FormulirMeninggalkanKerja;
use App\Models\SuratDinas;
use App\Models\Warninglettter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $bulan = $today->month;
        $tahun = $today->year;

        // Supervisor/manajer: semua statistik di bawah dibatasi ke departemen
        // yang mereka kelola. null berarti admin/hrd/superadmin, tidak dibatasi.
        $deptIds = $this->scopedDepartmentIds();
        $karyawanIdsScope = $deptIds !== null
            ? Karyawan::whereIn('department_id', $deptIds ?: [0])->pluck('id')
            : null;

        // ── Karyawan aktif ─────────────────────────────────────────────────
        $statusAktif   = ['kontrak','tetap','magang','training','outsourcing','harian'];
        $totalKaryawan = Karyawan::whereIn('status', $statusAktif)
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->count();

        // ── Absensi hari ini: satu query, group by status ──────────────────
        $absensiHariIniRaw = DB::table('absensi')
            ->whereDate('tanggal', $today)
            ->when($karyawanIdsScope !== null, fn($q) => $q->whereIn('karyawan_id', $karyawanIdsScope ?: [0]))
            ->select('status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $hadirHariIni     = ($absensiHariIniRaw['hadir'] ?? 0) + ($absensiHariIniRaw['terlambat'] ?? 0);
        $terlambatHariIni = $absensiHariIniRaw['terlambat'] ?? 0;
        $absenHariIni     = ($absensiHariIniRaw['izin'] ?? 0)
                          + ($absensiHariIniRaw['sakit'] ?? 0)
                          + ($absensiHariIniRaw['alpha'] ?? 0);
        $belumAbsen       = max(0, $totalKaryawan - $hadirHariIni - $absenHariIni);

        // ── Pie chart hari ini (dari data yang sudah ada) ──────────────────
        $pieData = [
            'hadir'     => $absensiHariIniRaw['hadir']    ?? 0,
            'terlambat' => $terlambatHariIni,
            'izin'      => $absensiHariIniRaw['izin']     ?? 0,
            'sakit'     => $absensiHariIniRaw['sakit']    ?? 0,
            'alpha'     => $absensiHariIniRaw['alpha']    ?? 0,
            'belum'     => $belumAbsen,
        ];

        // ── Grafik 7 hari: SATU query, group by tanggal + status ──────────
        $start7 = $today->copy()->subDays(6)->toDateString();
        $end7   = $today->toDateString();

        $raw7 = DB::table('absensi')
            ->whereBetween('tanggal', [$start7, $end7])
            ->when($karyawanIdsScope !== null, fn($q) => $q->whereIn('karyawan_id', $karyawanIdsScope ?: [0]))
            ->select('tanggal', 'status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('tanggal', 'status')
            ->get()
            ->groupBy(fn($r) => $r->tanggal);

        $grafik7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl    = $today->copy()->subDays($i)->toDateString();
            $byDate = $raw7->get($tgl, collect());
            $map    = $byDate->pluck('jumlah', 'status');
            $grafik7Hari[] = [
                'label'     => Carbon::parse($tgl)->format('d/m'),
                'hadir'     => ($map['hadir'] ?? 0) + ($map['terlambat'] ?? 0),
                'terlambat' => $map['terlambat'] ?? 0,
                'alpha'     => ($map['alpha'] ?? 0) + ($map['izin'] ?? 0) + ($map['sakit'] ?? 0),
            ];
        }

        // ── Alert: karyawan terlambat hari ini ─────────────────────────────
        $karyawanTerlambat = Absensi::with('karyawan.department')
            ->whereDate('tanggal', $today)
            ->where('status', 'terlambat')
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->orderBy('jam_masuk')
            ->get();

        // ── Alert: karyawan belum absen ────────────────────────────────────
        $sudahAbsenIds = DB::table('absensi')
            ->whereDate('tanggal', $today)
            ->pluck('karyawan_id');

        $belumAbsenList = Karyawan::with(['department', 'shift'])
            ->whereIn('status', $statusAktif)
            ->whereNotIn('id', $sudahAbsenIds)
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->orderBy('nama')
            ->get();

        // ── Absensi terbaru hari ini ───────────────────────────────────────
        $absensiTerbaru = Absensi::with('karyawan.department')
            ->whereDate('tanggal', $today)
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->latest()
            ->take(10)
            ->get();

        // ── Statistik tambahan: satu query per model ───────────────────────
        $cutiPending     = Leaverequest::where('status', 'pending')
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->count();
        $izinPending     = Izin::where('status', 'pending')
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->count();
        $lemburanPending  = Lemburan::where('status', 'pending')
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->count();
        $spkAktifHariIni  = SpkLembur::where('status', 'aktif')->whereDate('tanggal', today())
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->with(['karyawan','department'])->get();
        $spAktif         = Warninglettter::where('status', 'aktif')
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->count();
        $fmkPending      = FormulirMeninggalkanKerja::where('status', 'menunggu')
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->count();
        $spdDraft        = SuratDinas::where('status', 'draft')
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->count();
        $karyawanBaru = Karyawan::whereIn('status', $statusAktif)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->count();

        // ── Chart: Rekap per Departemen bulan ini ──────────────────────────
        // Supervisor/manajer cuma membawahi departemennya sendiri, jadi chart
        // "per departemen" otomatis cuma berisi departemen itu untuk mereka.
        $absBulan = Absensi::with('karyawan.department')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereHas('karyawan', fn($q) => $q->whereIn('status', $statusAktif)
                ->when($deptIds !== null, fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->get();

        $chartDept = $absBulan
            ->groupBy(fn($a) => $a->karyawan?->department?->name ?? $a->karyawan?->departemen ?? 'Lainnya')
            ->map(fn($rows, $dept) => [
                'dept'  => $dept,
                'hadir' => $rows->whereIn('status', ['hadir','terlambat'])->count(),
                'alpha' => $rows->whereIn('status', ['alpha','izin','sakit'])->count(),
            ])
            ->sortKeys()
            ->values();

        // ── Pengumuman aktif ───────────────────────────────────────────────
        $pengumumanAktif = Pengumuman::aktifSekarang()
            ->orderByRaw("FIELD(prioritas,'mendesak','penting','normal')")
            ->limit(3)->get();

        // ── Alert: Kontrak akan habis (30 hari ke depan) ───────────────────
        $kontrakAkanHabis = Karyawan::with('department')
            ->where('status', 'kontrak')
            ->whereNotNull('tanggal_kontrak_berakhir')
            ->whereBetween('tanggal_kontrak_berakhir', [
                $today->toDateString(),
                $today->copy()->addDays(30)->toDateString(),
            ])
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->orderBy('tanggal_kontrak_berakhir')
            ->get();

        // ── Alert: Pelatihan / Sertifikat wajib expired / hampir expired ───
        $pelatihanExpired = Pelatihan::with('karyawan')
            ->whereHas('karyawan', fn($q) => $q->whereIn('status', $statusAktif)
                ->when($deptIds !== null, fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->whereNotNull('tanggal_expired')
            ->where('status', 'lulus')
            ->whereIn('jenis', ['cpob', 'k3', 'hygiene'])
            ->where('tanggal_expired', '<=', $today->copy()->addDays(60)->toDateString())
            ->orderBy('tanggal_expired')
            ->get();

        // ── Grafik trend 12 bulan ──────────────────────────────────────────
        $chartBulanan = collect(range(11, 0))->map(function ($i) use ($karyawanIdsScope) {
            $tgl   = now()->subMonths($i);
            $bulan = $tgl->month;
            $tahun = $tgl->year;
            $rows  = Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                        ->when($karyawanIdsScope !== null, fn($q) => $q->whereIn('karyawan_id', $karyawanIdsScope ?: [0]))
                        ->select('status', DB::raw('count(*) as total'))
                        ->groupBy('status')->pluck('total', 'status');
            return [
                'label'     => $tgl->translatedFormat('M y'),
                'hadir'     => ($rows['hadir'] ?? 0) + ($rows['terlambat'] ?? 0),
                'alpha'     => $rows['alpha'] ?? 0,
                'izin'      => ($rows['izin'] ?? 0) + ($rows['sakit'] ?? 0) + ($rows['cuti'] ?? 0),
            ];
        });

        // ── Top 5 karyawan paling sering terlambat bulan ini ──────────────
        $top5Terlambat = DB::table('absensi')
            ->join('karyawan', 'absensi.karyawan_id', '=', 'karyawan.id')
            ->leftJoin('departments', 'karyawan.department_id', '=', 'departments.id')
            ->where('absensi.status', 'terlambat')
            ->whereMonth('absensi.tanggal', $bulan)
            ->whereYear('absensi.tanggal', $tahun)
            ->when($deptIds !== null, fn($q) => $q->whereIn('karyawan.department_id', $deptIds ?: [0]))
            ->select('karyawan.id', 'karyawan.nama', 'departments.name as dept_name', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('karyawan.id', 'karyawan.nama', 'departments.name')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get();

        // ── Alert: Ulang tahun hari ini & minggu ini ───────────────────────
        $ultahHariIni = Karyawan::with('department')
            ->whereIn('status', $statusAktif)
            ->whereNotNull('birth_date')
            ->whereRaw('MONTH(birth_date) = ? AND DAY(birth_date) = ?', [$today->month, $today->day])
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->get();

        // Ambil ultah 7 hari ke depan — cek per tanggal satu per satu agar aman di year-end
        $upcomingDates = collect(range(1, 7))->map(fn($d) => $today->copy()->addDays($d));
        $conditions = $upcomingDates->map(fn($dt) => ['month' => $dt->month, 'day' => $dt->day]);

        $ultahMingguIni = Karyawan::with('department')
            ->whereIn('status', $statusAktif)
            ->whereNotNull('birth_date')
            ->where(function ($q) use ($conditions) {
                foreach ($conditions as $c) {
                    $q->orWhere(fn($s) => $s->whereRaw('MONTH(birth_date) = ?', [$c['month']])->whereRaw('DAY(birth_date) = ?', [$c['day']]));
                }
            })
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->orderByRaw('MONTH(birth_date), DAY(birth_date)')
            ->get()
            ->filter(fn($k) => !$ultahHariIni->contains('id', $k->id));

        return view('admin.dashboard', compact(
            'totalKaryawan', 'hadirHariIni', 'terlambatHariIni',
            'absenHariIni', 'belumAbsen',
            'grafik7Hari', 'pieData',
            'karyawanTerlambat', 'belumAbsenList',
            'absensiTerbaru',
            'cutiPending', 'izinPending', 'lemburanPending', 'spkAktifHariIni', 'spAktif', 'fmkPending', 'spdDraft', 'karyawanBaru',
            'chartDept', 'chartBulanan', 'pengumumanAktif',
            'kontrakAkanHabis', 'pelatihanExpired',
            'ultahHariIni', 'ultahMingguIni',
            'top5Terlambat'
        ));
    }
}
