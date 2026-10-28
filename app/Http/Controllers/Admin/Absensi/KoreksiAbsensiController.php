<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\KoreksiAbsensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class KoreksiAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = KoreksiAbsensi::with(['karyawan.department', 'approver'])
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $request->department_id)))
            ->when($request->search,        fn($q) => $q->whereHas('karyawan', fn($k) =>
                $k->where('nama', 'like', "%{$request->search}%")->orWhere('nik', 'like', "%{$request->search}%")
            ))
            ->when($request->bulan,         fn($q) => $q->whereMonth('tanggal', $request->bulan))
            ->when($request->tahun,         fn($q) => $q->whereYear('tanggal', $request->tahun));

        $koreksiList = $query->latest()->paginate(20)->withQueryString();
        $departemen  = Department::orderBy('name')->get();
        $totalPending = KoreksiAbsensi::where('status', 'pending')->count();

        return view('admin.koreksi-absensi.index', compact('koreksiList', 'departemen', 'totalPending'));
    }

    public function approve(Request $request, KoreksiAbsensi $koreksi)
    {
        if (Gate::denies('approve-koreksi-absensi', $koreksi)) {
            return back()->with('error', 'Anda bukan approver untuk koreksi absensi ini.');
        }

        $request->validate(['catatan_approver' => 'nullable|string|max:500']);

        $sudahDiproses = DB::transaction(function () use ($koreksi, $request) {
            $locked = KoreksiAbsensi::where('id', $koreksi->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return true;
            }
            $koreksi->update([
                'status'           => 'disetujui',
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
                'catatan_approver' => $request->catatan_approver,
            ]);

            // Terapkan koreksi ke absensi yang ada atau buat baru
            $absensi = $koreksi->absensi
                ?? Absensi::where('karyawan_id', $koreksi->karyawan_id)
                    ->whereDate('tanggal', $koreksi->tanggal)->first();

            if ($absensi) {
                $update = [];
                if (in_array($koreksi->tipe, ['masuk', 'keduanya']) && $koreksi->jam_masuk_diajukan) {
                    $update['jam_masuk'] = $koreksi->jam_masuk_diajukan;
                }
                if (in_array($koreksi->tipe, ['keluar', 'keduanya']) && $koreksi->jam_keluar_diajukan) {
                    $update['jam_keluar'] = $koreksi->jam_keluar_diajukan;
                }
                // Hitung ulang durasi jika ada masuk & keluar
                $jamMasuk  = $update['jam_masuk']  ?? $absensi->jam_masuk;
                $jamKeluar = $update['jam_keluar'] ?? $absensi->jam_keluar;
                if ($jamMasuk && $jamKeluar) {
                    $detik = strtotime($jamKeluar) - strtotime($jamMasuk);
                    if ($detik < 0) $detik += 86400;
                    $update['durasi_menit'] = (int) round($detik / 60);
                }
                $absensi->update($update);
            } else {
                // Buat absensi baru dari koreksi
                $karyawan  = $koreksi->karyawan;
                $jm        = $koreksi->jam_masuk_diajukan;
                $jk        = $koreksi->jam_keluar_diajukan;
                $durasi    = null;
                if ($jm && $jk) {
                    $detik = strtotime($jk) - strtotime($jm);
                    if ($detik < 0) $detik += 86400;
                    $durasi = (int) round($detik / 60);
                }
                Absensi::create([
                    'karyawan_id'  => $koreksi->karyawan_id,
                    'shift_id'     => $karyawan->resolveShift(\Carbon\Carbon::parse($koreksi->tanggal))?->id ?? $karyawan->shift_id,
                    'tanggal'      => $koreksi->tanggal,
                    'jam_masuk'    => $jm,
                    'jam_keluar'   => $jk,
                    'durasi_menit' => $durasi,
                    'status'       => 'hadir',
                    'metode'       => 'manual',
                    'keterangan'   => 'Dibuat dari koreksi absensi yang disetujui',
                ]);
            }

            // Notif ke karyawan
            if ($koreksi->karyawan->user) {
                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'type'            => 'koreksi_absensi_disetujui',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $koreksi->karyawan->user->id,
                    'data'            => json_encode([
                        'title' => '✅ Koreksi Absensi Disetujui',
                        'body'  => "Pengajuan koreksi absensi tanggal {$koreksi->tanggal->format('d/m/Y')} ({$koreksi->label_tipe}) telah disetujui.",
                        'url'   => '/portal/absensi',
                        'icon'  => 'fas fa-check-circle',
                        'color' => '#276749',
                        'bg'    => '#f0fff4',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Koreksi ini sudah diproses.');
        }

        AuditLog::catat('approve', "Koreksi absensi {$koreksi->karyawan->nama} tgl {$koreksi->tanggal->format('d/m/Y')} disetujui", $koreksi);
        return back()->with('success', 'Koreksi absensi disetujui dan absensi telah diperbarui.');
    }

    public function reject(Request $request, KoreksiAbsensi $koreksi)
    {
        if (Gate::denies('approve-koreksi-absensi', $koreksi)) {
            return back()->with('error', 'Anda bukan approver untuk koreksi absensi ini.');
        }

        $request->validate(['catatan_approver' => 'required|string|max:500']);

        $sudahDiproses = DB::transaction(function () use ($koreksi, $request) {
            $locked = KoreksiAbsensi::where('id', $koreksi->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return true;
            }
            $koreksi->update([
                'status'           => 'ditolak',
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
                'catatan_approver' => $request->catatan_approver,
            ]);
            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Koreksi ini sudah diproses.');
        }

        if ($koreksi->karyawan->user) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'koreksi_absensi_ditolak',
                'notifiable_type' => User::class,
                'notifiable_id'   => $koreksi->karyawan->user->id,
                'data'            => json_encode([
                    'title' => '❌ Koreksi Absensi Ditolak',
                    'body'  => "Pengajuan koreksi absensi tanggal {$koreksi->tanggal->format('d/m/Y')} ditolak. Alasan: {$request->catatan_approver}",
                    'url'   => '/portal/absensi',
                    'icon'  => 'fas fa-times-circle',
                    'color' => '#c53030',
                    'bg'    => '#fff5f5',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        AuditLog::catat('reject', "Koreksi absensi {$koreksi->karyawan->nama} tgl {$koreksi->tanggal->format('d/m/Y')} ditolak", $koreksi);
        return back()->with('success', 'Koreksi absensi ditolak.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $list    = KoreksiAbsensi::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->where('status', 'pending')->get();
        $count   = 0;
        $skipped = 0;
        foreach ($list as $koreksi) {
            if (Gate::denies('approve-koreksi-absensi', $koreksi)) {
                $skipped++;
                continue;
            }
            $this->approve(new Request(['catatan_approver' => 'Bulk approve']), $koreksi);
            $count++;
        }

        $pesan = "{$count} koreksi absensi disetujui.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda.";
        return back()->with('success', $pesan);
    }
}
