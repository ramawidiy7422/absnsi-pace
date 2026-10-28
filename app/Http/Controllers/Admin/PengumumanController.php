<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Pengumuman;
use App\Models\User;
use App\Notifications\PengumumanNotification;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengumuman::with('penulis')
            ->when($request->jenis, fn($q) => $q->where('jenis', $request->jenis))
            ->when($request->prioritas, fn($q) => $q->where('prioritas', $request->prioritas))
            ->when($request->aktif !== null && $request->aktif !== '', fn($q) => $q->where('aktif', (bool)$request->aktif))
            ->when($request->search, fn($q) => $q->where('judul', 'like', "%{$request->search}%"));

        $list           = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $departemenList = Department::orderBy('name')->pluck('name');

        return view('admin.pengumuman.index', compact('list','departemenList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'             => 'required|string|max:200',
            'isi'               => 'required|string',
            'jenis'             => 'required|in:pengumuman,memo',
            'prioritas'         => 'required|in:normal,penting,mendesak',
            'target_departemen' => 'nullable|string|max:100',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'nullable|date|after_or_equal:tanggal_mulai',
            'tampil_popup'      => 'boolean',
            'lampiran'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data['user_id']      = auth()->id();
        $data['aktif']        = true;
        $data['tampil_popup'] = $request->boolean('tampil_popup');

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $p = Pengumuman::create($data);
        AuditLog::catat('create', "Buat pengumuman: {$p->judul}", $p);

        // Kirim notifikasi ke semua user yang punya karyawan sesuai target departemen
        $userQuery = User::whereNotNull('karyawan_id')->with('karyawan');
        if (!empty($data['target_departemen'])) {
            $userQuery->whereHas('karyawan', fn($q) => $q->where('departemen', $data['target_departemen']));
        }
        $userQuery->get()->each(fn($u) => $u->notify(new PengumumanNotification($p)));

        return back()->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $request->validate([
            'judul'             => 'required|string|max:200',
            'isi'               => 'required|string',
            'jenis'             => 'required|in:pengumuman,memo',
            'prioritas'         => 'required|in:normal,penting,mendesak',
            'target_departemen' => 'nullable|string|max:100',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'nullable|date|after_or_equal:tanggal_mulai',
            'aktif'             => 'boolean',
            'tampil_popup'      => 'boolean',
            'lampiran'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data['tampil_popup'] = $request->boolean('tampil_popup');

        if ($request->hasFile('lampiran')) {
            if ($pengumuman->lampiran) {
                \Storage::disk('public')->delete($pengumuman->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $data['aktif'] = $request->boolean('aktif');
        $pengumuman->update($data);
        AuditLog::catat('update', "Edit pengumuman: {$pengumuman->judul}", $pengumuman);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function toggleAktif(Pengumuman $pengumuman)
    {
        $pengumuman->update(['aktif' => !$pengumuman->aktif]);
        $status = $pengumuman->aktif ? 'diaktifkan' : 'dinonaktifkan';
        AuditLog::catat('update', "Pengumuman '{$pengumuman->judul}' {$status}", $pengumuman);
        return back()->with('success', "Pengumuman berhasil {$status}.");
    }

    public function destroy(Pengumuman $pengumuman)
    {
        if ($pengumuman->lampiran) {
            \Storage::disk('public')->delete($pengumuman->lampiran);
        }
        AuditLog::catat('delete', "Hapus pengumuman: {$pengumuman->judul}", $pengumuman);
        $pengumuman->delete();
        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
