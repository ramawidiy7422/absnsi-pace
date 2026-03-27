<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiKioskController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\ProfilController;
use App\Http\Controllers\Admin\UserController;

// Halaman utama redirect ke kiosk
Route::get('/', fn() => redirect()->route('kiosk.index'));

// Kiosk Absensi (publik - tidak perlu login)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [AbsensiKioskController::class, 'index'])->name('index');
    Route::post('/scan', [AbsensiKioskController::class, 'scan'])->name('scan');
    Route::post('/scan-qr', [AbsensiKioskController::class, 'scanQr'])->name('scan-qr');
});

// Auth routes
require __DIR__ . '/auth.php';

// Admin routes (harus login)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Profil ────────────────────────────────────────────────────────────
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    Route::post('/profil/nama', [ProfilController::class, 'updateNama'])->name('profil.nama');
    Route::post('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // ── Laporan ───────────────────────────────────────────────────────────
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/export-pdf', [LaporanController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-excel', [LaporanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/rekap-bulanan', [LaporanController::class, 'rekapBulanan'])->name('rekap-bulanan');
        Route::get('/slip-pdf/{karyawan}', [LaporanController::class, 'slipPdf'])->name('slip-pdf');
    });

    // ── Karyawan — statis dulu, baru parameter ────────────────────────────
    Route::get('karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
    Route::get('karyawan/print-qr-semua', [KaryawanController::class, 'printQrSemua'])->name('karyawan.print-qr-semua');
    Route::middleware('role:admin,hrd')->group(function () {
        Route::get('karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');
    });
    // Route dengan {karyawan} parameter di bawah semua statis
    Route::get('karyawan/{karyawan}', [KaryawanController::class, 'show'])->name('karyawan.show');
    Route::get('karyawan/{karyawan}/qrcode', [KaryawanController::class, 'generateQr'])->name('karyawan.qrcode');
    Route::get('karyawan/{karyawan}/print-qr', [KaryawanController::class, 'printQr'])->name('karyawan.print-qr');
    Route::middleware('role:admin,hrd')->group(function () {
        Route::get('karyawan/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
        Route::put('karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('karyawan.update');
    });
    Route::middleware('role:admin')->delete('karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy');

    // ── Shift — statis dulu, baru parameter ──────────────────────────────
    Route::get('shift', [ShiftController::class, 'index'])->name('shift.index');
    Route::middleware('role:admin,hrd')->group(function () {
        Route::get('shift/create', [ShiftController::class, 'create'])->name('shift.create');
        Route::post('shift', [ShiftController::class, 'store'])->name('shift.store');
    });
    Route::get('shift/{shift}', [ShiftController::class, 'show'])->name('shift.show');
    Route::middleware('role:admin,hrd')->group(function () {
        Route::get('shift/{shift}/edit', [ShiftController::class, 'edit'])->name('shift.edit');
        Route::put('shift/{shift}', [ShiftController::class, 'update'])->name('shift.update');
    });
    Route::middleware('role:admin')->delete('shift/{shift}', [ShiftController::class, 'destroy'])->name('shift.destroy');

    // ── Absensi — statis dulu, baru parameter ────────────────────────────
    Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::middleware('role:admin,hrd')->group(function () {
        Route::get('absensi/create', [AbsensiController::class, 'create'])->name('absensi.create');
        Route::post('absensi', [AbsensiController::class, 'store'])->name('absensi.store');
    });
    Route::get('absensi/{absensi}', [AbsensiController::class, 'show'])->name('absensi.show');
    Route::middleware('role:admin,hrd')->group(function () {
        Route::get('absensi/{absensi}/edit', [AbsensiController::class, 'edit'])->name('absensi.edit');
        Route::put('absensi/{absensi}', [AbsensiController::class, 'update'])->name('absensi.update');
    });
    Route::middleware('role:admin')->delete('absensi/{absensi}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');

    // ── User Management (admin only) ──────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

// AJAX: data realtime untuk notifikasi dashboard
Route::middleware('auth')->get('/admin/dashboard-realtime', function () {
    $totalKaryawan = \App\Models\Karyawan::where('status', 'aktif')->count();
    $sudahAbsen    = \App\Models\Absensi::whereDate('tanggal', today())->pluck('karyawan_id')->count();
    $belumAbsen    = max(0, $totalKaryawan - $sudahAbsen);
    $terlambat     = \App\Models\Absensi::whereDate('tanggal', today())->where('status', 'terlambat')->count();
    $hadir         = \App\Models\Absensi::whereDate('tanggal', today())->whereIn('status', ['hadir', 'terlambat'])->count();

    $terbaru = \App\Models\Absensi::with('karyawan')
        ->whereDate('tanggal', today())->latest()->take(5)->get()
        ->map(fn($a) => [
            'nama'       => $a->karyawan->nama ?? '-',
            'departemen' => $a->karyawan->departemen ?? '-',
            'jam_masuk'  => $a->jam_masuk,
            'jam_keluar' => $a->jam_keluar,
            'status'     => $a->status,
            'metode'     => $a->metode ?? 'wajah',
        ]);

    return response()->json(compact('belumAbsen', 'terlambat', 'hadir', 'totalKaryawan', 'terbaru'));
})->name('admin.dashboard-realtime');

// Cek status Python service (AJAX dari dashboard)
Route::middleware('auth')->get('/admin/python-status', function () {
    try {
        $res = \Illuminate\Support\Facades\Http::timeout(3)->get(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/health');
        return response()->json(['online' => true, 'model' => $res->json()['model'] ?? 'AI']);
    } catch (\Exception $e) {
        return response()->json(['online' => false]);
    }
})->name('admin.python-status');

// Redirect setelah login ke dashboard admin
Route::middleware('auth')->get('/home', fn() => redirect()->route('admin.dashboard'));
