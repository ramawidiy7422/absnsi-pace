<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class AbsensiExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    private int $no = 0;
    private int $totalRows = 0;

    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?int $karyawanId = null,
        private ?array $deptIds = null
    ) {}

    public function collection()
    {
        $data = Absensi::with(['karyawan.department', 'shift'])
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->when($this->deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $this->deptIds ?: [0])))
            ->when($this->karyawanId, fn($q) => $q->where('karyawan_id', $this->karyawanId))
            ->orderBy('tanggal')
            ->get();

        $this->totalRows = $data->count();
        return $data;
    }

    public function headings(): array
    {
        return [
            'No', 'Tanggal', 'NIK', 'Nama Karyawan', 'Departemen',
            'Jabatan', 'Shift', 'Jam Masuk', 'Jam Keluar', 'Durasi (menit)',
            'Status', 'Metode', 'Confidence Masuk (%)', 'Keterangan',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            Carbon::parse($row->tanggal)->format('d/m/Y'),
            $row->karyawan?->nik ?? '-',
            $row->karyawan?->nama ?? '-',
            $row->karyawan?->department?->name ?? $row->karyawan?->departemen ?? '-',
            $row->karyawan?->jabatan ?? '-',
            $row->shift?->nama_shift ?? '-',
            $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-',
            $row->jam_keluar ? substr($row->jam_keluar, 0, 5) : '-',
            $row->durasi_menit ?? '-',
            strtoupper($row->status),
            $row->metode ?? 'manual',
            $row->confidence_masuk ?? '-',
            $row->keterangan ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Laporan Absensi';
    }

    public function styles(Worksheet $sheet)
    {
        // Header row: dark blue background, white bold text, center aligned
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A365D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF2C5282']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $this->totalRows + 1;

                // Freeze header row
                $sheet->freezePane('A2');

                // Zebra striping + status color per row
                $statusColors = [
                    'HADIR'     => 'FFD1FAE5',
                    'TERLAMBAT' => 'FFFEF3C7',
                    'ALPHA'     => 'FFFEE2E2',
                    'IZIN'      => 'FFDBEAFE',
                    'SAKIT'     => 'FFFCE7F3',
                    'CUTI'      => 'FFEDE9FE',
                ];

                for ($r = 2; $r <= $lastRow; $r++) {
                    $status = strtoupper($sheet->getCell("K{$r}")->getValue());
                    $bg     = $statusColors[$status] ?? (($r % 2 === 0) ? 'FFF7FAFC' : 'FFFFFFFF');

                    $sheet->getStyle("A{$r}:N{$r}")->applyFromArray([
                        'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFE2E8F0']]],
                    ]);

                    // Status cell: bold
                    $sheet->getStyle("K{$r}")->getFont()->setBold(true);
                }

                // Bold border around entire data range
                if ($this->totalRows > 0) {
                    $sheet->getStyle("A1:N{$lastRow}")->applyFromArray([
                        'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2C5282']]],
                    ]);
                }

                // Center-align No, Jam Masuk, Jam Keluar, Status columns
                foreach (['A', 'H', 'I', 'J', 'K', 'L', 'M'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Add title rows above headers
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:N1');
                $sheet->setCellValue('A1', 'LAPORAN ABSENSI — ' . strtoupper(\Carbon\Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y')));
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1A365D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:N2');
                $sheet->setCellValue('A2', 'PT Global Multi Pharmalab — Dicetak: ' . now()->translatedFormat('d F Y H:i'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF718096']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Re-freeze below title rows (now header is row 3)
                $sheet->freezePane('A4');
            },
        ];
    }
}
