<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CutiSaldo;
use App\Models\Department;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanImportController extends Controller
{
    /**
     * Tampilkan halaman import + preview template
     */
    public function index()
    {
        return view('admin.karyawan.import');
    }

    /**
     * Download template CSV
     */
    public function template()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-karyawan.csv"',
        ];

        $columns = [
            'nik', 'kode_karyawan', 'inisial', 'nama', 'jenis_kelamin',
            'departemen', 'jabatan', 'status',
            'email', 'no_hp', 'alamat',
            'pendidikan_terakhir', 'marital_status', 'jumlah_tanggungan',
            'birth_date', 'birth_place', 'religion',
        ];

        $contoh = [
            '1234567890', 'EMP001', 'BS', 'Budi Santoso', 'L',
            'Produksi', 'Operator', 'kontrak',
            'budi@email.com', '08123456789', 'Jl. Merdeka No.1',
            'SMA/SMK', 'Menikah', '2',
            '1990-05-15', 'Jakarta', 'Islam',
        ];

        $callback = function () use ($columns, $contoh) {
            $out = fopen('php://output', 'w');
            // BOM untuk Excel UTF-8
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns);
            fputcsv($out, $contoh);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Proses import CSV
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'file.mimes' => 'File harus berformat CSV.',
        ]);

        $path    = $request->file('file')->getRealPath();
        $handle  = fopen($path, 'r');

        // Baca header baris pertama (strip BOM jika ada)
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau tidak valid.');
        }
        // Strip BOM dari header pertama
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        $header = array_map('trim', $header);

        $required = ['nik', 'nama', 'departemen', 'jabatan', 'status', 'jenis_kelamin'];
        $missing  = array_diff($required, $header);
        if ($missing) {
            fclose($handle);
            return back()->with('error', 'Kolom wajib tidak ditemukan: ' . implode(', ', $missing));
        }

        $berhasil = 0;
        $dilewati = 0;
        $errors   = [];
        $warnings = [];
        $baris    = 1;

        // Cache semua departemen: lowercase name → id (untuk mapping case-insensitive)
        $deptMap = Department::all()->mapWithKeys(
            fn($d) => [strtolower(trim($d->name)) => $d->id]
        );
        $posMap = \App\Models\Position::all()->mapWithKeys(
            fn($p) => [strtolower(trim($p->name)) => $p->id]
        );

        while (($row = fgetcsv($handle)) !== false) {
            $baris++;
            if (count($row) < count($header)) continue; // skip baris kosong

            $data = array_combine($header, array_map('trim', $row));

            // Validasi minimal
            if (empty($data['nik']) || empty($data['nama'])) {
                $errors[] = "Baris {$baris}: NIK dan Nama wajib diisi.";
                $dilewati++;
                continue;
            }

            // Validasi enum
            if (!in_array($data['jenis_kelamin'] ?? '', ['L','P'])) {
                $errors[] = "Baris {$baris} ({$data['nama']}): jenis_kelamin harus L atau P.";
                $dilewati++;
                continue;
            }
            if (!in_array($data['status'] ?? '', ['kontrak','tetap','magang','training','outsourcing','nonaktif'])) {
                $errors[] = "Baris {$baris} ({$data['nama']}): status tidak valid (kontrak/tetap/magang/training/outsourcing/nonaktif).";
                $dilewati++;
                continue;
            }

            // Skip jika NIK sudah ada
            if (Karyawan::where('nik', $data['nik'])->exists()) {
                $errors[] = "Baris {$baris}: NIK {$data['nik']} sudah terdaftar, dilewati.";
                $dilewati++;
                continue;
            }

            $fillable = [
                'nik', 'kode_karyawan', 'inisial', 'nama', 'jenis_kelamin',
                'departemen', 'jabatan', 'status',
                'email', 'no_hp', 'alamat',
                'pendidikan_terakhir', 'marital_status', 'jumlah_tanggungan',
                'birth_date', 'birth_place', 'religion',
            ];

            $insert = [];
            foreach ($fillable as $col) {
                if (isset($data[$col]) && $data[$col] !== '') {
                    $insert[$col] = $data[$col];
                }
            }

            // jumlah_tanggungan: pastikan integer >= 0
            if (isset($insert['jumlah_tanggungan'])) {
                $insert['jumlah_tanggungan'] = max(0, (int) $insert['jumlah_tanggungan']);
            }

            // birth_date: pastikan format valid
            if (!empty($insert['birth_date'])) {
                try {
                    $insert['birth_date'] = \Carbon\Carbon::parse($insert['birth_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    unset($insert['birth_date']);
                }
            }

            // Auto-map departemen text → department_id FK — kalau nama tidak cocok
            // persis dengan master data, catat sebagai warning (bukan gagal, karyawan
            // tetap dibuat) supaya HRD tahu department_id-nya kosong dan perlu dicek manual.
            if (!empty($insert['departemen'])) {
                $key = strtolower(trim($insert['departemen']));
                if (isset($deptMap[$key])) {
                    $insert['department_id'] = $deptMap[$key];
                } else {
                    $warnings[] = "Baris {$baris} ({$data['nama']}): departemen \"{$insert['departemen']}\" tidak cocok dengan master data — department_id dikosongkan, cek manual.";
                }
            }

            // Auto-map jabatan text → position_id FK, sama seperti departemen di atas.
            if (!empty($insert['jabatan'])) {
                $key = strtolower(trim($insert['jabatan']));
                if (isset($posMap[$key])) {
                    $insert['position_id'] = $posMap[$key];
                } else {
                    $warnings[] = "Baris {$baris} ({$data['nama']}): jabatan \"{$insert['jabatan']}\" tidak cocok dengan master data — position_id dikosongkan, cek manual.";
                }
            }

            $karyawan = Karyawan::create($insert);
            CutiSaldo::getSaldo($karyawan->id);
            $berhasil++;
        }

        fclose($handle);

        AuditLog::catat('import', "Import karyawan: {$berhasil} berhasil, {$dilewati} dilewati, " . count($warnings) . ' warning.');

        $msg = "Import selesai: <strong>{$berhasil}</strong> karyawan berhasil ditambahkan.";
        if ($dilewati > 0) {
            $msg .= " <strong>{$dilewati}</strong> baris dilewati.";
        }
        if ($warnings) {
            $msg .= ' <strong>' . count($warnings) . '</strong> baris berhasil namun departemen/jabatan tidak terhubung ke master data — cek detail di bawah.';
        }

        return redirect()->route('admin.karyawan.import')
                         ->with('success', $msg)
                         ->with('importErrors', $errors)
                         ->with('importWarnings', $warnings);
    }
}
