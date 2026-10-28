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

class KeterlambatanExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?int $departemenId = null
    ) {}

    public function title(): string
    {
        return 'Keterlambatan ' . Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y');
    }

    public function headings(): array
    {
        return ['No','Tanggal','Hari','NIK','Nama Karyawan','Departemen','Jabatan','Shift','Jam Masuk Shift','Jam Masuk Aktual','Selisih (menit)','Keterangan'];
    }

    public function collection()
    {
        $query = Absensi::with(['karyawan.department','karyawan.position','shift'])
            ->where('status','terlambat')
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal')
            ->orderBy('jam_masuk');

        if ($this->departemenId) {
            $query->whereHas('karyawan', fn($q) => $q->where('department_id', $this->departemenId));
        }

        $rows = collect();
        $no = 1;
        foreach ($query->get() as $a) {
            $jamShift  = $a->shift?->jam_masuk ? substr($a->shift?->jam_masuk, 0, 5) : '-';
            $jamMasuk  = $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-';
            $selisih   = '-';
            if ($a->jam_masuk && $a->shift?->jam_masuk) {
                $s = Carbon::parse($a->tanggal->format('Y-m-d') . ' ' . $a->shift->jam_masuk);
                $m = Carbon::parse($a->tanggal->format('Y-m-d') . ' ' . $a->jam_masuk);
                $selisih = max(0, $m->diffInMinutes($s, false) * -1);
            }
            $rows->push([
                $no++,
                $a->tanggal->format('d/m/Y'),
                $a->tanggal->translatedFormat('l'),
                $a->karyawan?->nik,
                $a->karyawan?->nama,
                $a->karyawan?->department?->name ?? '-',
                $a->karyawan?->position?->name ?? $a->karyawan?->jabatan ?? '-',
                $a->shift?->nama_shift ?? '-',
                $jamShift,
                $jamMasuk,
                $selisih,
                $a->keterangan_terlambat ?? '-',
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
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC05621']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A2:L{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
