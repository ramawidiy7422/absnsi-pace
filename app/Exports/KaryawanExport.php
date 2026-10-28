<?php

namespace App\Exports;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KaryawanExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private int|array|null $departmentId = null,
        private ?string $status       = null,
        private ?string $search       = null
    ) {}

    public function title(): string
    {
        return 'Data Karyawan';
    }

    public function headings(): array
    {
        return [
            'No',
            // Identitas
            'NIK Karyawan',
            'Nama Lengkap',
            'Inisial',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Status Pernikahan',
            'Jumlah Tanggungan',
            // Kontak
            'No HP',
            'Email',
            'Alamat',
            // Jabatan
            'Departemen',
            'Divisi',
            'Jabatan',
            'Shift',
            'Status Karyawan',
            'Tanggal Masuk',
            'Tgl Kontrak Berakhir',
            'Vendor Outsourcing',
            'Pendidikan Terakhir',
            // Dokumen Legal
            'NIK KTP',
            'No KK',
            'NPWP',
            'BPJS Ketenagakerjaan',
            'BPJS Kesehatan',
            // Data tambahan
            'Golongan Darah',
            'Kontak Darurat - Nama',
            'Kontak Darurat - Hubungan',
            'Kontak Darurat - No HP',
            'Bank',
            'No. Rekening',
            'Rekening Atas Nama',
        ];
    }

    public function collection()
    {
        $query = Karyawan::with([
                'department',
                'devision',
                'position',
                'shift',
                'legaldoc',
                'educations' => fn($q) => $q->latest('year'),
            ])
            ->when($this->departmentId, fn($q) => is_array($this->departmentId)
                ? $q->whereIn('department_id', $this->departmentId)
                : $q->where('department_id', $this->departmentId))
            ->when($this->status,       fn($q) => $q->where('status', $this->status))
            ->when($this->search,       fn($q) => $q->where(function($s) {
                $s->where('nama', 'like', "%{$this->search}%")
                  ->orWhere('nik', 'like', "%{$this->search}%");
            }))
            ->orderBy('department_id')
            ->orderBy('nama');

        $rows = collect();
        $no   = 1;

        foreach ($query->get() as $k) {
            $legal = $k->legaldoc;
            $edu   = $k->educations->first();

            $rows->push([
                $no++,
                // Identitas
                $k->nik,
                $k->nama,
                $k->inisial ?? '-',
                $k->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                $k->birth_place ?? '-',
                $k->birth_date?->format('d/m/Y') ?? '-',
                ucfirst($k->religion ?? '-'),
                ucfirst(str_replace('_', ' ', $k->marital_status ?? '-')),
                $k->jumlah_tanggungan ?? 0,
                // Kontak
                $k->no_hp ?? '-',
                $k->email ?? '-',
                $k->alamat ?? '-',
                // Jabatan
                $k->department?->name ?? $k->departemen ?? '-',
                $k->devision?->name   ?? '-',
                $k->position?->name   ?? $k->jabatan    ?? '-',
                $k->shift?->nama_shift ?? '-',
                ucfirst($k->status ?? '-'),
                $k->tanggal_masuk?->format('d/m/Y') ?? '-',
                $k->tanggal_kontrak_berakhir?->format('d/m/Y') ?? '-',
                $k->vendor_outsourcing ?? '-',
                $edu ? strtoupper($edu->level ?? '-').' - '.($edu->major ?? '-').' ('.($edu->institution ?? '-').')' : '-',
                // Dokumen Legal
                $legal?->nik             ?? '-',
                $legal?->kk              ?? '-',
                $legal?->npwp            ?? '-',
                $legal?->bpjs_tk         ?? '-',
                $legal?->bpjs_kesehatan  ?? '-',
                // Data tambahan
                $k->golongan_darah              ?? '-',
                $k->kontak_darurat_nama         ?? '-',
                $k->kontak_darurat_hubungan     ?? '-',
                $k->kontak_darurat_no_hp        ?? '-',
                $k->bank_nama                   ?? '-',
                $k->bank_no_rekening            ?? '-',
                $k->bank_atas_nama              ?? '-',
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestRow();

        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E4E8C']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ],
            "A2:AA{$last}" => [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Kolom dokumen legal
            "W1:AA1" => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF276749']],
            ],
            // Kolom data tambahan (golongan darah, kontak darurat, bank)
            "AB1:AH1" => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6B21A8']],
            ],
        ];
    }
}
