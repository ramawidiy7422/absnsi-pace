<?php

namespace App\Exports;

use App\Models\FormulirMeninggalkanKerja;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class FormulirMeninggalkanKerjaExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?int $bulan = null,
        private ?int $tahun = null,
        private ?string $status = null
    ) {}

    public function title(): string { return 'Formulir Meninggalkan Kerja'; }

    public function headings(): array
    {
        return ['No','Nomor FMK','Nama Karyawan','NIK','Departemen','Bagian','Jabatan','Tanggal','Jam Keluar','Jam Kembali','Alasan','Keterangan','Kembali Kerja','Status','SPV/Manager','HRD','Disetujui Oleh'];
    }

    public function collection()
    {
        $query = FormulirMeninggalkanKerja::with(['karyawan.department','karyawan.position','approver'])
            ->orderByDesc('tanggal');
        if ($this->bulan) $query->whereMonth('tanggal', $this->bulan);
        if ($this->tahun) $query->whereYear('tanggal', $this->tahun);
        if ($this->status) $query->where('status', $this->status);

        $rows = collect();
        $no = 1;
        foreach ($query->get() as $f) {
            $rows->push([
                $no++,
                $f->nomor_fmk,
                $f->karyawan?->nama,
                $f->karyawan?->nik,
                $f->karyawan?->department?->name ?? '-',
                $f->bagian ?? '-',
                $f->karyawan?->position?->name ?? $f->karyawan?->jabatan ?? '-',
                $f->tanggal->format('d/m/Y'),
                $f->jam_keluar ? substr($f->jam_keluar, 0, 5) : '-',
                $f->jam_kembali ? substr($f->jam_kembali,0,5) : '-',
                $f->label_alasan,
                $f->keterangan ?? '-',
                $f->kembali_bekerja === 'kembali' ? 'Kembali' : 'Tidak Kembali',
                $f->label_status,
                $f->spv_nama ?? '-',
                $f->hrd_nama ?? '-',
                $f->approver?->name ?? '-',
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
            "A2:Q{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
