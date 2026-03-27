<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
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
        $karyawan = Karyawan::where('nik', $nik)->where('status', 'aktif')->first();

        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'NIK tidak ditemukan atau karyawan tidak aktif.']);
        }

        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
                                 ->whereDate('tanggal', today())
                                 ->first();

        $shift       = $karyawan->shift;
        $jamSekarang = now()->format('H:i:s');

        if (!$absensiHariIni) {
            // Absen MASUK via QR
            $status = 'hadir';
            if ($shift) {
                $batasTerlambat = date('H:i:s', strtotime($shift->jam_masuk) + ($shift->toleransi_menit * 60));
                if ($jamSekarang > $batasTerlambat) $status = 'terlambat';
            }

            Absensi::create([
                'karyawan_id' => $karyawan->id,
                'shift_id'    => $karyawan->shift_id,
                'tanggal'     => today(),
                'jam_masuk'   => $jamSekarang,
                'status'      => $status,
                'metode'      => 'qrcode',
            ]);

            return response()->json([
                'success'    => true,
                'tipe'       => 'masuk',
                'karyawan'   => $karyawan->nama,
                'nik'        => $karyawan->nik,
                'jabatan'    => $karyawan->jabatan,
                'departemen' => $karyawan->departemen,
                'jam'        => now()->format('H:i:s'),
                'status'     => $status,
                'confidence' => 100,
                'foto'       => $karyawan->foto_wajah ? Storage::url($karyawan->foto_wajah) : null,
                'message'    => "Selamat datang, {$karyawan->nama}! Absen masuk via QR Code.",
                'metode'     => 'qrcode',
            ]);

        } elseif ($absensiHariIni->jam_keluar === null) {
            // Absen KELUAR via QR — hitung durasi dan deteksi pulang cepat
            $durasiMenit = (int) round(
                (strtotime($jamSekarang) - strtotime($absensiHariIni->jam_masuk)) / 60
            );
            $pulangCepat = false;
            if ($shift && $shift->jam_keluar) {
                $pulangCepat = $jamSekarang < $shift->jam_keluar;
            }

                $absensiHariIni->update([
                'jam_keluar'  => $jamSekarang,
                'durasi_menit'=> max(0, $durasiMenit),
                'pulang_cepat'=> $pulangCepat,
                'metode'      => 'qrcode',
            ]);

            $durasiFormat = ($durasiMenit >= 60)
                ? intdiv($durasiMenit, 60) . ' jam ' . ($durasiMenit % 60) . ' menit'
                : $durasiMenit . ' menit';

            return response()->json([
                'success'      => true,
                'tipe'         => 'keluar',
                'karyawan'     => $karyawan->nama,
                'nik'          => $karyawan->nik,
                'jabatan'      => $karyawan->jabatan,
                'departemen'   => $karyawan->departemen,
                'jam'          => now()->format('H:i:s'),
                'confidence'   => 100,
                'foto'         => $karyawan->foto_wajah ? Storage::url($karyawan->foto_wajah) : null,
                'durasi'       => $durasiFormat,
                'pulang_cepat' => $pulangCepat,
                'message'      => "Sampai jumpa, {$karyawan->nama}! Durasi kerja: {$durasiFormat}." . ($pulangCepat ? ' (Pulang lebih awal)' : ''),
                'metode'       => 'qrcode',
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => "{$karyawan->nama} sudah absen masuk dan keluar hari ini.",
            ]);
        }
    }

    public function scan(Request $request)
    {
        $request->validate(['foto' => 'required|string']);

        try {
            $response = Http::timeout(30)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/kenali', [
                'foto' => $request->foto,
            ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Wajah tidak dikenali'], 404);
            }

            $hasil = $response->json();

            if (!$hasil['success']) {
                return response()->json(['success' => false, 'message' => $hasil['message']], 404);
            }

            $nik      = $hasil['nik'];
            $confidence = $hasil['confidence'];

            $karyawan = Karyawan::where('nik', $nik)->where('status', 'aktif')->first();

            if (!$karyawan) {
                return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan atau tidak aktif'], 404);
            }

            // Simpan foto scan
            $fotoData = $request->foto;
            if (str_contains($fotoData, ',')) {
                $fotoData = explode(',', $fotoData)[1];
            }
            $fotoNama    = 'absensi/' . date('Ymd') . '_' . $nik . '_' . time() . '.jpg';
            Storage::disk('public')->put($fotoNama, base64_decode($fotoData));

            // Cek absensi hari ini
            $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
                                     ->whereDate('tanggal', today())
                                     ->first();

            $shift     = $karyawan->shift;
            $jamSekarang = now()->format('H:i:s');

            if (!$absensiHariIni) {
                // Absen MASUK
                $status = 'hadir';
                if ($shift) {
                    $jamMasukShift    = $shift->jam_masuk;
                    $batasTerlambat   = date('H:i:s', strtotime($jamMasukShift) + ($shift->toleransi_menit * 60));
                    if ($jamSekarang > $batasTerlambat) {
                        $status = 'terlambat';
                    }
                }

                $absensi = Absensi::create([
                    'karyawan_id'      => $karyawan->id,
                    'shift_id'         => $karyawan->shift_id,
                    'tanggal'          => today(),
                    'jam_masuk'        => $jamSekarang,
                    'status'           => $status,
                    'confidence_masuk' => $confidence,
                    'foto_masuk'       => $fotoNama,
                ]);

                return response()->json([
                    'success'    => true,
                    'tipe'       => 'masuk',
                    'karyawan'   => $karyawan->nama,
                    'nik'        => $karyawan->nik,
                    'jabatan'    => $karyawan->jabatan,
                    'departemen' => $karyawan->departemen,
                    'jam'        => now()->format('H:i:s'),
                    'status'     => $status,
                    'confidence' => $confidence,
                    'foto'       => $karyawan->foto_wajah ? Storage::url($karyawan->foto_wajah) : null,
                    'message'    => "Selamat datang, {$karyawan->nama}! Absen masuk berhasil.",
                ]);

            } elseif ($absensiHariIni->jam_keluar === null) {
                // Absen KELUAR (Face) — hitung durasi dan deteksi pulang cepat
                $durasiMenit = (int) round(
                    (strtotime($jamSekarang) - strtotime($absensiHariIni->jam_masuk)) / 60
                );
                $pulangCepat = false;
                if ($shift && $shift->jam_keluar) {
                    $pulangCepat = $jamSekarang < $shift->jam_keluar;
                }

                $absensiHariIni->update([
                    'jam_keluar'        => $jamSekarang,
                    'durasi_menit'      => max(0, $durasiMenit),
                    'pulang_cepat'      => $pulangCepat,
                    'confidence_keluar' => $confidence,
                    'foto_keluar'       => $fotoNama,
                ]);

                $durasiFormat = ($durasiMenit >= 60)
                    ? intdiv($durasiMenit, 60) . ' jam ' . ($durasiMenit % 60) . ' menit'
                    : $durasiMenit . ' menit';

                return response()->json([
                    'success'      => true,
                    'tipe'         => 'keluar',
                    'karyawan'     => $karyawan->nama,
                    'nik'          => $karyawan->nik,
                    'jabatan'      => $karyawan->jabatan,
                    'departemen'   => $karyawan->departemen,
                    'jam'          => now()->format('H:i:s'),
                    'confidence'   => $confidence,
                    'foto'         => $karyawan->foto_wajah ? Storage::url($karyawan->foto_wajah) : null,
                    'durasi'       => $durasiFormat,
                    'pulang_cepat' => $pulangCepat,
                    'message'      => "Sampai jumpa, {$karyawan->nama}! Durasi kerja: {$durasiFormat}." . ($pulangCepat ? ' (Pulang lebih awal)' : ''),
                ]);

            } else {
                return response()->json([
                    'success'    => false,
                    'karyawan'   => $karyawan->nama,
                    'message'    => "{$karyawan->nama} sudah absen masuk dan keluar hari ini.",
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service pengenalan wajah tidak tersedia. Pastikan Python service aktif.',
            ], 503);
        }
    }
}
