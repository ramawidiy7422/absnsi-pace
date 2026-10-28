<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class RekapKaryawanExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private int $karyawanId,
        private int $bulan,
        private int $tahun
    ) {}

    public function title(): string
    {
        return 'Rekap Karyawan ' . Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y');
    }

    public function headings(): array
    {
        return ['No','Tanggal','Hari','Shift','Jam Masuk','Jam Keluar','Durasi (menit)','Status','Metode','Keterangan'];
    }

    public function collection()
    {
        $rows = collect();
        $no = 1;
        $absensi = Absensi::with('shift')
            ->where('karyawan_id', $this->karyawanId)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal')
            ->get();

        foreach ($absensi as $a) {
            $rows->push([
                $no++,
                $a->tanggal->format('d/m/Y'),
                $a->tanggal->translatedFormat('l'),
                $a->shift?->nama_shift ?? '-',
                $a->jam_masuk ? substr($a->jam_masuk,0,5) : '-',
                $a->jam_keluar ? substr($a->jam_keluar,0,5) : '-',
                $a->durasi_menit ?? '-',
                ucfirst($a->status),
                ucfirst($a->metode ?? '-'),
                $a->keterangan ?? '-',
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
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF276749']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A2:J{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
