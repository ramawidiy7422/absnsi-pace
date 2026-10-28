<?php

namespace App\Exports;

use App\Models\Leaverequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class LeaverequestExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?int    $bulan  = null,
        private ?int    $tahun  = null,
        private ?string $status = null,
        private ?string $type   = null,
        private ?array  $deptIds = null
    ) {}

    public function title(): string
    {
        return 'Pengajuan Cuti';
    }

    public function headings(): array
    {
        return [
            'No', 'NIK', 'Nama Karyawan', 'Departemen', 'Jabatan',
            'Jenis Cuti', 'Tgl Mulai', 'Tgl Selesai', 'Durasi (hari)',
            'Keterangan', 'Status', 'Disetujui Oleh', 'Tgl Disetujui',
        ];
    }

    public function collection()
    {
        $query = Leaverequest::with(['karyawan.department', 'karyawan.position', 'approver'])
            ->orderBy('start_date', 'desc');

        if ($this->bulan)  $query->whereMonth('start_date', $this->bulan);
        if ($this->tahun)  $query->whereYear('start_date', $this->tahun);
        if ($this->status) $query->where('status', $this->status);
        if ($this->type)   $query->where('type', $this->type);
        if ($this->deptIds !== null) $query->whereHas('karyawan', fn($q) => $q->whereIn('department_id', $this->deptIds ?: [0]));

        $rows = collect();
        $no   = 1;
        foreach ($query->get() as $lr) {
            $start = Carbon::parse($lr->start_date);
            $end   = Carbon::parse($lr->end_date);
            $durasi = $start->diffInDays($end) + 1;

            $rows->push([
                $no++,
                $lr->karyawan?->nik,
                $lr->karyawan?->nama,
                $lr->karyawan?->department?->name ?? '-',
                $lr->karyawan?->position?->name ?? $lr->karyawan?->jabatan ?? '-',
                ucfirst($lr->type ?? '-'),
                $start->format('d/m/Y'),
                $end->format('d/m/Y'),
                $durasi . ' hari',
                $lr->keterangan ?? '-',
                match($lr->status) {
                    'disetujui'       => 'Disetujui',
                    'ditolak'         => 'Ditolak',
                    'pending_spv'     => 'Menunggu SPV',
                    'pending_manajer' => 'Menunggu Manajer',
                    'pending_hrd'     => 'Menunggu HRD',
                    default           => 'Pending',
                },
                $lr->approver?->name ?? '-',
                $lr->approved_at ? Carbon::parse($lr->approved_at)->format('d/m/Y H:i') : '-',
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
            "A2:M{$last}" => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
