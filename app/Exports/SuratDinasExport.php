<?php

namespace App\Exports;

use App\Models\SuratDinas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SuratDinasExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?int $bulan = null,
        private ?int $tahun = null,
        private ?string $status = null
    ) {}

    public function title(): string { return 'Surat Perintah Dinas'; }

    public function headings(): array
    {
        return ['No','Nomor SPD','Tanggal','Departemen','Keperluan','Kota Tujuan','Lokasi','Berangkat','Kembali','Durasi','Kendaraan','No. Kendaraan','Staf (Nama)','Pemberi Tugas','Jabatan','HRD','Status'];
    }

    public function collection()
    {
        $query = SuratDinas::with(['department','staf','pemberiTugas','hrd'])->orderByDesc('tanggal_dibuat');
        if ($this->bulan) $query->whereMonth('tanggal_dibuat', $this->bulan);
        if ($this->tahun) $query->whereYear('tanggal_dibuat', $this->tahun);
        if ($this->status) $query->where('status', $this->status);

        $rows = collect();
        $no = 1;
        foreach ($query->get() as $spd) {
            $rows->push([
                $no++,
                $spd->nomor_spd,
                $spd->tanggal_dibuat?->format('d/m/Y') ?? '-',
                $spd->department?->name ?? '-',
                $spd->keperluan,
                $spd->kota_tujuan,
                $spd->lokasi_tujuan,
                $spd->tanggal_berangkat?->format('d/m/Y') ?? '-',
                $spd->tanggal_kembali?->format('d/m/Y') ?? '-',
                ($spd->tanggal_berangkat && $spd->tanggal_kembali ? $spd->tanggal_berangkat->diffInDays($spd->tanggal_kembali) + 1 : '-') . ($spd->tanggal_berangkat ? ' hari' : ''),
                $spd->label_kendaraan,
                $spd->nomor_kendaraan ?? '-',
                $spd->staf?->pluck('nama')->join(', ') ?? '-',
                $spd->pemberi_tugas_nama ?? '-',
                $spd->pemberi_tugas_jabatan ?? '-',
                $spd->hrd_nama ?? '-',
                $spd->label_status,
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
            "A2:Q{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
