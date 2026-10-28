<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SuratDinasExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\Pengaturan;
use App\Models\SuratDinas;
use App\Notifications\SuratDinasStatusNotification;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SuratDinasController extends Controller
{
    private $statusAktif = ['kontrak','tetap','magang','training','outsourcing','harian'];

    // Approve/tolak SPD (baik lewat endpoint khusus maupun form edit biasa) hanya
    // untuk role yang berwenang menyetujui dinas — bukan sekadar siapa saja yang
    // bisa reach halaman admin (operator/supervisor tidak boleh self-approve SPD).
    private function assertCanApproveSpd(): void
    {
        if (!in_array(auth()->user()->role, ['superadmin', 'admin', 'hrd'])) {
            abort(403, 'Anda tidak berwenang menyetujui/menolak Surat Perintah Dinas.');
        }
    }

    public function index(Request $request)
    {
        $query = SuratDinas::with(['department','staf','pemberiTugas'])
            ->orderByDesc('tanggal_dibuat');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($w) => $w->where('nomor_spd','like',"%$q%")
                ->orWhere('keperluan','like',"%$q%")
                ->orWhere('kota_tujuan','like',"%$q%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_dibuat', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_dibuat', $request->tahun);
        }

        $dinas     = $query->paginate(15)->withQueryString();
        $tahunList = range(now()->year, 2024);

        return view('admin.surat-dinas.index', compact('dinas','tahunList'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $karyawan    = Karyawan::whereIn('status', $this->statusAktif)->orderBy('nama')->get();
        $hrdList     = Karyawan::whereIn('status', $this->statusAktif)
            ->whereHas('position', fn($q) => $q->where('name','like','%HRD%')
                ->orWhere('name','like','%HR%'))
            ->orWhereHas('department', fn($q) => $q->where('name','like','%HRD%')
                ->orWhere('name','like','%HR%'))
            ->whereIn('status', $this->statusAktif)
            ->orderBy('nama')->get();

        $nomorSPD = SuratDinas::generateNomor();

        return view('admin.surat-dinas.create', compact('departments','karyawan','hrdList','nomorSPD'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_dibuat'      => 'required|date',
            'department_id'       => 'nullable|exists:departments,id',
            'keperluan'           => 'required|string|max:255',
            'kota_tujuan'         => 'required|string|max:100',
            'lokasi_tujuan'       => 'required|string|max:255',
            'tanggal_berangkat'   => 'required|date',
            'tanggal_kembali'     => 'required|date|after_or_equal:tanggal_berangkat',
            'jam_berangkat'       => 'nullable|date_format:H:i',
            'jenis_kendaraan'     => 'required|in:motor,mobil_dinas,mobil_pribadi,angkutan_umum,pesawat,kereta,lainnya',
            'nomor_kendaraan'     => 'nullable|string|max:20',
            'staf_ids'            => 'required|array|min:1',
            'staf_ids.*'          => 'exists:karyawan,id',
            'pemberi_tugas_id'    => 'nullable|exists:karyawan,id',
            'pemberi_tugas_nama'  => 'nullable|string|max:150',
            'pemberi_tugas_jabatan' => 'nullable|string|max:100',
            'hrd_id'              => 'nullable|exists:karyawan,id',
            'hrd_nama'            => 'nullable|string|max:150',
            'catatan'             => 'nullable|string|max:500',
        ]);

        $data = $request->only([
            'tanggal_dibuat','department_id','keperluan','kota_tujuan','lokasi_tujuan',
            'tanggal_berangkat','tanggal_kembali','jam_berangkat','jenis_kendaraan','nomor_kendaraan',
            'pemberi_tugas_id','pemberi_tugas_nama','pemberi_tugas_jabatan','hrd_id','hrd_nama','catatan',
        ]);
        $data['status'] = 'draft';

        // Jika pilih karyawan dari dropdown, ambil nama otomatis
        if ($request->pemberi_tugas_id) {
            $spv = Karyawan::find($request->pemberi_tugas_id);
            $data['pemberi_tugas_nama']    = $spv->nama;
            $data['pemberi_tugas_jabatan'] = $spv->position?->name ?? $spv->jabatan ?? '';
        }
        if ($request->hrd_id) {
            $hrd = Karyawan::find($request->hrd_id);
            $data['hrd_nama'] = $hrd->nama;
        }

        $spd = SuratDinas::createWithNomor($data);
        $spd->staf()->sync($request->staf_ids);

        AuditLog::catat('create', "Buat SPD {$spd->nomor_spd}", $spd);

        return redirect()->route('admin.surat-dinas.show', $spd)
            ->with('success', "Surat Dinas {$spd->nomor_spd} berhasil dibuat.");
    }

    public function show(SuratDinas $suratDinas)
    {
        $suratDinas->load(['department','staf.department','staf.position','pemberiTugas','hrd']);
        $pengaturan = Pengaturan::all()->pluck('value','key');

        return view('admin.surat-dinas.show', compact('suratDinas','pengaturan'));
    }

    public function edit(SuratDinas $suratDinas)
    {
        $departments = Department::orderBy('name')->get();
        $karyawan    = Karyawan::whereIn('status', $this->statusAktif)->orderBy('nama')->get();
        $stafTerpilih = $suratDinas->staf->pluck('id')->toArray();

        return view('admin.surat-dinas.edit', compact('suratDinas','departments','karyawan','stafTerpilih'));
    }

    public function update(Request $request, SuratDinas $suratDinas)
    {
        $request->validate([
            'tanggal_dibuat'      => 'required|date',
            'department_id'       => 'nullable|exists:departments,id',
            'keperluan'           => 'required|string|max:255',
            'kota_tujuan'         => 'required|string|max:100',
            'lokasi_tujuan'       => 'required|string|max:255',
            'tanggal_berangkat'   => 'required|date',
            'tanggal_kembali'     => 'required|date|after_or_equal:tanggal_berangkat',
            'jam_berangkat'       => 'nullable|date_format:H:i',
            'jenis_kendaraan'     => 'required|in:motor,mobil_dinas,mobil_pribadi,angkutan_umum,pesawat,kereta,lainnya',
            'nomor_kendaraan'     => 'nullable|string|max:20',
            'staf_ids'            => 'required|array|min:1',
            'staf_ids.*'          => 'exists:karyawan,id',
            'pemberi_tugas_id'    => 'nullable|exists:karyawan,id',
            'pemberi_tugas_nama'  => 'nullable|string|max:150',
            'pemberi_tugas_jabatan' => 'nullable|string|max:100',
            'hrd_id'              => 'nullable|exists:karyawan,id',
            'hrd_nama'            => 'nullable|string|max:150',
            'status'              => 'required|in:draft,disetujui,ditolak',
            'catatan'             => 'nullable|string|max:500',
        ]);

        if ($request->status !== $suratDinas->status) {
            $this->assertCanApproveSpd();
        }

        $data = $request->only([
            'tanggal_dibuat','department_id','keperluan','kota_tujuan','lokasi_tujuan',
            'tanggal_berangkat','tanggal_kembali','jam_berangkat','jenis_kendaraan','nomor_kendaraan',
            'pemberi_tugas_id','pemberi_tugas_nama','pemberi_tugas_jabatan','hrd_id','hrd_nama',
            'status','catatan',
        ]);

        if ($request->pemberi_tugas_id) {
            $spv = Karyawan::find($request->pemberi_tugas_id);
            $data['pemberi_tugas_nama']    = $spv->nama;
            $data['pemberi_tugas_jabatan'] = $spv->position?->name ?? $spv->jabatan ?? '';
        }
        if ($request->hrd_id) {
            $hrd = Karyawan::find($request->hrd_id);
            $data['hrd_nama'] = $hrd->nama;
        }

        $suratDinas->update($data);
        $suratDinas->staf()->sync($request->staf_ids);

        AuditLog::catat('update', "Update SPD {$suratDinas->nomor_spd}", $suratDinas);

        return redirect()->route('admin.surat-dinas.show', $suratDinas)
            ->with('success', 'Surat Dinas berhasil diperbarui.');
    }

    public function destroy(SuratDinas $suratDinas)
    {
        $nomor = $suratDinas->nomor_spd;
        $suratDinas->staf()->detach();
        $suratDinas->delete();

        AuditLog::catat('delete', "Hapus SPD {$nomor}");

        return redirect()->route('admin.surat-dinas.index')
            ->with('success', "Surat Dinas {$nomor} berhasil dihapus.");
    }

    public function pdf(SuratDinas $suratDinas)
    {
        $suratDinas->load(['department','staf.department','staf.position','pemberiTugas','hrd']);
        $pengaturan = Pengaturan::all()->pluck('value','key');

        return view('admin.surat-dinas.pdf', compact('suratDinas','pengaturan'));
    }

    public function approve(SuratDinas $suratDinas)
    {
        $this->assertCanApproveSpd();

        if ($suratDinas->status !== 'draft') {
            return back()->with('error', 'SPD ini sudah diproses.');
        }

        $suratDinas->update(['status' => 'disetujui']);
        AuditLog::catat('update', "Setujui SPD {$suratDinas->nomor_spd}");

        // Notifikasi ke staf yang berdinas
        $suratDinas->load('staf.user');
        foreach ($suratDinas->staf as $k) {
            if ($k->user) {
                $k->user->notify(new SuratDinasStatusNotification($suratDinas));
            }
        }

        return back()->with('success', 'SPD disetujui.');
    }

    public function tolak(SuratDinas $suratDinas)
    {
        $this->assertCanApproveSpd();

        if ($suratDinas->status !== 'draft') {
            return back()->with('error', 'SPD ini sudah diproses.');
        }

        $suratDinas->update(['status' => 'ditolak']);
        AuditLog::catat('update', "Tolak SPD {$suratDinas->nomor_spd}");

        // Notifikasi ke staf yang berdinas
        $suratDinas->load('staf.user');
        foreach ($suratDinas->staf as $k) {
            if ($k->user) {
                $k->user->notify(new SuratDinasStatusNotification($suratDinas));
            }
        }

        return back()->with('success', 'SPD ditolak.');
    }

    public function exportExcel(Request $request)
    {
        // Wajibkan bulan/tahun (default bulan berjalan) — tanpa ini export bisa
        // menarik SELURUH riwayat surat dinas sekaligus, berisiko timeout/memory.
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        return Excel::download(
            new SuratDinasExport($bulan, $tahun, $request->status),
            'surat-dinas-' . now()->format('Y-m') . '.xlsx'
        );
    }

    public function exportPdfBulk(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        $query = SuratDinas::with(['department','staf.department','staf.position','pemberiTugas','hrd'])
            ->orderByDesc('tanggal_dibuat')
            ->whereMonth('tanggal_dibuat', $bulan)
            ->whereYear('tanggal_dibuat', $tahun);
        if ($request->status) $query->where('status', $request->status);
        $dinas = $query->get();
        $pengaturan = Pengaturan::all()->pluck('value','key');
        return view('admin.surat-dinas.pdf-bulk', compact('dinas','pengaturan'));
    }
}
