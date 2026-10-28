<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Employeshift;
use App\Models\FormulirMeninggalkanKerja;
use App\Models\IzinPulangCepat;
use App\Models\IzinTerlambat;
use App\Models\Karyawan;
use App\Models\HariLibur;
use App\Models\Lemburan;
use App\Models\Pengaturan;
use App\Models\SpkLembur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AbsensiKioskController extends Controller
{
    public function index()
    {
        return view('kiosk.index');
    }

    public function scanQr(Request $request)
    {
        $request->validate(['nik' => 'required|string']);

        $nik      = trim($request->nik);
        $karyawan = Karyawan::with('department')
            ->where('nik', $nik)
            ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->first();

        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'NIK tidak ditemukan atau karyawan tidak aktif.']);
        }

        $shift       = $this->resolveShift($karyawan);
        $jamSekarang = now()->format('H:i:s');

        $absensiKemarin = $this->cekAbsensiKemarinShiftMalam($karyawan, $shift, $jamSekarang);

        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        // === SCAN KELUAR shift malam (dari kemarin) ===
        // Prioritas pertama: jika ada absensi kemarin yang belum keluar (shift malam)
        if ($absensiKemarin) {
            return $this->prosesAbsenKeluar($karyawan, $absensiKemarin, $absensiKemarin->shift, $jamSekarang, 'qrcode');
        }

        // === CEK HARI LIBUR (hanya untuk absen masuk, bebas_absen tetap lolos) ===
        if (!$karyawan->isBebasAbsen() && HariLibur::isLibur(today())) {
            $namaLibur = HariLibur::whereDate('tanggal', today())->value('nama');
            return response()->json(['success' => false, 'message' => "Hari ini libur: {$namaLibur}. Absensi tidak diperlukan."]);
        }

        // Karyawan cuti/izin hari ini
        if ($absensiHariIni && $absensiHariIni->jam_masuk === null) {
            return response()->json([
                'success' => false,
                'message' => "{$karyawan->nama} tercatat {$absensiHariIni->status} hari ini. Tidak perlu absen.",
            ]);
        }

        // Sudah absen masuk dan keluar
        if ($absensiHariIni && $absensiHariIni->jam_keluar !== null) {
            return response()->json([
                'success'    => false,
                'karyawan'   => $karyawan->nama,
                'jam_masuk'  => substr($absensiHariIni->jam_masuk ?? '', 0, 5),
                'jam_keluar' => substr($absensiHariIni->jam_keluar ?? '', 0, 5),
                'message'    => "{$karyawan->nama} sudah absen masuk dan keluar hari ini.",
            ]);
        }

        if (!$absensiHariIni) {
            // === ABSEN MASUK via QR ===
            return $this->prosesAbsenMasuk($karyawan, $shift, $jamSekarang, 'qrcode');
        } else {
            // === ABSEN KELUAR via QR ===
            return $this->prosesAbsenKeluar($karyawan, $absensiHariIni, $shift, $jamSekarang, 'qrcode');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ABSEN PERGI/KEMBALI FMK (Formulir Meninggalkan Kerja)
    // Siklus terpisah dari absen masuk/keluar harian — tidak menyentuh tabel absensi.
    // Auto-detect: belum pergi hari ini → catat pergi; sudah pergi belum kembali → catat kembali.
    // ─────────────────────────────────────────────────────────────

    public function absenFmk(Request $request)
    {
        $karyawan = $this->identifikasiKaryawanFmk($request);
        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan atau tidak aktif.']);
        }

        $metode = $request->filled('foto') ? 'face' : 'qrcode';

        $hasil = DB::transaction(function () use ($karyawan, $metode) {
            // Lock per-karyawan supaya 2 scan bersamaan (double-tap/retry) tidak dobel-catat pergi/kembali.
            Karyawan::where('id', $karyawan->id)->lockForUpdate()->first();

            $fmkPergi = FormulirMeninggalkanKerja::where('karyawan_id', $karyawan->id)
                ->where('status', 'disetujui')
                ->whereDate('tanggal', today())
                ->whereNull('pergi_at')
                ->lockForUpdate()
                ->first();

            if ($fmkPergi) {
                $fmkPergi->update(['pergi_at' => now(), 'metode_pergi' => $metode]);
                return [
                    'success'  => true,
                    'tipe'     => 'fmk_pergi',
                    'karyawan' => $karyawan->nama,
                    'jam'      => now()->format('H:i:s'),
                    'message'  => "Selamat, {$karyawan->nama}! Absen pergi tercatat pukul " . now()->format('H:i') . ".",
                ];
            }

            $fmkKembali = FormulirMeninggalkanKerja::where('karyawan_id', $karyawan->id)
                ->where('status', 'disetujui')
                ->whereNotNull('pergi_at')
                ->whereNull('kembali_at')
                ->latest('pergi_at')
                ->lockForUpdate()
                ->first();

            if ($fmkKembali) {
                $fmkKembali->update(['kembali_at' => now(), 'metode_kembali' => $metode]);
                $durasi = $fmkKembali->hitungDurasiKerja();
                $fmkKembali->update(['durasi_menit' => $durasi]);

                $pesanDurasi = $fmkKembali->alasan === 'keperluan_dinas'
                    ? 'Dihitung sebagai jam kerja penuh (dinas).'
                    : "Durasi pergerakan tugas: {$durasi} menit.";

                return [
                    'success'      => true,
                    'tipe'         => 'fmk_kembali',
                    'karyawan'     => $karyawan->nama,
                    'jam'          => now()->format('H:i:s'),
                    'durasi_menit' => $durasi,
                    'message'      => "Selamat datang kembali, {$karyawan->nama}! {$pesanDurasi}",
                ];
            }

            return [
                'success' => false,
                'message' => "{$karyawan->nama} tidak memiliki Formulir Meninggalkan Kerja yang disetujui untuk diproses hari ini.",
            ];
        });

        return response()->json($hasil);
    }

    private function identifikasiKaryawanFmk(Request $request): ?Karyawan
    {
        if ($request->filled('nik')) {
            return Karyawan::where('nik', trim($request->nik))
                ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
                ->first();
        }

        if ($request->filled('foto')) {
            try {
                $response = Http::timeout(8)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/kenali', [
                    'foto' => $request->foto,
                ]);
                $body = $response->json() ?? [];
                if (!$response->successful() || empty($body['success'])) return null;

                return Karyawan::where('nik', $body['nik'])
                    ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
                    ->first();
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    public function scan(Request $request)
    {
        $request->validate(['foto' => 'required|string']);

        try {
            $response = Http::timeout(8)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/kenali', [
                'foto' => $request->foto,
            ]);

            $body = $response->json() ?? [];

            // Forward cooldown response langsung
            if ($response->status() === 429 && isset($body['cooldown'])) {
                return response()->json($body, 429);
            }

            // Jika Python error / tidak berhasil, forward pesan asli termasuk info distance
            if (!$response->successful() || empty($body['success'])) {
                return response()->json([
                    'success' => false,
                    'message' => $body['message'] ?? 'Wajah tidak dikenali',
                    'dist'    => $body['dist'] ?? null,
                ]);
            }

            $nik        = $body['nik'];
            $confidence = $body['confidence'];

            $karyawan = Karyawan::with('department')
                ->where('nik', $nik)
                ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
                ->first();

            if (!$karyawan) {
                return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan atau tidak aktif']);
            }

            $shift       = $this->resolveShift($karyawan);
            $jamSekarang = now()->format('H:i:s');

            $absensiKemarin = $this->cekAbsensiKemarinShiftMalam($karyawan, $shift, $jamSekarang);

            // Sudah absen masuk+keluar hari ini — cek dulu sebelum simpan foto
            $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', today())
                ->first();

            // Shift malam: scan keluar dari kemarin → proses tanpa simpan foto dulu
            if ($absensiKemarin) {
                // Simpan foto keluar
                $fotoNama = $this->simpanFoto($request->foto, $nik);
                return $this->prosesAbsenKeluar($karyawan, $absensiKemarin, $absensiKemarin->shift, $jamSekarang, 'face', $confidence, $fotoNama);
            }

            // Cek status cuti/izin SEBELUM simpan foto
            if ($absensiHariIni && $absensiHariIni->jam_masuk === null) {
                return response()->json([
                    'success' => false,
                    'message' => "{$karyawan->nama} tercatat {$absensiHariIni->status} hari ini. Tidak perlu absen.",
                ]);
            }

            // Sudah absen masuk & keluar
            if ($absensiHariIni && $absensiHariIni->jam_keluar !== null) {
                return response()->json([
                    'success'    => false,
                    'karyawan'   => $karyawan->nama,
                    'jam_masuk'  => substr($absensiHariIni->jam_masuk  ?? '', 0, 5),
                    'jam_keluar' => substr($absensiHariIni->jam_keluar ?? '', 0, 5),
                    'message'    => "{$karyawan->nama} sudah absen masuk dan keluar hari ini.",
                ]);
            }

            // Simpan foto setelah validasi awal lolos
            $fotoNama = $this->simpanFoto($request->foto, $nik);

            if (!$absensiHariIni) {
                // === ABSEN MASUK via Face ===
                // Cek hari libur (bebas_absen tetap lolos)
                if (!$karyawan->isBebasAbsen() && HariLibur::isLibur(today())) {
                    Storage::disk('public')->delete($fotoNama);
                    $namaLibur = HariLibur::whereDate('tanggal', today())->value('nama');
                    return response()->json(['success' => false, 'message' => "Hari ini libur: {$namaLibur}. Absensi tidak diperlukan."]);
                }

                return $this->prosesAbsenMasuk($karyawan, $shift, $jamSekarang, 'face', $confidence, $fotoNama);
            } else {
                // === ABSEN KELUAR via Face ===
                return $this->prosesAbsenKeluar($karyawan, $absensiHariIni, $shift, $jamSekarang, 'face', $confidence, $fotoNama);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service pengenalan wajah tidak tersedia. Pastikan Python service aktif.',
            ], 503);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: Proses Absen MASUK (shared antara QR dan Face)
    // ─────────────────────────────────────────────────────────────

    private function prosesAbsenMasuk(Karyawan $karyawan, $shift, string $jamSekarang, string $metode, float $confidence = 100, ?string $fotoNama = null): \Illuminate\Http\JsonResponse
    {
        // Karyawan biasa tanpa shift → tolak
        if (!$shift && !$karyawan->isBebasAbsen()) {
            if ($fotoNama) Storage::disk('public')->delete($fotoNama);
            return response()->json([
                'success' => false,
                'message' => "{$karyawan->nama} belum memiliki shift kerja. Hubungi HRD untuk pengaturan shift.",
            ], 400);
        }

        // Cek window absen masuk — hanya untuk karyawan yang punya shift dan bukan bebas
        if ($shift && !$karyawan->isBebasAbsen()) {
            $cutoffJam         = (int) Pengaturan::get('cutoff_jam_masuk', 2);
            $toleransiAwal     = (int) Pengaturan::get('toleransi_masuk_awal_menit', 30);
            $batasCutoff       = date('H:i:s', strtotime($shift->jam_masuk) + ($cutoffJam * 3600));
            $jamMasukFormatted = substr($shift->jam_masuk, 0, 5);

            if (!$this->dalamWindowMasuk($shift->jam_masuk, $toleransiAwal, $cutoffJam, $jamSekarang)) {
                if ($fotoNama) Storage::disk('public')->delete($fotoNama);
                $detikScanKeJamMasuk = $this->detikSetelahBase($jamSekarang, $shift->jam_masuk);
                if ($detikScanKeJamMasuk > 0 && $detikScanKeJamMasuk < 12 * 3600) {
                    // Datang terlalu awal untuk jadwal shift-nya — BUKAN kegagalan,
                    // cuma info jadwal (misal karyawan dijadwalkan shift siang tapi
                    // datang pagi). Tandai eksplisit supaya frontend tampilkan netral.
                    $bukaJam = date('H:i', strtotime($shift->jam_masuk) - ($toleransiAwal * 60));
                    return response()->json([
                        'success'        => false,
                        'belum_dibuka'   => true,
                        'karyawan'       => $karyawan->nama,
                        'waktu_boleh_masuk' => $bukaJam,
                        'jam_shift'      => $jamMasukFormatted,
                        'message'        => "Absen masuk belum dibuka. Window absen dibuka pukul {$bukaJam} ({$toleransiAwal} menit sebelum shift). Shift mulai {$jamMasukFormatted}.",
                    ], 400);
                }
                $batasCutoffFormatted = substr($batasCutoff, 0, 5);
                return response()->json(['success' => false,
                    'message' => "Absen masuk ditutup. Batas waktu absen adalah {$batasCutoffFormatted} ({$cutoffJam} jam setelah jam masuk {$jamMasukFormatted}). Hubungi HRD jika ada keperluan mendesak."], 400);
            }
        }

        // Tentukan status: hadir atau terlambat
        $status = 'hadir';
        $keteranganTerlambat = null;
        $izinTerlambatId = null;

        if ($shift && !$karyawan->isBebasAbsen()) {
            $scanSudahShift = $this->detikSetelahBase($shift->jam_masuk, $jamSekarang) <= 12 * 3600;
            $batasTerlambat = date('H:i:s', strtotime($shift->jam_masuk) + ($shift->toleransi_menit * 60));
            if ($scanSudahShift && $this->sudahMelewati($shift->jam_masuk, $batasTerlambat, $jamSekarang)) {
                $status = 'terlambat';

                // Cek pengajuan izin terlambat (via WA) yang sudah disetujui HRD untuk hari ini.
                // Cutoff_jam_masuk sudah dicek di dalamWindowMasuk() sebelum blok ini, jadi
                // izin yang disetujui otomatis berlaku selama masih dalam batas hard-limit sistem.
                $izinTerlambat = IzinTerlambat::where('karyawan_id', $karyawan->id)
                    ->whereDate('tanggal', today())
                    ->where('status', 'disetujui')
                    ->latest('approved_at')
                    ->first();

                if ($izinTerlambat) {
                    $izinTerlambatId = $izinTerlambat->id;
                    $keteranganTerlambat = $izinTerlambat->keterangan
                        . ($izinTerlambat->catatan_approver ? ' | Catatan HRD: ' . $izinTerlambat->catatan_approver : '');
                }
            }
        }

        [$absensiMasuk, $baru] = DB::transaction(function () use (
            $karyawan, $shift, $jamSekarang, $status, $confidence, $fotoNama, $metode,
            $keteranganTerlambat, $izinTerlambatId
        ) {
            Karyawan::where('id', $karyawan->id)->lockForUpdate()->first();
            $existing = Absensi::where('karyawan_id', $karyawan->id)->whereDate('tanggal', today())->first();
            if ($existing) return [$existing, false];

            $data = [
                'karyawan_id' => $karyawan->id,
                'shift_id'    => $shift?->id ?? $karyawan->shift_id,
                'tanggal'     => today(),
                'jam_masuk'   => $jamSekarang,
                'status'      => $status,
                'metode'      => $metode,
            ];
            if ($fotoNama)                    $data['foto_masuk']       = $fotoNama;
            if ($metode === 'face')           $data['confidence_masuk'] = $confidence;
            if ($keteranganTerlambat)         $data['keterangan_terlambat'] = $keteranganTerlambat;
            if ($izinTerlambatId)             $data['izin_terlambat_id'] = $izinTerlambatId;

            return [Absensi::create($data), true];
        });

        if (!$baru) {
            if ($fotoNama) Storage::disk('public')->delete($fotoNama);
            return response()->json([
                'success'    => false,
                'karyawan'   => $karyawan->nama,
                'jam_masuk'  => substr($absensiMasuk->jam_masuk ?? '', 0, 5),
                'jam_keluar' => substr($absensiMasuk->jam_keluar ?? '', 0, 5),
                'message'    => "{$karyawan->nama} sudah absen masuk hari ini.",
            ]);
        }

        if ($status === 'terlambat' && !$keteranganTerlambat) {
            $this->kirimNotifTerlambat($karyawan, $jamSekarang, $shift);
        }

        return response()->json([
            'success'    => true,
            'tipe'       => 'masuk',
            'karyawan'   => $karyawan->nama,
            'nik'        => $karyawan->nik,
            'jabatan'    => $karyawan->jabatan,
            'departemen' => $karyawan->department?->name ?? $karyawan->departemen,
            'jam'        => now()->format('H:i:s'),
            'status'     => $status,
            'confidence' => $confidence,
            'foto'       => $karyawan->foto_wajah ? Storage::url($karyawan->foto_wajah) : null,
            'message'    => $keteranganTerlambat
                ? "Selamat datang, {$karyawan->nama}! Anda tercatat Terlambat (Berizin)."
                : "Selamat datang, {$karyawan->nama}! Absen masuk" . ($metode === 'qrcode' ? ' via QR Code' : '') . ".",
            'metode'     => $metode,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: Proses Absen KELUAR (shared antara QR dan Face)
    // ─────────────────────────────────────────────────────────────

    private function prosesAbsenKeluar(Karyawan $karyawan, Absensi $absensi, $shift, string $jamSekarang, string $metode, float $confidence = 100, ?string $fotoNama = null): \Illuminate\Http\JsonResponse
    {
        $durasiMenit    = $this->selisihMenit($absensi->jam_masuk, $jamSekarang);
        $minMenitKeluar = (int) Pengaturan::get('min_menit_keluar', 60);

        // Jika absensi kemarin (shift malam) dan durasiMenit sangat kecil,
        // kemungkinan ini scan masuk hari baru — beri pesan yang lebih jelas.
        // Ini kasus yang butuh perhatian HRD (bukan sekadar "belum waktunya"),
        // jadi tetap ditandai sebagai kondisi non-netral di frontend. Dicek
        // lebih dulu terlepas dari aturan wajib-izin, karena ini bukan soal
        // pulang cepat — ini kemungkinan shift kemarin yang belum ditutup.
        if ($durasiMenit < $minMenitKeluar && $absensi->tanggal->isYesterday() && $durasiMenit < 30) {
            if ($fotoNama) Storage::disk('public')->delete($fotoNama);
            return response()->json([
                'success'  => false,
                'karyawan' => $karyawan->nama,
                'message'  => "Anda tercatat masuk kemarin ({$absensi->tanggal->format('d/m')}) pukul " . substr($absensi->jam_masuk ?? '', 0, 5) . " dan belum scan keluar. Jika ingin absen masuk shift baru, hubungi HRD untuk menutup absensi kemarin terlebih dahulu.",
            ], 400);
        }

        $pulangCepat = false;
        if ($shift && $shift->jam_keluar && !$karyawan->isBebasAbsen()) {
            $toleransiPulang   = (int) Pengaturan::get('toleransi_pulang_cepat_menit', 15);
            $menitMenujuKeluar = $this->selisihMenit($jamSekarang, $shift->jam_keluar);
            // Pulang cepat: masih ada > toleransi menit sebelum jadwal keluar (max 12 jam agar cross-midnight tidak salah arah)
            $pulangCepat = $menitMenujuKeluar > $toleransiPulang && $menitMenujuKeluar < 12 * 60;
        }

        // Cek izin pulang cepat yang sudah disetujui HRD untuk tanggal absensi ini.
        // Jika ada, tandai tetap sebagai berizin (bukan dibatalkan) supaya tidak
        // memicu notifikasi ke HRD walau statusnya tetap pulang_cepat=true.
        $izinPulangCepatId    = null;
        $keteranganPulangCepat = null;
        if ($pulangCepat) {
            $izinPulangCepat = IzinPulangCepat::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', $absensi->tanggal)
                ->where('status', 'disetujui')
                ->latest('approved_at')
                ->first();

            if ($izinPulangCepat) {
                $izinPulangCepatId     = $izinPulangCepat->id;
                $keteranganPulangCepat = $izinPulangCepat->keterangan
                    . ($izinPulangCepat->catatan_approver ? ' | Catatan HRD: ' . $izinPulangCepat->catatan_approver : '');
            }
        }

        // Jika "wajib izin pulang cepat" diaktifkan HRD, TOLAK absen keluar sebelum
        // jam shift selesai kecuali sudah ada izin disetujui untuk hari ini. Dicek
        // SEBELUM guard min_menit_keluar di bawah, supaya pesan yang tampil selalu
        // merujuk ke jam shift selesai (bukan "mulai jam X" dari min_menit_keluar
        // yang bisa jatuh jauh lebih awal dan menyesatkan karyawan).
        if ($pulangCepat && !$izinPulangCepatId && Pengaturan::get('wajib_izin_pulang_cepat', '0') === '1') {
            if ($fotoNama) Storage::disk('public')->delete($fotoNama);
            $jamShiftFormatted = substr($shift->jam_keluar, 0, 5);
            return response()->json([
                'success'          => false,
                'wajib_izin_pulang_cepat' => true,
                'karyawan'         => $karyawan->nama,
                'jam_shift_keluar' => $jamShiftFormatted,
                'message'          => "{$karyawan->nama} belum bisa absen keluar sebelum jam shift selesai ({$jamShiftFormatted}). Hubungi HRD untuk mengajukan izin pulang cepat jika ada keperluan mendesak.",
            ], 400);
        }

        // Guard minimal menit kerja — tetap berlaku sebagai batas bawah mutlak,
        // termasuk untuk karyawan yang sudah punya izin pulang cepat disetujui.
        if ($durasiMenit < $minMenitKeluar) {
            if ($fotoNama) Storage::disk('public')->delete($fotoNama);
            $sisaMenit        = $minMenitKeluar - $durasiMenit;
            $waktuBolehKeluar = date('H:i', strtotime($absensi->jam_masuk) + ($minMenitKeluar * 60));
            return response()->json([
                'success'            => false,
                'belum_waktu_keluar' => true,
                'karyawan'           => $karyawan->nama,
                'jam_masuk'          => substr($absensi->jam_masuk ?? '', 0, 5),
                'waktu_boleh_keluar' => $waktuBolehKeluar,
                'sisa_menit'         => $sisaMenit,
                'message'            => "{$karyawan->nama} sudah absen masuk pukul " . substr($absensi->jam_masuk, 0, 5) . ". Absen keluar bisa mulai pukul {$waktuBolehKeluar} ({$sisaMenit} menit lagi).",
            ], 400);
        }

        $updateData = [
            'jam_keluar'   => $jamSekarang,
            'durasi_menit' => max(0, $durasiMenit),
            'pulang_cepat' => $pulangCepat,
        ];
        if ($fotoNama)           $updateData['foto_keluar']        = $fotoNama;
        if ($metode === 'face') $updateData['confidence_keluar'] = $confidence;
        if ($izinPulangCepatId) $updateData['izin_pulang_cepat_id']     = $izinPulangCepatId;
        if ($keteranganPulangCepat) $updateData['keterangan_pulang_cepat'] = $keteranganPulangCepat;

        $absensi->update($updateData);

        if ($pulangCepat && !$izinPulangCepatId) {
            $this->kirimNotifPulangCepat($karyawan, $jamSekarang, $shift);
        }

        // Tanggal referensi untuk cek lembur: pakai tanggal absensi (bisa kemarin untuk shift malam)
        $potensiLembur = $this->cekPotensiLembur($karyawan, $shift, $jamSekarang, $absensi->tanggal);

        $durasiFormat = ($durasiMenit >= 60)
            ? intdiv($durasiMenit, 60) . ' jam ' . ($durasiMenit % 60) . ' menit'
            : $durasiMenit . ' menit';

        $msgTambahan = '';
        if ($pulangCepat && $izinPulangCepatId) {
            $msgTambahan = ' (Pulang lebih awal - Berizin)';
        } elseif ($pulangCepat) {
            $msgTambahan = ' (Pulang lebih awal)';
        }
        if ($potensiLembur) $msgTambahan .= ' 🕐 Potensi lembur terdeteksi - cek portal.';

        return response()->json([
            'success'        => true,
            'tipe'           => 'keluar',
            'karyawan'       => $karyawan->nama,
            'nik'            => $karyawan->nik,
            'jabatan'        => $karyawan->jabatan,
            'departemen'     => $karyawan->department?->name ?? $karyawan->departemen,
            'jam'            => now()->format('H:i:s'),
            'confidence'     => $confidence,
            'foto'           => $karyawan->foto_wajah ? Storage::url($karyawan->foto_wajah) : null,
            'durasi'         => $durasiFormat,
            'pulang_cepat'   => $pulangCepat,
            'izin_pulang_cepat' => (bool) $izinPulangCepatId,
            'potensi_lembur' => $potensiLembur,
            'message'        => "Sampai jumpa, {$karyawan->nama}! Durasi kerja: {$durasiFormat}.{$msgTambahan}",
            'metode'         => $metode,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    private function simpanFoto(string $fotoBase64, string $nik): string
    {
        $fotoData = $fotoBase64;
        if (str_contains($fotoData, ',')) {
            $fotoData = explode(',', $fotoData)[1];
        }
        $fotoNama = 'absensi/' . date('Ymd') . '_' . $nik . '_' . time() . '.jpg';
        Storage::disk('public')->put($fotoNama, base64_decode($fotoData));
        return $fotoNama;
    }

    /**
     * Apakah shift ini cross-midnight (shift malam)?
     * jam_keluar < jam_masuk berarti keluar keesokan harinya.
     */

    /**
     * Resolve shift aktif karyawan untuk tanggal hari ini.
     * Employeshift (jadwal khusus) lebih prioritas dari shift default.
     */
    private function resolveShift(Karyawan $karyawan, ?\Carbon\Carbon $tanggal = null)
    {
        return $karyawan->resolveShift($tanggal);
    }

    private function isShiftMalam($shift): bool
    {
        if (!$shift || !$shift->jam_keluar) return false;
        return strtotime($shift->jam_keluar) < strtotime($shift->jam_masuk);
    }

    /**
     * Cek apakah scan sekarang adalah lanjutan (absen keluar) dari shift malam
     * kemarin yang belum ditutup. Pakai shift yang tersimpan di record kemarin
     * itu sendiri (bukan resolveShift() hari ini) supaya tidak salah base
     * perhitungan pulang cepat/lembur kalau jadwal karyawan berubah.
     *
     * Guard tambahan: jika karyawan sudah dijadwalkan shift BARU hari ini dan
     * scan sekarang jatuh di window absen masuk shift baru tsb, JANGAN anggap
     * ini lanjutan shift malam kemarin — supaya absen masuk shift baru tidak
     * ketelan jadi "penutup" shift malam kemarin (kasus: shift malam lalu di
     * hari yang sama dijadwalkan shift pagi/siang lagi).
     */
    private function cekAbsensiKemarinShiftMalam(Karyawan $karyawan, $shiftHariIni, string $jamSekarang): ?Absensi
    {
        $absensiKemarin = Absensi::with('shift')
            ->where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today()->subDay())
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->first();

        if (!$absensiKemarin || !$this->isShiftMalam($absensiKemarin->shift)) {
            return null;
        }

        $shiftBaruBeda = $shiftHariIni && $shiftHariIni->id !== $absensiKemarin->shift_id;
        if ($shiftBaruBeda && !$karyawan->isBebasAbsen()) {
            // Sudah ada absensi shift baru hari ini (jam_masuk terisi) — shift malam
            // kemarin sudah "dilewati", jangan proses lagi via scan. Harus ditutup
            // manual oleh HRD (koreksi absensi) supaya tidak dobel-hitung durasi kerja.
            $absensiHariIniShiftBaru = Absensi::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', today())
                ->whereNotNull('jam_masuk')
                ->exists();
            if ($absensiHariIniShiftBaru) {
                return null;
            }

            $toleransiAwal = (int) Pengaturan::get('toleransi_masuk_awal_menit', 30);
            $cutoffJam     = (int) Pengaturan::get('cutoff_jam_masuk', 2);
            if ($this->dalamWindowMasuk($shiftHariIni->jam_masuk, $toleransiAwal, $cutoffJam, $jamSekarang)) {
                return null; // scan ini absen masuk shift baru, bukan penutup shift malam kemarin
            }
        }

        return $absensiKemarin;
    }

    /**
     * Hitung selisih menit antara dua jam (H:i:s), cross-midnight aware.
     */
    private function selisihMenit(string $jamAwal, string $jamAkhir): int
    {
        $detik = strtotime($jamAkhir) - strtotime($jamAwal);
        if ($detik < 0) $detik += 86400;
        return (int) round($detik / 60);
    }

    /**
     * Hitung detik $jamAcuan relatif terhadap $jamBase, cross-midnight aware.
     * Selalu return nilai dalam [0, 86400).
     */
    private function detikSetelahBase(string $jamBase, string $jamAcuan): int
    {
        $d = strtotime($jamAcuan) - strtotime($jamBase);
        if ($d < 0) $d += 86400;
        return $d;
    }

    /**
     * Apakah $jamAcuan sudah melewati $jamBatas?
     * Keduanya diukur relatif dari $jamBase (anchor = jam_masuk shift).
     */
    private function sudahMelewati(string $jamBase, string $jamBatas, string $jamAcuan): bool
    {
        return $this->detikSetelahBase($jamBase, $jamAcuan) > $this->detikSetelahBase($jamBase, $jamBatas);
    }

    /**
     * Apakah $jamAcuan sebelum $jamBatas?
     * Keduanya diukur relatif dari $jamBase (cross-midnight aware).
     */
    private function sebelumBatas(string $jamBase, string $jamBatas, string $jamAcuan): bool
    {
        return $this->detikSetelahBase($jamBase, $jamAcuan) < $this->detikSetelahBase($jamBase, $jamBatas);
    }

    /**
     * Apakah $jamAcuan berada di dalam window absen masuk yang valid?
     * Window kiri: (jam_masuk - toleransi_awal_menit), window kanan: (jam_masuk + cutoff_jam*3600).
     */
    private function dalamWindowMasuk(string $jamMasukShift, int $toleransiAwalMenit, int $cutoffJam, string $jamAcuan): bool
    {
        $windowStart  = date('H:i:s', strtotime($jamMasukShift) - ($toleransiAwalMenit * 60));
        $windowEnd    = date('H:i:s', strtotime($jamMasukShift) + ($cutoffJam * 3600));
        $detikKeEnd   = $this->detikSetelahBase($windowStart, $windowEnd);
        $detikKeAcuan = $this->detikSetelahBase($windowStart, $jamAcuan);
        return $detikKeAcuan <= $detikKeEnd;
    }

    /**
     * Deteksi potensi lembur saat scan keluar.
     * $tanggalAbsensi: tanggal absensi (bisa kemarin untuk shift malam).
     */
    private function cekPotensiLembur($karyawan, $shift, string $jamSekarang, $tanggalAbsensi = null): bool
    {
        if (!$shift || !$shift->jam_keluar) return false;

        $batasMenit   = (int) Pengaturan::get('min_lembur_otomatis_menit', 30);
        $selisihMenit = $this->selisihMenit($shift->jam_keluar, $jamSekarang);

        // Guard: jika scan jauh sebelum jam_keluar (> 12 jam relatif), bukan lembur
        if ($selisihMenit > 12 * 60) return false;
        if ($selisihMenit < $batasMenit) return false;

        // Gunakan tanggal absensi sebagai referensi (penting untuk shift malam)
        $tanggalRef = $tanggalAbsensi ?? today();

        // Sudah ada klaim lembur pada tanggal absensi tersebut? Skip
        $sudahAda = Lemburan::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $tanggalRef)
            ->exists();
        if ($sudahAda) return false;

        // Cek SPK aktif hari ini. Record lembur HANYA dibuat otomatis jika ada
        // SPK Lembur resmi (sudah diotorisasi atasan sebelumnya) — tanpa SPK,
        // pulang telat TIDAK membuat record lembur apapun, supaya SPV/manajer
        // tidak perlu menyortir antrian approval berisi "lembur" yang sebenarnya
        // belum pernah diajukan siapa pun. Karyawan tetap dinotif untuk mengajukan
        // sendiri lewat portal jika memang ingin klaim lembur.
        $spk = SpkLembur::spkAktifUntuk($karyawan->id);

        if ($spk) {
            Lemburan::create([
                'karyawan_id'   => $karyawan->id,
                'spk_lembur_id' => $spk->id,
                'jenis'         => 'spk',
                'tanggal'       => $tanggalRef,
                'jam_mulai'     => $shift->jam_keluar,
                'jam_selesai'   => $jamSekarang,
                'total_jam'     => round($selisihMenit / 60, 2),
                'status'        => 'pending',
                'alasan'        => "Lembur berdasar SPK {$spk->nomor_spk}",
                'keterangan'    => 'Auto-detected dari kiosk (SPK aktif). Lengkapi dari portal.',
                'is_mendadak'   => false,
            ]);
        }

        if ($karyawan->user) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'potensi_lembur',
                'notifiable_type' => User::class,
                'notifiable_id'   => $karyawan->user->id,
                'data'            => json_encode([
                    'title' => '🕐 Potensi Lembur Terdeteksi',
                    'body'  => $spk
                        ? 'Anda pulang ' . $selisihMenit . ' menit setelah jam shift. Klaim lembur berdasar SPK sudah tercatat, silakan lengkapi di portal.'
                        : 'Anda pulang ' . $selisihMenit . ' menit setelah jam shift. Jika ini lembur, silakan ajukan klaim lembur di portal.',
                    'url'   => '/portal/lembur',
                    'icon'  => 'fas fa-business-time',
                    'color' => '#92400e',
                    'bg'    => '#fef9c3',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Kirim notifikasi keterlambatan ke HRD dan supervisor karyawan.
     */
    private function kirimNotifTerlambat($karyawan, string $jamMasuk, $shift): void
    {
        if (Pengaturan::get('notif_terlambat_aktif', '1') !== '1') return;

        $menitTerlambat = $shift
            ? max(0, $this->selisihMenit($shift->jam_masuk, $jamMasuk) - ($shift->toleransi_menit ?? 0))
            : 0;

        $data = [
            'title' => '⚠️ Karyawan Terlambat',
            'body'  => "{$karyawan->nama} absen masuk pukul " . substr($jamMasuk, 0, 5) .
                       ($menitTerlambat > 0 ? " (terlambat {$menitTerlambat} menit)" : '') . ".",
            'url'   => '/admin/absensi',
            'icon'  => 'fas fa-clock',
            'color' => '#c53030',
            'bg'    => '#fff5f5',
        ];

        foreach ($this->getUserHrdDanSupervisor($karyawan) as $userId) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'karyawan_terlambat',
                'notifiable_type' => User::class,
                'notifiable_id'   => $userId,
                'data'            => json_encode($data),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // Reminder isi alasan ke karyawan sendiri
        if (Pengaturan::get('alasan_terlambat_wajib', '1') === '1' && $karyawan->user) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'reminder_alasan_terlambat',
                'notifiable_type' => User::class,
                'notifiable_id'   => $karyawan->user->id,
                'data'            => json_encode([
                    'title' => '📝 Isi Alasan Keterlambatan',
                    'body'  => 'Anda tercatat terlambat hari ini. Mohon isi alasan keterlambatan di portal absensi.',
                    'url'   => '/portal/absensi',
                    'icon'  => 'fas fa-exclamation-circle',
                    'color' => '#c05621',
                    'bg'    => '#fffaf0',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Kirim notifikasi pulang cepat ke HRD dan supervisor.
     */
    private function kirimNotifPulangCepat($karyawan, string $jamKeluar, $shift): void
    {
        $data = [
            'title' => '🏃 Karyawan Pulang Lebih Awal',
            'body'  => "{$karyawan->nama} scan pulang pukul " . substr($jamKeluar, 0, 5) .
                       ($shift ? ", sebelum jam shift selesai (" . substr($shift->jam_keluar, 0, 5) . ")." : "."),
            'url'   => '/admin/absensi',
            'icon'  => 'fas fa-sign-out-alt',
            'color' => '#744210',
            'bg'    => '#fefcbf',
        ];

        foreach ($this->getUserHrdDanSupervisor($karyawan) as $userId) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'pulang_cepat',
                'notifiable_type' => User::class,
                'notifiable_id'   => $userId,
                'data'            => json_encode($data),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    /**
     * Ambil user ID HRD dan supervisor yang relevan untuk notifikasi.
     */
    private function getUserHrdDanSupervisor($karyawan): array
    {
        return User::whereIn('role', ['hrd', 'admin', 'superadmin'])->pluck('id')->toArray();
    }
}
