<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TrendKehadiranExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    private array $rows = [];
    private int   $dataStart;

    public function __construct(private int $bulanRange, private ?int $departementId = null) {}

    private function buildRows(): void
    {
        $trendData = [];
        for ($i = $this->bulanRange - 1; $i >= 0; $i--) {
            $dt    = now()->startOfMonth()->subMonths($i);
            $bulan = (int) $dt->month;
            $tahun = (int) $dt->year;

            $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training']);
            if ($this->departementId) $query->where('department_id', $this->departementId);
            $ids = $query->pluck('id');

            $absensi = Absensi::whereIn('karyawan_id', $ids)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            $hadir     = $absensi->where('status', 'hadir')->count();
            $terlambat = $absensi->where('status', 'terlambat')->count();
            $izin      = $absensi->where('status', 'izin')->count();
            $sakit     = $absensi->where('status', 'sakit')->count();
            $alpha     = $absensi->where('status', 'alpha')->count();
            $total     = $absensi->count();

            $trendData[] = [
                $dt->translatedFormat('F Y'),
                $hadir,
                $terlambat,
                $izin,
                $sakit,
                $alpha,
                $total,
                $total > 0 ? round(($hadir + $terlambat) / $total * 100) . '%' : '0%',
            ];
        }

        $this->rows = array_merge([
            ['LAPORAN TREND KEHADIRAN — PT GLOBAL MULTI PHARMALAB'],
            ['Rentang: ' . $this->bulanRange . ' Bulan Terakhir | Dicetak: ' . now()->format('d/m/Y H:i')],
            [],
            ['Bulan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Total Absensi', '% Kehadiran'],
        ], $trendData);

        $this->dataStart = 5;
    }

    public function array(): array
    {
        $this->buildRows();
        return $this->rows;
    }

    public function title(): string
    {
        return 'Trend Kehadiran';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        return [
            1 => ['font'      => ['bold' => true, 'size' => 13],
                  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                  'font'      => ['color' => ['argb' => 'FF718096']]],
            4 => ['font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2B6CB0']],
                  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
