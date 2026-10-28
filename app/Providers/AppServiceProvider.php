<?php

namespace App\Providers;

use App\Models\FormulirMeninggalkanKerja;
use App\Models\Izin;
use App\Models\IzinPulangCepat;
use App\Models\IzinTerlambat;
use App\Models\KoreksiAbsensi;
use App\Models\Leaverequest;
use App\Models\Lemburan;
use App\Models\PasswordResetRequest;
use App\Models\SuratDinas;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Route::model('suratDinas', SuratDinas::class);

        Gate::define('approve-leaverequest', function (User $user, Leaverequest $leaverequest) {
            if (!$user->hasPermission('cuti.approve')) return false;
            if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) return true;

            $dept = $leaverequest->karyawan?->department;
            return match ($leaverequest->status) {
                'pending_spv'     => $dept && $dept->spv_user_id === $user->id,
                'pending_manajer' => $dept && $dept->manager_user_id === $user->id,
                default           => false,
            };
        });

        Gate::define('approve-fmk', function (User $user, FormulirMeninggalkanKerja $fmk) {
            if (!$user->hasPermission('fmk.approve')) return false;
            if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) return true;

            $dept = $fmk->karyawan?->department;
            return match ($fmk->status) {
                'pending_spv'     => $dept && $dept->spv_user_id === $user->id,
                'pending_manajer' => $dept && $dept->manager_user_id === $user->id,
                default           => false,
            };
        });

        Gate::define('approve-izin-terlambat', function (User $user, IzinTerlambat $izinTerlambat) {
            if (!$user->hasPermission('izin-terlambat.approve')) return false;
            if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) return true;

            $dept = $izinTerlambat->karyawan?->department;
            return $dept && ($dept->spv_user_id === $user->id || $dept->manager_user_id === $user->id);
        });

        Gate::define('approve-izin-pulang-cepat', function (User $user, IzinPulangCepat $izinPulangCepat) {
            if (!$user->hasPermission('izin-pulang-cepat.approve')) return false;
            if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) return true;

            $dept = $izinPulangCepat->karyawan?->department;
            return $dept && ($dept->spv_user_id === $user->id || $dept->manager_user_id === $user->id);
        });

        Gate::define('approve-lemburan', function (User $user, Lemburan $lemburan) {
            if (!$user->hasPermission('lembur.approve')) return false;
            if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) return true;

            $dept = $lemburan->karyawan?->department;
            return $dept && ($dept->spv_user_id === $user->id || $dept->manager_user_id === $user->id);
        });

        Gate::define('approve-izin', function (User $user, Izin $izin) {
            if (!$user->hasPermission('izin.approve')) return false;
            if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) return true;

            $dept = $izin->karyawan?->department;
            return $dept && ($dept->spv_user_id === $user->id || $dept->manager_user_id === $user->id);
        });

        Gate::define('approve-reset-password', function (User $user, PasswordResetRequest $req) {
            if (!$user->hasPermission('reset-password.approve')) return false;
            return in_array($user->role, ['hrd', 'superadmin']);
        });

        Gate::define('approve-koreksi-absensi', function (User $user, KoreksiAbsensi $koreksi) {
            if (!$user->hasPermission('absensi.edit')) return false;
            if (in_array($user->role, ['hrd', 'admin', 'superadmin'])) return true;

            $dept = $koreksi->karyawan?->department;
            return $dept && ($dept->spv_user_id === $user->id || $dept->manager_user_id === $user->id);
        });

        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');

        // Share identitas perusahaan ke semua views — kiosk, login, error pages, PDF
        View::share('namaPerusahaan',   \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab'));
        View::share('alamatPerusahaan', \App\Models\Pengaturan::get('alamat_perusahaan', ''));
        View::share('kotaPerusahaan',   \App\Models\Pengaturan::get('kota_perusahaan', ''));
        View::share('noTelpPerusahaan', \App\Models\Pengaturan::get('no_telp_perusahaan', ''));
        View::share('slipFooter',       \App\Models\Pengaturan::get('slip_footer', ''));
        View::share('logoPerusahaan',   \App\Models\Pengaturan::get('logo_perusahaan', '/images/logo-gmp.png'));
        View::share('websitePerusahaan',\App\Models\Pengaturan::get('website_perusahaan', ''));
    }
}
