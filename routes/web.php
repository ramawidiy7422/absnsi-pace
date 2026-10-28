<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiKioskController;
use App\Http\Controllers\PortalKaryawanController;

// Halaman utama: sudah login → sesuai role, belum → login
Route::get('/', function () {
    if (!auth()->check()) return redirect()->route('login');
    $role = auth()->user()->role;
    return in_array($role, ['superadmin', 'admin', 'hrd', 'supervisor', 'operator'])
        ? redirect()->route('admin.dashboard')
        : redirect()->route('portal.index');
});

// Kiosk Absensi (publik)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [AbsensiKioskController::class, 'index'])->name('index');
    Route::post('/scan',    [AbsensiKioskController::class, 'scan'])->middleware('throttle:30,1')->name('scan');
    Route::post('/scan-qr', [AbsensiKioskController::class, 'scanQr'])->middleware('throttle:30,1')->name('scan-qr');
    Route::post('/fmk',     [AbsensiKioskController::class, 'absenFmk'])->middleware('throttle:30,1')->name('fmk');
});

// Auth (login, logout)
require __DIR__ . '/auth.php';

// Admin (semua route /admin/*) — hanya superadmin/admin/hrd/supervisor/operator
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.only'])->group(function () {
    require __DIR__ . '/admin.php';
});

// Portal Karyawan — hanya role karyawan/harian
Route::prefix('portal')->name('portal.')->middleware(['auth', 'karyawan.only'])->group(function () {
    Route::get('/',              [PortalKaryawanController::class, 'index'])->name('index');
    Route::get('/absensi',       [PortalKaryawanController::class, 'absensi'])->name('absensi');
    Route::get('/cuti',          [PortalKaryawanController::class, 'cuti'])->name('cuti');
    Route::post('/cuti',         [PortalKaryawanController::class, 'storeCuti'])->name('cuti.store');
    Route::get('/lembur',                  [PortalKaryawanController::class, 'lembur'])->name('lembur');
    Route::post('/lembur',                 [PortalKaryawanController::class, 'storeLembur'])->name('lembur.store');
    Route::delete('/lembur/{lemburan}',    [PortalKaryawanController::class, 'cancelLembur'])->name('lembur.cancel');
    Route::post('/absensi/alasan-terlambat', [PortalKaryawanController::class, 'storeAlasanTerlambat'])->name('absensi.alasan-terlambat');
    Route::get('/slip-pdf',      [PortalKaryawanController::class, 'slipPdf'])->name('slip-pdf');
    Route::get('/pelatihan',     [PortalKaryawanController::class, 'pelatihan'])->name('pelatihan');
    Route::get('/riwayat-kerja', [PortalKaryawanController::class, 'riwayatKerja'])->name('riwayat-kerja');
    Route::get('/jadwal-shift', [PortalKaryawanController::class, 'jadwalShift'])->name('jadwal-shift');
    Route::get('/ganti-password',  [PortalKaryawanController::class, 'gantiPassword'])->name('ganti-password');
    Route::post('/ganti-password', [PortalKaryawanController::class, 'updatePassword'])->name('ganti-password.update')->middleware('throttle:5,1');
    Route::get('/notifikasi',      [PortalKaryawanController::class, 'notifikasi'])->name('notifikasi');
    Route::post('/notifikasi/hapus', [PortalKaryawanController::class, 'hapusNotifikasi'])->name('notifikasi.hapus');
    Route::get('/notifikasi/fetch',    [PortalKaryawanController::class, 'notifikasiFetch'])->name('notifikasi.fetch');
    Route::post('/notifikasi/mark-read', [PortalKaryawanController::class, 'notifikasiMarkRead'])->name('notifikasi.mark-read');
    Route::get('/profil',              [PortalKaryawanController::class, 'profil'])->name('profil');
    Route::get('/profil-lengkap',      [PortalKaryawanController::class, 'profilLengkap'])->name('profil-lengkap');
    Route::post('/profil/update',      [PortalKaryawanController::class, 'updateProfil'])->name('profil.update')->middleware('throttle:10,1');
    Route::get('/surat-peringatan',    [PortalKaryawanController::class, 'suratPeringatan'])->name('surat-peringatan');
    Route::get('/rekap-kehadiran',     [PortalKaryawanController::class, 'rekapKehadiran'])->name('rekap-kehadiran');
    Route::get('/riwayat-pengajuan',   [PortalKaryawanController::class, 'riwayatPengajuan'])->name('riwayat-pengajuan');
    Route::get('/fmk',                 [PortalKaryawanController::class, 'fmk'])->name('fmk');
    Route::post('/fmk',                [PortalKaryawanController::class, 'storeFmk'])->name('fmk.store')->middleware('throttle:10,1');
    Route::get('/kalender-cuti',       [PortalKaryawanController::class, 'kalenderCuti'])->name('kalender-cuti');
    Route::get('/pengumuman',          [PortalKaryawanController::class, 'pengumuman'])->name('pengumuman');
});

// Redirect setelah login
Route::middleware('auth')->get('/home', function () {
    $role = auth()->user()->role;
    return in_array($role, ['superadmin', 'admin', 'hrd', 'supervisor', 'operator'])
        ? redirect()->route('admin.dashboard')
        : redirect()->route('portal.index');
});

