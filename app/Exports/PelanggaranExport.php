<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class PelanggaranExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?int $departemenId = null,
        private string $jenis = 'semua'
    ) {}

    public function title(): string
    {
        return 'Pelanggaran ' . Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y');
    }

    public function headings(): array
    {
        return ['No','Tanggal','Hari','NIK','Nama Karyawan','Departemen','Jabatan','Jenis Pelanggaran','Jam Masuk','Keterangan'];
    }

    public function collection()
    {
        $statusFilter = match($this->jenis) {
            'alpha'     => ['alpha'],
            'terlambat' => ['terlambat'],
            default     => ['alpha','terlambat'],
        };

        $query = Absensi::with(['karyawan.department','karyawan.position'])
            ->whereIn('status', $statusFilter)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal');

        if ($this->departemenId) {
            $query->whereHas('karyawan', fn($q) => $q->where('department_id', $this->departemenId));
        }

        $rows = collect();
        $no = 1;
        foreach ($query->get() as $a) {
            $rows->push([
                $no++,
                $a->tanggal->format('d/m/Y'),
                $a->tanggal->translatedFormat('l'),
                $a->karyawan?->nik,
                $a->karyawan?->nama,
                $a->karyawan?->department?->name ?? '-',
                $a->karyawan?->position?->name ?? $a->karyawan?->jabatan ?? '-',
                ucfirst($a->status),
                $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-',
                $a->keterangan ?? $a->keterangan_terlambat ?? '-',
            ]);
        }
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestRow();
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC53030']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A2:J{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
