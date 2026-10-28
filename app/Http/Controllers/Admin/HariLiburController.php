<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $libur = HariLibur::whereYear('tanggal', $tahun)->orderBy('tanggal')->get();
        $tahunList = range(now()->year - 1, now()->year + 2);
        return view('admin.hari-libur.index', compact('libur','tahun','tahunList'));
    }

    public function store(Request $request)
    {
        $isKP = $request->input('jenis') === 'kerja_pengganti';
        $request->validate([
            'tanggal'                => 'required|date|unique:hari_libur,tanggal',
            'nama'                   => 'required|string|max:150',
            'jenis'                  => 'required|in:nasional,cuti_bersama,internal,kerja_pengganti',
            'tanggal_libur_pengganti'=> $isKP ? 'nullable|date' : 'nullable',
        ]);
        $data = $request->only('tanggal','nama','jenis');
        if ($isKP) {
            $data['tanggal_libur_pengganti'] = $request->tanggal_libur_pengganti ?: null;
        }
        $hl = HariLibur::create($data);
        AuditLog::catat('create', "Tambah hari libur: {$hl->nama} ({$hl->tanggal->format('d/m/Y')})", $hl);
        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function update(Request $request, HariLibur $hariLibur)
    {
        $isKP = $request->input('jenis') === 'kerja_pengganti';
        $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal,'.$hariLibur->id,
            'nama'    => 'required|string|max:150',
            'jenis'   => 'required|in:nasional,cuti_bersama,internal,kerja_pengganti',
            'tanggal_libur_pengganti' => $isKP ? 'nullable|date' : 'nullable',
        ]);
        $data = $request->only('tanggal','nama','jenis');
        $data['tanggal_libur_pengganti'] = $isKP ? ($request->tanggal_libur_pengganti ?: null) : null;
        $hariLibur->update($data);
        return back()->with('success', 'Hari libur berhasil diupdate.');
    }

    public function destroy(HariLibur $hariLibur)
    {
        AuditLog::catat('delete', "Hapus hari libur: {$hariLibur->nama} ({$hariLibur->tanggal->format('d/m/Y')})", $hariLibur);
        $hariLibur->delete();
        return back()->with('success', 'Hari libur berhasil dihapus.');
    }

    public function importNasional(Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);

        // Data hari libur nasional + cuti bersama per tahun (sesuai Keppres/SKB)
        $dataPerTahun = [
            2025 => [
                // Hari Libur Nasional
                ['2025-01-01', 'Tahun Baru Masehi 2025',          'nasional'],
                ['2025-01-27', 'Isra Mi\'raj Nabi Muhammad SAW',   'nasional'],
                ['2025-01-28', 'Tahun Baru Imlek 2576',            'nasional'],
                ['2025-03-29', 'Hari Suci Nyepi (Tahun Baru Saka 1947)', 'nasional'],
                ['2025-04-18', 'Wafat Isa Al Masih',               'nasional'],
                ['2025-03-31', 'Idulfitri 1446 H',                 'nasional'],
                ['2025-04-01', 'Idulfitri 1446 H',                 'nasional'],
                ['2025-05-01', 'Hari Buruh Internasional',         'nasional'],
                ['2025-05-12', 'Hari Raya Waisak 2569 BE',         'nasional'],
                ['2025-05-29', 'Kenaikan Isa Al Masih',            'nasional'],
                ['2025-06-01', 'Hari Lahir Pancasila',             'nasional'],
                ['2025-06-06', 'Iduladha 1446 H',                  'nasional'],
                ['2025-06-27', 'Tahun Baru Islam 1447 H',          'nasional'],
                ['2025-08-17', 'Hari Kemerdekaan RI ke-80',        'nasional'],
                ['2025-09-05', 'Maulid Nabi Muhammad SAW',         'nasional'],
                ['2025-12-25', 'Hari Raya Natal',                  'nasional'],
                // Cuti Bersama
                ['2025-04-02', 'Cuti Bersama Idulfitri 1446 H',   'cuti_bersama'],
                ['2025-04-03', 'Cuti Bersama Idulfitri 1446 H',   'cuti_bersama'],
                ['2025-04-04', 'Cuti Bersama Idulfitri 1446 H',   'cuti_bersama'],
                ['2025-04-07', 'Cuti Bersama Idulfitri 1446 H',   'cuti_bersama'],
                ['2025-12-26', 'Cuti Bersama Natal 2025',          'cuti_bersama'],
            ],
            2026 => [
                // Hari Libur Nasional 2026 (sesuai SKB 3 Menteri)
                ['2026-01-01', 'Tahun Baru Masehi 2026',                   'nasional'],
                ['2026-01-17', 'Isra Mi\'raj Nabi Muhammad SAW 1447 H',    'nasional'],
                ['2026-02-17', 'Tahun Baru Imlek 2577',                    'nasional'],
                ['2026-03-19', 'Hari Suci Nyepi (Tahun Baru Saka 1948)',   'nasional'],
                ['2026-03-20', 'Idulfitri 1447 H',                         'nasional'],
                ['2026-03-21', 'Idulfitri 1447 H',                         'nasional'],
                ['2026-04-03', 'Wafat Isa Al Masih',                       'nasional'],
                ['2026-05-01', 'Hari Buruh Internasional',                  'nasional'],
                ['2026-05-14', 'Kenaikan Isa Al Masih',                    'nasional'],
                ['2026-05-16', 'Iduladha 1447 H',                          'nasional'],
                ['2026-06-01', 'Hari Raya Waisak 2570 BE',                 'nasional'],
                ['2026-06-01', 'Hari Lahir Pancasila',                     'nasional'],
                ['2026-07-06', 'Tahun Baru Islam 1448 H',                  'nasional'],
                ['2026-08-17', 'Hari Kemerdekaan RI ke-81',                'nasional'],
                ['2026-09-25', 'Maulid Nabi Muhammad SAW 1448 H',          'nasional'],
                ['2026-12-25', 'Hari Raya Natal',                          'nasional'],
                // Cuti Bersama 2026
                ['2026-03-19', 'Cuti Bersama Idulfitri 1447 H',           'cuti_bersama'],
                ['2026-03-23', 'Cuti Bersama Idulfitri 1447 H',           'cuti_bersama'],
                ['2026-03-24', 'Cuti Bersama Idulfitri 1447 H',           'cuti_bersama'],
                ['2026-12-24', 'Cuti Bersama Natal 2026',                  'cuti_bersama'],
                ['2026-12-26', 'Cuti Bersama Natal 2026',                  'cuti_bersama'],
            ],
            2027 => [
                ['2027-01-01', 'Tahun Baru Masehi 2027',           'nasional'],
                ['2027-01-06', 'Isra Mi\'raj Nabi Muhammad SAW',   'nasional'],
                ['2027-02-06', 'Tahun Baru Imlek 2578',            'nasional'],
                ['2027-03-09', 'Hari Suci Nyepi',                  'nasional'],
                ['2027-03-10', 'Idulfitri 1448 H',                 'nasional'],
                ['2027-03-11', 'Idulfitri 1448 H',                 'nasional'],
                ['2027-03-26', 'Wafat Isa Al Masih',               'nasional'],
                ['2027-05-01', 'Hari Buruh Internasional',         'nasional'],
                ['2027-05-04', 'Kenaikan Isa Al Masih',            'nasional'],
                ['2027-05-21', 'Hari Raya Waisak',                 'nasional'],
                ['2027-05-25', 'Iduladha 1448 H',                  'nasional'],
                ['2027-06-01', 'Hari Lahir Pancasila',             'nasional'],
                ['2027-06-25', 'Tahun Baru Islam 1449 H',          'nasional'],
                ['2027-08-17', 'Hari Kemerdekaan RI ke-82',        'nasional'],
                ['2027-09-14', 'Maulid Nabi Muhammad SAW',         'nasional'],
                ['2027-12-25', 'Hari Raya Natal',                  'nasional'],
            ],
        ];

        $data = $dataPerTahun[$tahun] ?? null;

        if (!$data) {
            // Fallback generik jika tahun belum terdaftar
            return back()->with('error', "Data hari libur untuk tahun {$tahun} belum tersedia. Silakan tambah manual.");
        }

        $count    = 0;
        $skipped  = 0;
        foreach ($data as [$tgl, $nama, $jenis]) {
            $created = HariLibur::firstOrCreate(
                ['tanggal' => $tgl],
                ['nama' => $nama, 'jenis' => $jenis]
            );
            if ($created->wasRecentlyCreated) {
                $count++;
            } else {
                $skipped++;
            }
        }

        AuditLog::catat('create', "Import {$count} hari libur nasional tahun {$tahun}");

        $msg = "Import hari libur nasional + cuti bersama {$tahun} selesai: {$count} ditambahkan.";
        if ($skipped > 0) {
            $msg .= " {$skipped} sudah ada, dilewati.";
        }

        return back()->with('success', $msg);
    }
}
