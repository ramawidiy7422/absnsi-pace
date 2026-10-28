<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FormulirMeninggalkanKerja;
use App\Models\Izin;
use App\Models\IzinPulangCepat;
use App\Models\IzinTerlambat;
use App\Models\Leaverequest;
use App\Models\Lemburan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class ApprovalSayaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $cuti = $user->hasPermission('cuti.approve')
            ? $this->filterUntukUser(
                Leaverequest::with('karyawan.department')->whereIn('status', ['pending_spv', 'pending_manajer']),
                $user, 'approve-leaverequest'
            )
            : collect();

        $fmk = $user->hasPermission('fmk.approve')
            ? $this->filterUntukUser(
                FormulirMeninggalkanKerja::with('karyawan.department')->whereIn('status', ['pending_spv', 'pending_manajer']),
                $user, 'approve-fmk'
            )
            : collect();

        $izinTerlambat = $user->hasPermission('izin-terlambat.approve')
            ? $this->filterUntukUser(
                IzinTerlambat::with('karyawan.department')->where('status', 'pending'),
                $user, 'approve-izin-terlambat'
            )
            : collect();

        $lembur = $user->hasPermission('lembur.approve')
            ? $this->filterUntukUser(
                Lemburan::with('karyawan.department')->where('status', 'pending'),
                $user, 'approve-lemburan'
            )
            : collect();

        $izin = $user->hasPermission('izin.approve')
            ? $this->filterUntukUser(
                Izin::with('karyawan.department')->where('status', 'pending'),
                $user, 'approve-izin'
            )
            : collect();

        $izinPulangCepat = $user->hasPermission('izin-pulang-cepat.approve')
            ? $this->filterUntukUser(
                IzinPulangCepat::with('karyawan.department')->where('status', 'pending'),
                $user, 'approve-izin-pulang-cepat'
            )
            : collect();

        $total = $cuti->count() + $fmk->count() + $izinTerlambat->count() + $izinPulangCepat->count() + $lembur->count() + $izin->count();

        return view('admin.approval-saya.index', compact(
            'cuti', 'fmk', 'izinTerlambat', 'izinPulangCepat', 'lembur', 'izin', 'total'
        ));
    }

    private function filterUntukUser($query, User $user, string $ability)
    {
        // HRD/admin/superadmin bisa approve semua departemen — tidak perlu filter tambahan.
        if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) {
            return $query->get();
        }

        // SPV/Manajer: batasi query ke departemen yang dia bawahi, baru cek Gate per-item
        // (Gate tetap jadi sumber kebenaran otorisasi, filter departemen ini murni optimasi query).
        $deptIds = Department::where('spv_user_id', $user->id)
            ->orWhere('manager_user_id', $user->id)
            ->pluck('id');

        if ($deptIds->isEmpty()) {
            return collect();
        }

        return $query->whereHas('karyawan', fn($q) => $q->whereIn('department_id', $deptIds))
            ->get()
            ->filter(fn($item) => Gate::forUser($user)->allows($ability, $item))
            ->values();
    }

    private function hitungUntukUser($query, User $user, string $ability): int
    {
        if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) {
            return $query->count();
        }

        $deptIds = Department::where('spv_user_id', $user->id)
            ->orWhere('manager_user_id', $user->id)
            ->pluck('id');

        if ($deptIds->isEmpty()) {
            return 0;
        }

        return $query->whereHas('karyawan', fn($q) => $q->whereIn('department_id', $deptIds))
            ->get()
            ->filter(fn($item) => Gate::forUser($user)->allows($ability, $item))
            ->count();
    }

    /**
     * Hitung total item yang menunggu approval user login — dipakai untuk badge sidebar.
     * Di-cache singkat karena dipanggil di setiap halaman admin lewat sidebar.
     */
    public static function hitungPending(User $user): int
    {
        return Cache::remember("approval_pending_{$user->id}", 60, fn() => self::hitungPendingUncached($user));
    }

    /**
     * Hapus cache badge approval untuk semua user yang mungkin terdampak
     * (HRD/admin/superadmin selalu kebawa, ditambah supervisor/manajer
     * departemen karyawan terkait). Panggil ini setiap kali ada pengajuan
     * baru, approve, reject, atau penghapusan data pending — supaya badge
     * sidebar langsung akurat tanpa nunggu TTL cache (60 detik) habis.
     */
    public static function invalidasiCache(?Department $department = null): void
    {
        $userIds = User::whereIn('role', ['hrd', 'admin', 'superadmin'])->pluck('id');

        if ($department) {
            $userIds = $userIds->merge(array_filter([
                $department->spv_user_id,
                $department->manager_user_id,
            ]));
        }

        $userIds->unique()->each(fn($id) => Cache::forget("approval_pending_{$id}"));
    }

    private static function hitungPendingUncached(User $user): int
    {
        $controller = new self();
        $total = 0;

        if ($user->hasPermission('cuti.approve')) {
            $total += $controller->hitungUntukUser(
                Leaverequest::query()->whereIn('status', ['pending_spv', 'pending_manajer']),
                $user, 'approve-leaverequest'
            );
        }
        if ($user->hasPermission('fmk.approve')) {
            $total += $controller->hitungUntukUser(
                FormulirMeninggalkanKerja::query()->whereIn('status', ['pending_spv', 'pending_manajer']),
                $user, 'approve-fmk'
            );
        }
        if ($user->hasPermission('izin-terlambat.approve')) {
            $total += $controller->hitungUntukUser(
                IzinTerlambat::query()->where('status', 'pending'),
                $user, 'approve-izin-terlambat'
            );
        }
        if ($user->hasPermission('izin-pulang-cepat.approve')) {
            $total += $controller->hitungUntukUser(
                IzinPulangCepat::query()->where('status', 'pending'),
                $user, 'approve-izin-pulang-cepat'
            );
        }
        if ($user->hasPermission('lembur.approve')) {
            $total += $controller->hitungUntukUser(
                Lemburan::query()->where('status', 'pending'),
                $user, 'approve-lemburan'
            );
        }
        if ($user->hasPermission('izin.approve')) {
            $total += $controller->hitungUntukUser(
                Izin::query()->where('status', 'pending'),
                $user, 'approve-izin'
            );
        }

        return $total;
    }
}
