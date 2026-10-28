<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Karyawan;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiImportController extends Controller
{
    public function index()
    {
        return view('admin.absensi.import');
    }

    public function template()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-absensi.csv"',
        ];

        $columns = ['nik_karyawan', 'tanggal', 'jam_masuk', 'jam_keluar', 'status', 'keterangan'];
        $contoh  = ['1234567890', '2026-04-01', '08:05:00', '17:00:00', 'hadir', 'Koreksi manual'];
        $contoh2 = ['1234567891', '2026-04-01', '08:45:00', '', 'terlambat', ''];

        $callback = function() use ($columns, $contoh, $contoh2) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns);
            fputcsv($out, $contoh);
            fputcsv($out, $contoh2);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:csv,txt|max:5120',
            'mode'   => 'required|in:skip,update',
        ]);

        $mode   = $request->mode; // skip = lewati jika sudah ada, update = timpa
        $path   = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau tidak valid.');
        }
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        $header    = array_map('trim', $header);

        $required = ['nik_karyawan', 'tanggal', 'jam_masuk', 'status'];
        $missing  = array_diff($required, $header);
        if ($missing) {
            fclose($handle);
            return back()->with('error', 'Kolom wajib tidak ada: ' . implode(', ', $missing));
        }

        $validStatus = ['hadir','terlambat','izin','sakit','alpha'];
        $berhasil = 0; $dilewati = 0; $diupdate = 0; $errors = []; $baris = 1;

        // Cache NIK → karyawan_id dan shift_id
        $nikMap   = Karyawan::pluck('id', 'nik');
        $shiftMap = Karyawan::pluck('shift_id', 'id');

        while (($row = fgetcsv($handle)) !== false) {
            $baris++;
            if (count($row) < count($header)) continue;
            $data = array_combine($header, array_map('trim', $row));

            // Validasi NIK
            $nik = $data['nik_karyawan'] ?? '';
            if (!isset($nikMap[$nik])) {
                $errors[] = "Baris {$baris}: NIK '{$nik}' tidak ditemukan.";
                $dilewati++;
                continue;
            }
            $karyawanId = $nikMap[$nik];

            // Validasi tanggal
            try {
                $tanggal = Carbon::parse($data['tanggal'])->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = "Baris {$baris}: Format tanggal tidak valid '{$data['tanggal']}'.";
                $dilewati++;
                continue;
            }

            // Validasi status
            $status = strtolower($data['status'] ?? 'hadir');
            if (!in_array($status, $validStatus)) {
                $errors[] = "Baris {$baris}: Status '{$status}' tidak valid. Gunakan: " . implode(', ', $validStatus);
                $dilewati++;
                continue;
            }

            // Format jam
            try {
                $jamMasuk  = !empty($data['jam_masuk'])  ? Carbon::parse($data['jam_masuk'])->format('H:i:s') : null;
                $jamKeluar = !empty($data['jam_keluar']) ? Carbon::parse($data['jam_keluar'])->format('H:i:s') : null;
            } catch (\Exception $e) {
                $errors[] = "Baris {$baris}: Format jam tidak valid (jam_masuk: '{$data['jam_masuk']}', jam_keluar: '{$data['jam_keluar']}').";
                $dilewati++;
                continue;
            }

            // Hitung durasi
            $durasiMenit = null;
            if ($jamMasuk && $jamKeluar) {
                $detik = strtotime($jamKeluar) - strtotime($jamMasuk);
                if ($detik < 0) $detik += 86400; // cross-midnight aware
                $durasiMenit = (int) round($detik / 60);
            }

            $existing = Absensi::where('karyawan_id', $karyawanId)->whereDate('tanggal', $tanggal)->first();

            if ($existing) {
                if ($mode === 'skip') {
                    $errors[] = "Baris {$baris}: Data absensi {$nik} tgl {$tanggal} sudah ada, dilewati.";
                    $dilewati++;
                    continue;
                }
                // mode=update: timpa
                $existing->update([
                    'jam_masuk'    => $jamMasuk,
                    'jam_keluar'   => $jamKeluar,
                    'durasi_menit' => $durasiMenit,
                    'status'       => $status,
                    'keterangan'   => $data['keterangan'] ?? $existing->keterangan,
                    'metode'       => 'manual',
                ]);
                $diupdate++;
            } else {
                Absensi::create([
                    'karyawan_id'  => $karyawanId,
                    'shift_id'     => $shiftMap[$karyawanId] ?? null,
                    'tanggal'      => $tanggal,
                    'jam_masuk'    => $jamMasuk,
                    'jam_keluar'   => $jamKeluar,
                    'durasi_menit' => $durasiMenit,
                    'status'       => $status,
                    'keterangan'   => $data['keterangan'] ?? null,
                    'metode'       => 'manual',
                ]);
                $berhasil++;
            }
        }

        fclose($handle);

        $total = $berhasil + $diupdate;
        AuditLog::catat('import', "Import absensi: {$berhasil} baru, {$diupdate} diupdate, {$dilewati} dilewati (mode: {$mode})");

        $msg = "Import selesai: <strong>{$berhasil}</strong> data baru";
        if ($diupdate > 0) $msg .= ", <strong>{$diupdate}</strong> diperbarui";
        if ($dilewati > 0) $msg .= ", <strong>{$dilewati}</strong> dilewati";

        return redirect()->route('admin.absensi.import')
            ->with('success', $msg)
            ->with('importErrors', $errors);
    }
}
