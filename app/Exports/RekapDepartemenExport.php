<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Department;
use App\Models\Karyawan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekapDepartemenExport implements WithMultipleSheets
{
    public function __construct(private int $bulan, private int $tahun, private ?int $departmentId = null) {}

    public function sheets(): array
    {
        return [
            new RekapDepartemenRingkasanSheet($this->bulan, $this->tahun, $this->departmentId),
            new RekapDepartemenDetailSheet($this->bulan, $this->tahun, $this->departmentId),
        ];
    }
}

// ── Sheet 1: Ringkasan per departemen ────────────────────────────────────────
class RekapDepartemenRingkasanSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    private string $namaBulan;
    private array  $data = [];

    public function __construct(private int $bulan, private int $tahun, private ?int $departmentId = null)
    {
        $this->namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $this->buildData();
    }

    private function buildData(): void
    {
        $departemenList = Department::orderBy('name')
            ->when($this->departmentId, fn($q) => $q->where('id', $this->departmentId))
            ->get();
        $semuaKaryawan  = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->get(['id', 'department_id']);

        $allIds     = $semuaKaryawan->pluck('id');
        $absensiAll = Absensi::whereIn('karyawan_id', $allIds)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get()
            ->groupBy('karyawan_id');

        $byDept = $semuaKaryawan->groupBy('department_id');

        $this->data = [];
        foreach ($departemenList as $dept) {
            $ids     = $byDept->get($dept->id, collect())->pluck('id');
            $absensi = $ids->flatMap(fn($id) => $absensiAll->get($id, collect()));
            $hadir     = $absensi->where('status', 'hadir')->count();
            $terlambat = $absensi->where('status', 'terlambat')->count();
            $total     = $absensi->count();

            if ($ids->isEmpty()) continue;

            $this->data[] = [
                $dept->name,
                $ids->count(),
                $hadir,
                $terlambat,
                $absensi->where('status', 'izin')->count(),
                $absensi->where('status', 'sakit')->count(),
                $absensi->where('status', 'alpha')->count(),
                $absensi->where('status', 'cuti')->count(),
                $total,
                $total > 0 ? round(($hadir + $terlambat) / $total * 100) . '%' : '0%',
            ];
        }
    }

    public function array(): array
    {
        $header = [
            ['REKAP ABSENSI PER DEPARTEMEN — PT GLOBAL MULTI PHARMALAB'],
            ['Periode: ' . $this->namaBulan],
            ['Dicetak: ' . now()->format('d/m/Y H:i')],
            [],
            ['Departemen', 'Jml Karyawan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Cuti', 'Total Absensi', '% Kehadiran'],
        ];

        $footer  = [[]];
        $totals  = array_fill(0, 9, 0);
        foreach ($this->data as $row) {
            for ($i = 1; $i <= 8; $i++) {
                $totals[$i] += is_numeric($row[$i]) ? $row[$i] : 0;
            }
        }
        $totalPersen = $totals[8] > 0 ? round(($totals[2] + $totals[3]) / $totals[8] * 100) . '%' : '0%';
        $footer[] = ['TOTAL', $totals[1], $totals[2], $totals[3], $totals[4], $totals[5], $totals[6], $totals[7], $totals[8], $totalPersen];

        return array_merge($header, $this->data, $footer);
    }

    public function title(): string
    {
        return 'Ringkasan Departemen';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->data) + 6;

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14],
                  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['color' => ['argb' => 'FF718096']]],
            5 => ['font'       => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill'       => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF276749']],
                  'alignment'  => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            $lastRow => ['font' => ['bold' => true],
                         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FFF4']]],
        ];
    }
}

// ── Sheet 2: Detail per karyawan ──────────────────────────────────────────────
class RekapDepartemenDetailSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    private string $namaBulan;
    private array  $rows = [];

    public function __construct(private int $bulan, private int $tahun, private ?int $departmentId = null)
    {
        $this->namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $this->buildData();
    }

    private function buildData(): void
    {
        $departemenList = Department::orderBy('name')
            ->when($this->departmentId, fn($q) => $q->where('id', $this->departmentId))
            ->get();
        $karyawanAll    = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with('department')
            ->orderBy('nama')
            ->get();

        $absensiAll = Absensi::whereIn('karyawan_id', $karyawanAll->pluck('id'))
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get()
            ->groupBy('karyawan_id');

        $byDept = $karyawanAll->groupBy('department_id');
        $no     = 1;

        foreach ($departemenList as $dept) {
            $list = $byDept->get($dept->id, collect());
            if ($list->isEmpty()) continue;

            $this->rows[] = [$dept->name];

            foreach ($list as $k) {
                $absensi   = $absensiAll->get($k->id, collect());
                $hadir     = $absensi->where('status', 'hadir')->count();
                $terlambat = $absensi->where('status', 'terlambat')->count();
                $total     = $absensi->count();
                $persen    = $total > 0 ? round(($hadir + $terlambat) / $total * 100) . '%' : '0%';

                $this->rows[] = [
                    $no++,
                    $k->nik,
                    $k->nama,
                    $hadir,
                    $terlambat,
                    $absensi->where('status', 'izin')->count(),
                    $absensi->where('status', 'sakit')->count(),
                    $absensi->where('status', 'alpha')->count(),
                    $absensi->where('status', 'cuti')->count(),
                    $total,
                    $persen,
                ];
            }

            $this->rows[] = [];
        }
    }

    public function array(): array
    {
        $header = [
            ['DETAIL ABSENSI KARYAWAN PER DEPARTEMEN — PT GLOBAL MULTI PHARMALAB'],
            ['Periode: ' . $this->namaBulan],
            [],
            ['No', 'NIK', 'Nama Karyawan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Cuti', 'Total', '% Kehadiran'],
        ];

        return array_merge($header, $this->rows);
    }

    public function title(): string
    {
        return 'Detail Karyawan';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');

        return [
            1 => ['font' => ['bold' => true, 'size' => 13],
                  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            4 => ['font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF276749']],
                  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
