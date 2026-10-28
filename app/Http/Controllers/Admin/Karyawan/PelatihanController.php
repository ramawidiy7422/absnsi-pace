<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\Pelatihan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PelatihanController extends Controller
{
    public function index(Request $request)
    {
        $jenis         = $request->jenis;
        $tipe          = $request->tipe;
        $status        = $request->status;
        $department_id = $request->department_id;
        $expiring      = $request->expiring;

        $query = Pelatihan::with('karyawan.department')
            ->when($jenis,         fn($q) => $q->where('jenis', $jenis))
            ->when($tipe,          fn($q) => $q->where('tipe', $tipe))
            ->when($status,        fn($q) => $q->where('status', $status))
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->when($expiring === '30', fn($q) => $q->whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '>=', now()->toDateString())
                ->where('tanggal_expired', '<=', now()->addDays(30)->toDateString()))
            ->when($expiring === 'expired', fn($q) => $q->whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '<', now()->toDateString()))
            ->orderByDesc('tanggal_mulai');

        $pelatihan = $query->paginate(25)->withQueryString();
        $deptList  = Department::orderBy('name')->get();

        $stats = [
            'total'        => Pelatihan::count(),
            'lulus'        => Pelatihan::where('status', 'lulus')->count(),
            'internal'     => Pelatihan::where('tipe', 'internal')->count(),
            'eksternal'    => Pelatihan::where('tipe', 'eksternal')->count(),
            'expired'      => Pelatihan::whereNotNull('tanggal_expired')->where('tanggal_expired', '<', now())->count(),
            'near_expired' => Pelatihan::whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '>=', now())->where('tanggal_expired', '<=', now()->addDays(30))->count(),
        ];

        return view('admin.pelatihan.index', compact('pelatihan', 'deptList', 'jenis', 'tipe', 'status', 'department_id', 'expiring', 'stats'));
    }

    public function exportCsv(Request $request)
    {
        $jenis         = $request->jenis;
        $tipe          = $request->tipe;
        $status        = $request->status;
        $department_id = $request->department_id;
        $expiring      = $request->expiring;

        $data = Pelatihan::with('karyawan.department')
            ->when($jenis,         fn($q) => $q->where('jenis', $jenis))
            ->when($tipe,          fn($q) => $q->where('tipe', $tipe))
            ->when($status,        fn($q) => $q->where('status', $status))
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->when($expiring === '30', fn($q) => $q->whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '>=', now()->toDateString())
                ->where('tanggal_expired', '<=', now()->addDays(30)->toDateString()))
            ->when($expiring === 'expired', fn($q) => $q->whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '<', now()->toDateString()))
            ->orderByDesc('tanggal_mulai')
            ->get();

        $callback = function() use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['NIK','Nama Karyawan','Departemen','Nama Pelatihan','Tipe','Jenis','Penyelenggara','Tgl Mulai','Tgl Selesai','Status','No. Sertifikat','Tgl Expired']);
            foreach ($data as $p) {
                fputcsv($out, [
                    $p->karyawan?->nik ?? '-',
                    $p->karyawan?->nama ?? '-',
                    $p->karyawan?->department?->name ?? $p->karyawan?->departemen ?? '-',
                    $p->nama_pelatihan,
                    $p->tipe_label,
                    $p->jenis_label,
                    $p->penyelenggara ?? '-',
                    $p->tanggal_mulai?->format('d/m/Y') ?? '-',
                    $p->tanggal_selesai?->format('d/m/Y') ?? '-',
                    $p->status,
                    $p->nomor_sertifikat ?? '-',
                    $p->tanggal_expired?->format('d/m/Y') ?? '-',
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=pelatihan_' . now()->format('Y-m-d') . '.csv',
        ]);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $jenis         = $request->jenis;
        $tipe          = $request->tipe;
        $status        = $request->status;
        $department_id = $request->department_id;
        $expiring      = $request->expiring;

        $pelatihan = Pelatihan::with('karyawan.department')
            ->when($jenis,         fn($q) => $q->where('jenis', $jenis))
            ->when($tipe,          fn($q) => $q->where('tipe', $tipe))
            ->when($status,        fn($q) => $q->where('status', $status))
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->when($expiring === '30', fn($q) => $q->whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '>=', now()->toDateString())
                ->where('tanggal_expired', '<=', now()->addDays(30)->toDateString()))
            ->when($expiring === 'expired', fn($q) => $q->whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '<', now()->toDateString()))
            ->orderByDesc('tanggal_mulai')
            ->get();

        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $labels = [];
        if ($jenis)         $labels[] = 'Jenis: ' . ucfirst($jenis);
        if ($status)        $labels[] = 'Status: ' . ucfirst(str_replace('_', ' ', $status));
        if ($department_id) { $dept = Department::find($department_id); if ($dept) $labels[] = 'Dept: ' . $dept->name; }
        if ($expiring === '30')      $labels[] = 'Akan expired 30 hari';
        elseif ($expiring === 'expired') $labels[] = 'Sudah expired';
        $filterLabel = $labels ? implode(' | ', $labels) : 'Semua data';

        $pdf = Pdf::loadView('admin.pelatihan.export-pdf', compact(
            'pelatihan', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan', 'filterLabel'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('pelatihan-' . now()->format('Ymd_His') . '.pdf');
    }

    private function rules(): array
    {
        return [
            'nama_pelatihan'   => 'required|string|max:150',
            'jenis'            => 'required|in:cpob,k3,hygiene,sop,internal,external,lainnya',
            'tipe'             => 'required|in:internal,eksternal',
            'penyelenggara'    => 'nullable|string|max:150',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_mulai',
            'tanggal_expired'  => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'           => 'required|in:lulus,tidak_lulus,sedang_berjalan,belum_ikut',
            'nomor_sertifikat' => 'nullable|string|max:100',
            'lampiran'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'catatan'          => 'nullable|string|max:500',
        ];
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $data = $request->validate($this->rules());
        $data['karyawan_id'] = $karyawan->id;

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('pelatihan', 'public');
        }

        $pelatihan = Pelatihan::create($data);
        AuditLog::catat('create', "Tambah pelatihan '{$pelatihan->nama_pelatihan}' untuk karyawan {$karyawan->nama}", $pelatihan);

        return redirect()->route('admin.karyawan.show', $karyawan)
            ->with('success', "Pelatihan '{$pelatihan->nama_pelatihan}' berhasil ditambahkan.")
            ->withFragment('tab-pelatihan');
    }

    public function update(Request $request, Pelatihan $pelatihan)
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('lampiran')) {
            if ($pelatihan->lampiran) Storage::disk('public')->delete($pelatihan->lampiran);
            $data['lampiran'] = $request->file('lampiran')->store('pelatihan', 'public');
        } else {
            unset($data['lampiran']); // jangan overwrite kalau tidak upload baru
        }

        $pelatihan->update($data);
        AuditLog::catat('update', "Update pelatihan '{$pelatihan->nama_pelatihan}' karyawan ID {$pelatihan->karyawan_id}", $pelatihan);

        return redirect()->route('admin.karyawan.show', $pelatihan->karyawan_id)
            ->with('success', "Pelatihan '{$pelatihan->nama_pelatihan}' berhasil diupdate.")
            ->withFragment('tab-pelatihan');
    }

    public function destroy(Pelatihan $pelatihan)
    {
        $karyawanId = $pelatihan->karyawan_id;
        $nama       = $pelatihan->nama_pelatihan;

        if ($pelatihan->lampiran) Storage::disk('public')->delete($pelatihan->lampiran);
        AuditLog::catat('delete', "Hapus pelatihan '{$nama}' karyawan ID {$karyawanId}", $pelatihan);
        $pelatihan->delete();

        return redirect()->route('admin.karyawan.show', $karyawanId)
            ->with('success', "Pelatihan '{$nama}' berhasil dihapus.")
            ->withFragment('tab-pelatihan');
    }
}
