<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RekapBulananExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?int $departemenId = null
    ) {}

    public function title(): string
    {
        return 'Rekap ' . Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y');
    }

    public function headings(): array
    {
        return ['No','NIK','Nama Karyawan','Departemen','Jabatan','Status','Hadir','Terlambat','Izin','Sakit','Alpha','Cuti','Total Hari Kerja','% Kehadiran'];
    }

    public function collection(): Collection
    {
        $statusAktif = ['kontrak','tetap','magang','training','outsourcing','harian'];
        $query = Karyawan::with(['department','position'])
            ->whereIn('status', $statusAktif)
            ->orderBy('nama');

        if ($this->departemenId) {
            $query->where('department_id', $this->departemenId);
        }

        $karyawanList = $query->get();
        $ids = $karyawanList->pluck('id');

        $absensiMap = Absensi::whereIn('karyawan_id', $ids)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get()
            ->groupBy('karyawan_id');

        $rows = collect();
        $no = 1;
        foreach ($karyawanList as $k) {
            $abs = $absensiMap->get($k->id, collect());
            $hadir     = $abs->whereIn('status',['hadir','terlambat'])->count();
            $terlambat = $abs->where('status','terlambat')->count();
            $izin      = $abs->where('status','izin')->count();
            $sakit     = $abs->where('status','sakit')->count();
            $alpha     = $abs->where('status','alpha')->count();
            $cuti      = $abs->where('status','cuti')->count();
            $total     = $hadir + $izin + $sakit + $alpha + $cuti;
            $pct       = $total > 0 ? round($hadir / $total * 100, 1) : 0;

            $rows->push([
                $no++,
                $k->nik,
                $k->nama,
                $k->department?->name ?? '-',
                $k->position?->name ?? $k->jabatan ?? '-',
                ucfirst($k->status),
                $hadir,
                $terlambat,
                $izin,
                $sakit,
                $alpha,
                $cuti,
                $total,
                $pct . '%',
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
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2B6CB0']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A2:N{$last}" => [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }
}
