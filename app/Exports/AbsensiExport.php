<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?int $karyawanId = null
    ) {}

    public function collection()
    {
        return Absensi::with(['karyawan', 'shift'])
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->when($this->karyawanId, fn($q) => $q->where('karyawan_id', $this->karyawanId))
            ->orderBy('tanggal')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No', 'Tanggal', 'NIK', 'Nama Karyawan', 'Departemen',
            'Jabatan', 'Shift', 'Jam Masuk', 'Jam Keluar', 'Status',
            'Confidence Masuk (%)', 'Keterangan',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->tanggal,
            $row->karyawan->nik ?? '-',
            $row->karyawan->nama ?? '-',
            $row->karyawan->departemen ?? '-',
            $row->karyawan->jabatan ?? '-',
            $row->shift->nama_shift ?? '-',
            $row->jam_masuk ?? '-',
            $row->jam_keluar ?? '-',
            strtoupper($row->status),
            $row->confidence_masuk ?? '-',
            $row->keterangan ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Laporan Absensi';
    }
}
