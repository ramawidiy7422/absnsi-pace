<?php

namespace App\Exports;

use App\Models\Lemburan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class LemburanExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?int    $bulan  = null,
        private ?int    $tahun  = null,
        private ?string $status = null
    ) {}

    public function title(): string
    {
        $bln = $this->bulan ? Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y') : 'Semua';
        return 'Rekap Lembur ' . $bln;
    }

    public function headings(): array
    {
        return [
            'No', 'Tanggal', 'Hari', 'NIK', 'Nama Karyawan', 'Departemen', 'Jabatan',
            'Jam Mulai', 'Jam Selesai', 'Total Jam', 'Alasan', 'Keterangan',
            'Status', 'Disetujui Oleh', 'Tgl Disetujui',
        ];
    }

    public function collection()
    {
        $query = Lemburan::with(['karyawan.department', 'karyawan.position', 'approver'])
            ->orderBy('tanggal', 'desc');

        if ($this->bulan) $query->whereMonth('tanggal', $this->bulan);
        if ($this->tahun) $query->whereYear('tanggal', $this->tahun);
        if ($this->status) $query->where('status', $this->status);

        $rows = collect();
        $no   = 1;
        foreach ($query->get() as $l) {
            $rows->push([
                $no++,
                $l->tanggal instanceof \Carbon\Carbon ? $l->tanggal->format('d/m/Y') : $l->tanggal,
                $l->tanggal instanceof \Carbon\Carbon ? $l->tanggal->translatedFormat('l') : '-',
                $l->karyawan?->nik,
                $l->karyawan?->nama,
                $l->karyawan?->department?->name ?? '-',
                $l->karyawan?->position?->name ?? $l->karyawan?->jabatan ?? '-',
                $l->jam_mulai   ? substr($l->jam_mulai, 0, 5)   : '-',
                $l->jam_selesai ? substr($l->jam_selesai, 0, 5) : '-',
                number_format((float)$l->total_jam, 1) . ' jam',
                $l->alasan    ?? '-',
                $l->keterangan ?? '-',
                ucfirst($l->status),
                $l->approver?->name ?? '-',
                $l->approved_at ? Carbon::parse($l->approved_at)->format('d/m/Y H:i') : '-',
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
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFB45309']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A2:O{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
