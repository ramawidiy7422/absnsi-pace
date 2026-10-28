<?php

namespace App\Exports;

use App\Models\Izin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class IzinExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?int    $bulan  = null,
        private ?int    $tahun  = null,
        private ?string $status = null,
        private ?string $jenis  = null,
        private ?array  $deptIds = null
    ) {}

    public function title(): string
    {
        return 'Data Izin';
    }

    public function headings(): array
    {
        return [
            'No', 'Tanggal', 'Hari', 'NIK', 'Nama Karyawan', 'Departemen', 'Jabatan',
            'Jenis', 'Keterangan', 'Status', 'Catatan Approver', 'Disetujui Oleh', 'Tgl Disetujui',
        ];
    }

    public function collection()
    {
        $query = Izin::with(['karyawan.department', 'karyawan.position', 'approver'])
            ->orderBy('tanggal', 'desc');

        if ($this->bulan)  $query->whereMonth('tanggal', $this->bulan);
        if ($this->tahun)  $query->whereYear('tanggal', $this->tahun);
        if ($this->status) $query->where('status', $this->status);
        if ($this->jenis)  $query->where('jenis', $this->jenis);
        if ($this->deptIds !== null) $query->whereHas('karyawan', fn($q) => $q->whereIn('department_id', $this->deptIds ?: [0]));

        $labelJenis = ['sakit' => 'Sakit', 'izin' => 'Izin', 'alfa' => 'Alpha'];

        $rows = collect();
        $no   = 1;
        foreach ($query->get() as $i) {
            $tgl = $i->tanggal instanceof \Carbon\Carbon ? $i->tanggal : Carbon::parse($i->tanggal);
            $rows->push([
                $no++,
                $tgl->format('d/m/Y'),
                $tgl->translatedFormat('l'),
                $i->karyawan?->nik,
                $i->karyawan?->nama,
                $i->karyawan?->department?->name ?? '-',
                $i->karyawan?->position?->name ?? $i->karyawan?->jabatan ?? '-',
                $labelJenis[$i->jenis] ?? ucfirst($i->jenis),
                $i->keterangan ?? '-',
                match($i->status) {
                    'disetujui' => 'Disetujui',
                    'ditolak'   => 'Ditolak',
                    default     => 'Pending',
                },
                $i->catatan_approver ?? '-',
                $i->approver?->name ?? '-',
                $i->approved_at ? Carbon::parse($i->approved_at)->format('d/m/Y H:i') : '-',
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
            "A2:M{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
