<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfilController;
use App\Http\Controllers\Admin\PasswordResetRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\ApprovalSayaController;
// Laporan
use App\Http\Controllers\Admin\Laporan\AbsensiLaporanController;
use App\Http\Controllers\Admin\Laporan\RekapController;
use App\Http\Controllers\Admin\Laporan\JadwalShiftLaporanController;
use App\Http\Controllers\Admin\Laporan\KeterlambatanController;
use App\Http\Controllers\Admin\Laporan\PelanggaranController;

// Absensi
use App\Http\Controllers\Admin\Absensi\AbsensiController;
use App\Http\Controllers\Admin\AbsensiOperasionalHubController;
use App\Http\Controllers\Admin\Absensi\AbsensiImportController;
use App\Http\Controllers\Admin\Absensi\KoreksiAbsensiController;
use App\Http\Controllers\Admin\AlphaGeneratorController;

// Organisasi
use App\Http\Controllers\Admin\Organisasi\DepartmentController;
use App\Http\Controllers\Admin\Organisasi\DevisionController;
use App\Http\Controllers\Admin\Organisasi\PositionController;
use App\Http\Controllers\Admin\Organisasi\ShiftController;
use App\Http\Controllers\Admin\OrganisasiHubController;

// Karyawan sub-resources
use App\Http\Controllers\Admin\Karyawan\EducationController;
use App\Http\Controllers\Admin\Karyawan\LegaldocController;
use App\Http\Controllers\Admin\Karyawan\WorkhistoryController;
use App\Http\Controllers\Admin\Karyawan\WarningletterController;
use App\Http\Controllers\Admin\Karyawan\LeaverequestController;
use App\Http\Controllers\Admin\Karyawan\EmployeshiftController;
use App\Http\Controllers\Admin\Karyawan\LemburanController;
use App\Http\Controllers\Admin\Karyawan\SpkLemburanController;
use App\Http\Controllers\Admin\Karyawan\IzinController;
use App\Http\Controllers\Admin\Karyawan\IzinPulangCepatController;
use App\Http\Controllers\Admin\Karyawan\IzinTerlambatController;
use App\Http\Controllers\Admin\Karyawan\PelatihanController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\LogSistemController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\NotifikasiController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\Admin\KaryawanImportController;
use App\Http\Controllers\Admin\CutiSaldoController;
use App\Http\Controllers\Admin\SuratDinasController;
use App\Http\Controllers\Admin\FormulirHubController;
use App\Http\Controllers\Admin\FormulirMeninggalkanKerjaController;
use App\Http\Controllers\Admin\KontrakController;

// ── Dashboard & Profil ────────────────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/approval-saya', [ApprovalSayaController::class, 'index'])->name('approval-saya.index');
Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
Route::get('/profil/jadwal', [ProfilController::class, 'jadwalSaya'])->name('profil.jadwal');
Route::post('/profil/nama', [ProfilController::class, 'updateNama'])->name('profil.nama');
Route::post('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

// ── Laporan ───────────────────────────────────────────────────────────────────
Route::prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/', [AbsensiLaporanController::class, 'index'])->middleware('permission:laporan.lihat')->name('index');
    Route::get('/export-pdf', [AbsensiLaporanController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('export-pdf');
    Route::get('/export-excel', [AbsensiLaporanController::class, 'exportExcel'])->middleware('permission:laporan.export')->name('export-excel');

    Route::get('/rekap-bulanan',    [RekapController::class, 'rekapBulanan'])->middleware('permission:laporan.rekap-bulanan')->name('rekap-bulanan');
    Route::get('/rekap-departemen',            [RekapController::class, 'rekapPerDepartemen'])->middleware('permission:laporan.rekap-dept')->name('rekap-departemen');
    Route::get('/rekap-departemen/export-excel', [RekapController::class, 'exportRekapDepartemenExcel'])->middleware('permission:laporan.export')->name('rekap-departemen.export-excel');
    Route::get('/rekap-departemen/export-csv', [RekapController::class, 'exportRekapDepartemenCsv'])->middleware('permission:laporan.export')->name('rekap-departemen.export-csv');
    Route::get('/rekap-departemen/export-pdf', [RekapController::class, 'exportRekapDepartemenPdf'])->middleware('permission:laporan.export')->name('rekap-departemen.export-pdf');
    Route::get('/rekap-bulanan/export-excel', [RekapController::class, 'exportRekapBulananExcel'])->middleware('permission:laporan.export')->name('rekap-bulanan.export-excel');
    Route::get('/rekap-karyawan',            [RekapController::class, 'rekapKaryawan'])->middleware('permission:laporan.rekap-karyawan')->name('rekap-karyawan');
    Route::get('/rekap-karyawan/export-pdf', [RekapController::class, 'exportRekapKaryawanPdf'])->middleware('permission:laporan.export')->name('rekap-karyawan.export-pdf');
    Route::get('/rekap-karyawan/export-excel', [RekapController::class, 'exportRekapKaryawanExcel'])->middleware('permission:laporan.export')->name('rekap-karyawan.export-excel');
    Route::get('/rekap-cuti-izin',        [RekapController::class, 'rekapCutiIzin'])->middleware('permission:laporan.rekap-cuti')->name('rekap-cuti-izin');
    Route::get('/rekap-cuti-izin/export-pdf', [RekapController::class, 'exportCutiIzinPdf'])->middleware('permission:laporan.export')->name('rekap-cuti-izin.export-pdf');
    Route::get('/rekap-cuti-izin/export-csv', [RekapController::class, 'exportCutiIzinCsv'])->middleware('permission:laporan.export')->name('rekap-cuti-izin.export-csv');
    Route::get('/export-csv',             [RekapController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('export-csv');
    Route::get('/export-rekap-pdf', [RekapController::class, 'exportRekapPdf'])->middleware('permission:laporan.export')->name('export-rekap-pdf');
    Route::get('/slip-pdf/{karyawan}', [RekapController::class, 'slipPdf'])->middleware('permission:laporan.export')->name('slip-pdf');
    Route::get('/rekap-potongan-tunjangan', [RekapController::class, 'rekapPotonganTunjangan'])->middleware('permission:laporan.lihat')->name('rekap-potongan-tunjangan');
    Route::get('/rekap-potongan-tunjangan/export-csv', [RekapController::class, 'exportPotonganTunjanganCsv'])->middleware('permission:laporan.export')->name('rekap-potongan-tunjangan.export-csv');

    Route::get('/jadwal-shift',            [JadwalShiftLaporanController::class, 'index'])->middleware('permission:laporan.jadwal-shift')->name('jadwal-shift');
    Route::get('/jadwal-shift/export-pdf', [JadwalShiftLaporanController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('jadwal-shift.export-pdf');
    Route::get('/jadwal-karyawan',         [JadwalShiftLaporanController::class, 'jadwalKaryawan'])->middleware('permission:laporan.jadwal-shift')->name('jadwal-karyawan');
    Route::get('/buat-jadwal-shift',       [JadwalShiftLaporanController::class, 'buatJadwal'])->middleware('permission:laporan.jadwal-shift')->name('buat-jadwal-shift');

    Route::get('/keterlambatan',            [KeterlambatanController::class, 'index'])->middleware('permission:laporan.lihat')->name('keterlambatan');
    Route::get('/keterlambatan/export-csv', [KeterlambatanController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('keterlambatan.csv');
    Route::get('/keterlambatan/export-pdf',   [KeterlambatanController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('keterlambatan.pdf');
    Route::get('/keterlambatan/export-excel', [KeterlambatanController::class, 'exportExcel'])->middleware('permission:laporan.export')->name('keterlambatan.export-excel');

    Route::get('/pelanggaran',              [PelanggaranController::class, 'index'])->middleware('permission:laporan.lihat')->name('pelanggaran');
    Route::get('/pelanggaran/export-csv',   [PelanggaranController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('pelanggaran.csv');
    Route::get('/pelanggaran/export-pdf',   [PelanggaranController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('pelanggaran.pdf');
    Route::get('/pelanggaran/export-excel', [PelanggaranController::class, 'exportExcel'])->middleware('permission:laporan.export')->name('pelanggaran.export-excel');

    Route::get('/trend-kehadiran', [RekapController::class, 'trendKehadiran'])->middleware('permission:laporan.lihat')->name('trend-kehadiran');
    Route::get('/trend-kehadiran/export-excel', [RekapController::class, 'exportTrendExcel'])->middleware('permission:laporan.export')->name('trend-kehadiran.export-excel');

    Route::get('/rekap-lembur',              [RekapController::class, 'rekapLembur'])->middleware('permission:laporan.lihat')->name('rekap-lembur');
    Route::get('/rekap-lembur/export-excel', [RekapController::class, 'exportRekapLemburExcel'])->middleware('permission:laporan.export')->name('rekap-lembur.export-excel');
});

// ── Karyawan ──────────────────────────────────────────────────────────────────
Route::get('karyawan', [KaryawanController::class, 'index'])->middleware('permission:karyawan.lihat')->name('karyawan.index');
Route::get('karyawan/kontrak', [KontrakController::class, 'index'])->middleware('permission:karyawan.lihat')->name('kontrak.index');
Route::get('karyawan/export-csv', [KaryawanController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('karyawan.export-csv');
Route::get('karyawan/export-pdf', [KaryawanController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('karyawan.export-pdf');
Route::get('karyawan/print-qr-semua', [KaryawanController::class, 'printQrSemua'])->middleware('permission:karyawan.lihat')->name('karyawan.print-qr-semua');

Route::middleware('permission:karyawan.tambah')->group(function () {
    Route::get('karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create');
    Route::post('karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');
});
Route::middleware('permission:karyawan.import')->group(function () {
    Route::get('karyawan/import',          [KaryawanImportController::class, 'index'])->name('karyawan.import');
    Route::post('karyawan/import',         [KaryawanImportController::class, 'store'])->name('karyawan.import.store');
    Route::get('karyawan/import/template', [KaryawanImportController::class, 'template'])->name('karyawan.import.template');
});
Route::get('karyawan/id-card-semua', [KaryawanController::class, 'idCardSemua'])->middleware('permission:karyawan.lihat')->name('karyawan.id-card-semua');
Route::get('karyawan/{karyawan}', [KaryawanController::class, 'show'])->middleware('permission:karyawan.lihat')->name('karyawan.show');
Route::get('karyawan/{karyawan}/qrcode', [KaryawanController::class, 'generateQr'])->middleware('permission:karyawan.lihat')->name('karyawan.qrcode');
Route::get('karyawan/{karyawan}/print-qr', [KaryawanController::class, 'printQr'])->middleware('permission:karyawan.lihat')->name('karyawan.print-qr');
Route::get('karyawan/{karyawan}/id-card', [KaryawanController::class, 'idCard'])->middleware('permission:karyawan.lihat')->name('karyawan.id-card');

Route::middleware('permission:karyawan.edit')->group(function () {
    Route::get('karyawan/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
    Route::put('karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('karyawan.update');
    Route::post('karyawan/{karyawan}/foto', [KaryawanController::class, 'updateFoto'])->name('karyawan.foto');
});
Route::delete('karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->middleware('permission:karyawan.hapus')->name('karyawan.destroy');
Route::delete('karyawan/{karyawan}/permanen', [KaryawanController::class, 'destroyPermanent'])->middleware('permission:karyawan.hapus')->name('karyawan.destroy-permanent');
Route::post('karyawan/bulk-buat-akun', [KaryawanController::class, 'bulkBuatAkun'])->middleware('permission:karyawan.buat-akun')->name('karyawan.bulk-buat-akun');
Route::post('karyawan/sync-wajah', [KaryawanController::class, 'syncWajah'])->name('karyawan.sync-wajah');
Route::post('karyawan/{karyawan}/buat-akun', [KaryawanController::class, 'buatAkun'])->middleware('permission:karyawan.buat-akun')->name('karyawan.buat-akun');

// ── Data Karyawan (per-karyawan sub-resources) ────────────────────────────────
Route::middleware('permission:karyawan.edit')->group(function () {
    // Pendidikan
    Route::post('karyawan/{karyawan}/education', [EducationController::class, 'store'])->name('education.store');
    Route::put('education/{education}', [EducationController::class, 'update'])->name('education.update');
    Route::delete('education/{education}', [EducationController::class, 'destroy'])->name('education.destroy');

    // Dokumen Legal
    Route::post('karyawan/{karyawan}/legaldoc', [LegaldocController::class, 'store'])->name('legaldoc.store');
    Route::put('legaldoc/{legaldoc}', [LegaldocController::class, 'update'])->name('legaldoc.update');
    Route::delete('legaldoc/{legaldoc}', [LegaldocController::class, 'destroy'])->name('legaldoc.destroy');

    // Riwayat Kerja
    Route::post('karyawan/{karyawan}/workhistory', [WorkhistoryController::class, 'store'])->name('workhistory.store');
    Route::put('workhistory/{workhistory}', [WorkhistoryController::class, 'update'])->name('workhistory.update');
    Route::delete('workhistory/{workhistory}', [WorkhistoryController::class, 'destroy'])->name('workhistory.destroy');

    // Surat Peringatan
    Route::post('karyawan/{karyawan}/warninglettter', [WarningletterController::class, 'store'])->middleware('permission:karyawan.edit')->name('warninglettter.store');
    Route::put('warninglettter/{warninglettter}', [WarningletterController::class, 'update'])->middleware('permission:karyawan.edit')->name('warninglettter.update');
    Route::delete('warninglettter/{warninglettter}', [WarningletterController::class, 'destroy'])->middleware('permission:karyawan.edit')->name('warninglettter.destroy');

    // Pelatihan & Sertifikasi
    Route::post('karyawan/{karyawan}/pelatihan', [PelatihanController::class, 'store'])->name('pelatihan.store');
    Route::put('pelatihan/{pelatihan}', [PelatihanController::class, 'update'])->name('pelatihan.update');
    Route::delete('pelatihan/{pelatihan}', [PelatihanController::class, 'destroy'])->name('pelatihan.destroy');

    // Jadwal Shift
    Route::post('karyawan/{karyawan}/employeshifts', [EmployeshiftController::class, 'store'])->name('employeshift.store');
    Route::post('karyawan/{karyawan}/employeshifts/range', [EmployeshiftController::class, 'storeRange'])->name('employeshift.storeRange');
    Route::post('employeshifts/bulk', [EmployeshiftController::class, 'storeBulk'])->name('employeshift.storeBulk');
    Route::put('employeshifts/{employeshift}', [EmployeshiftController::class, 'update'])->name('employeshift.update');
    Route::delete('employeshifts/{employeshift}', [EmployeshiftController::class, 'destroy'])->name('employeshift.destroy');
    Route::patch('karyawan/{karyawan}/shift-default', [EmployeshiftController::class, 'updateShiftDefault'])->name('employeshift.shift-default');
    Route::post('karyawan/{karyawan}/shift-default', [EmployeshiftController::class, 'updateShiftDefault'])->name('employeshift.shift-default-post');
});

// Surat Peringatan & Pelatihan (view-only — tidak butuh karyawan.edit)
Route::get('warninglettter', [WarningletterController::class, 'index'])->middleware('permission:karyawan.lihat')->name('warninglettter.index');
Route::get('warninglettter/export-csv', [WarningletterController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('warninglettter.export-csv');
Route::get('karyawan/{karyawan}/warninglettter/export-pdf', [WarningletterController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('warninglettter.export-pdf');
Route::get('pelatihan', [PelatihanController::class, 'index'])->middleware('permission:karyawan.lihat')->name('pelatihan.index');
Route::get('pelatihan/export-csv', [PelatihanController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('pelatihan.export-csv');
Route::get('pelatihan/export-pdf', [PelatihanController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('pelatihan.export-pdf');

// ── Shift Kerja ───────────────────────────────────────────────────────────────
Route::get('shift', [ShiftController::class, 'index'])->middleware('permission:shift.lihat')->name('shift.index');
Route::middleware('permission:shift.kelola')->group(function () {
    Route::get('shift/create', [ShiftController::class, 'create'])->name('shift.create');
    Route::post('shift', [ShiftController::class, 'store'])->name('shift.store');
});
Route::get('shift/{shift}', [ShiftController::class, 'show'])->middleware('permission:shift.lihat')->name('shift.show');
Route::middleware('permission:shift.kelola')->group(function () {
    Route::get('shift/{shift}/edit', [ShiftController::class, 'edit'])->name('shift.edit');
    Route::put('shift/{shift}', [ShiftController::class, 'update'])->name('shift.update');
    Route::delete('shift/{shift}', [ShiftController::class, 'destroy'])->name('shift.destroy');
    Route::post('shift/bulk-assign', [ShiftController::class, 'bulkAssign'])->name('shift.bulk-assign');
});

// ── Absensi ───────────────────────────────────────────────────────────────────
Route::get('absensi-operasional', [AbsensiOperasionalHubController::class, 'index'])
    ->middleware('permission:absensi.lihat,absensi.edit')
    ->name('absensi-operasional.index');
Route::get('absensi', [AbsensiController::class, 'index'])->middleware('permission:absensi.lihat')->name('absensi.index');
Route::get('absensi/belum-absen', [AbsensiController::class, 'belumAbsen'])->middleware('permission:absensi.lihat')->name('absensi.belum-absen');
Route::post('absensi/absen-massal', [AbsensiController::class, 'absenMassal'])->middleware('permission:absensi.tambah')->name('absensi.absen-massal');
Route::middleware('permission:absensi.tambah')->group(function () {
    Route::get('absensi/create', [AbsensiController::class, 'create'])->name('absensi.create');
    Route::post('absensi', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('absensi/import', [AbsensiImportController::class, 'index'])->name('absensi.import');
    Route::post('absensi/import', [AbsensiImportController::class, 'store'])->name('absensi.import.store');
    Route::get('absensi/import/template', [AbsensiImportController::class, 'template'])->name('absensi.import.template');
});
Route::middleware('permission:absensi.edit')->group(function () {
    Route::get('absensi/alpha-generator', [AlphaGeneratorController::class, 'index'])->name('absensi.alpha-generator');
    Route::post('absensi/alpha-generator/run', [AlphaGeneratorController::class, 'run'])->name('absensi.alpha-generator.run');
});
Route::get('absensi/{absensi}', [AbsensiController::class, 'show'])->middleware('permission:absensi.lihat')->name('absensi.show');
Route::middleware('permission:absensi.edit')->group(function () {
    Route::get('absensi/{absensi}/edit', [AbsensiController::class, 'edit'])->name('absensi.edit');
    Route::put('absensi/{absensi}', [AbsensiController::class, 'update'])->name('absensi.update');
    Route::patch('absensi/{absensi}/alasan-terlambat', [AbsensiController::class, 'updateAlasanTerlambat'])->name('absensi.alasan-terlambat');
});
Route::delete('absensi/{absensi}', [AbsensiController::class, 'destroy'])->middleware('permission:absensi.hapus')->name('absensi.destroy');

// Koreksi Absensi
Route::middleware('permission:absensi.lihat')->group(function () {
    Route::get('koreksi-absensi', [KoreksiAbsensiController::class, 'index'])->name('koreksi-absensi.index');
});
Route::middleware('permission:absensi.edit')->group(function () {
    Route::post('koreksi-absensi/bulk-approve', [KoreksiAbsensiController::class, 'bulkApprove'])->name('koreksi-absensi.bulk-approve');
    Route::post('koreksi-absensi/{koreksi}/approve', [KoreksiAbsensiController::class, 'approve'])->name('koreksi-absensi.approve');
    Route::post('koreksi-absensi/{koreksi}/reject', [KoreksiAbsensiController::class, 'reject'])->name('koreksi-absensi.reject');
});

// ── Lembur (Overtime) ─────────────────────────────────────────────────────────
Route::get('lemburan', [LemburanController::class, 'index'])->middleware('permission:lembur.lihat')->name('lemburan.index');
Route::get('lemburan/rekap', [LemburanController::class, 'rekap'])->middleware('permission:lembur.lihat')->name('lemburan.rekap');
Route::get('lemburan/rekap/export-csv',   [LemburanController::class, 'rekapExportCsv'])->middleware('permission:laporan.export')->name('lemburan.rekap.csv');
Route::get('lemburan/rekap/export-pdf',   [LemburanController::class, 'rekapExportPdf'])->middleware('permission:laporan.export')->name('lemburan.rekap.pdf');
Route::get('lemburan/rekap/export-excel', [LemburanController::class, 'rekapExportExcel'])->middleware('permission:laporan.export')->name('lemburan.rekap.excel');

Route::post('karyawan/{karyawan}/lemburan', [LemburanController::class, 'store'])->middleware('permission:lembur.tambah')->name('lemburan.store');
Route::put('lemburan/{lemburan}', [LemburanController::class, 'update'])->middleware('permission:lembur.edit')->name('lemburan.update');
Route::delete('lemburan/{lemburan}', [LemburanController::class, 'destroy'])->middleware('permission:lembur.hapus')->name('lemburan.destroy');
Route::middleware('permission:lembur.approve')->group(function () {
    Route::post('lemburan/{lemburan}/approve', [LemburanController::class, 'approve'])->name('lemburan.approve');
    Route::post('lemburan/{lemburan}/reject', [LemburanController::class, 'reject'])->name('lemburan.reject');
    Route::post('lemburan/bulk-approve', [LemburanController::class, 'bulkApprove'])->name('lemburan.bulk-approve');
    Route::post('lemburan/bulk-reject', [LemburanController::class, 'bulkReject'])->name('lemburan.bulk-reject');
});

// ── SPK Lembur ────────────────────────────────────────────────────────────────
Route::middleware('permission:lembur.lihat')->group(function () {
    Route::get('spk-lembur',                         [SpkLemburanController::class, 'index'])->name('spk-lembur.index');
});
Route::middleware('permission:lembur.tambah')->group(function () {
    Route::get('spk-lembur/create',                  [SpkLemburanController::class, 'create'])->name('spk-lembur.create');
    Route::post('spk-lembur',                        [SpkLemburanController::class, 'store'])->name('spk-lembur.store');
});
Route::middleware('permission:lembur.lihat')->group(function () {
    Route::get('spk-lembur/{spkLembur}',             [SpkLemburanController::class, 'show'])->name('spk-lembur.show');
});
Route::middleware('permission:lembur.approve')->group(function () {
    Route::post('spk-lembur/{spkLembur}/aktifkan',   [SpkLemburanController::class, 'aktifkan'])->name('spk-lembur.aktifkan');
    Route::post('spk-lembur/{spkLembur}/selesaikan', [SpkLemburanController::class, 'selesaikan'])->name('spk-lembur.selesaikan');
    Route::post('spk-lembur/{spkLembur}/batalkan',   [SpkLemburanController::class, 'batalkan'])->name('spk-lembur.batalkan');
});
Route::delete('spk-lembur/{spkLembur}',              [SpkLemburanController::class, 'destroy'])->middleware('permission:lembur.hapus')->name('spk-lembur.destroy');

// ── Izin Tidak Masuk ──────────────────────────────────────────────────────────
Route::get('izin', [IzinController::class, 'index'])->middleware('permission:izin.lihat')->name('izin.index');
Route::get('izin/export-csv', [IzinController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('izin.export-csv');
Route::get('izin/export-pdf', [IzinController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('izin.export-pdf');
Route::post('karyawan/{karyawan}/izin', [IzinController::class, 'store'])->middleware('permission:izin.tambah')->name('izin.store');
Route::delete('izin/{izin}', [IzinController::class, 'destroy'])->middleware('permission:izin.hapus')->name('izin.destroy');
Route::middleware('permission:izin.approve')->group(function () {
    Route::post('izin/bulk-approve', [IzinController::class, 'bulkApprove'])->name('izin.bulk-approve');
    Route::post('izin/bulk-reject',  [IzinController::class, 'bulkReject'])->name('izin.bulk-reject');
    Route::post('izin/{izin}/approve', [IzinController::class, 'approve'])->name('izin.approve');
    Route::post('izin/{izin}/reject',  [IzinController::class, 'reject'])->name('izin.reject');
});

// ── Izin Terlambat (via WA) ────────────────────────────────────────────────
Route::get('izin-terlambat', [IzinTerlambatController::class, 'index'])->middleware('permission:izin-terlambat.lihat')->name('izin-terlambat.index');
Route::post('karyawan/{karyawan}/izin-terlambat', [IzinTerlambatController::class, 'store'])->middleware('permission:izin-terlambat.tambah')->name('izin-terlambat.store');
Route::delete('izin-terlambat/{izinTerlambat}', [IzinTerlambatController::class, 'destroy'])->middleware('permission:izin-terlambat.hapus')->name('izin-terlambat.destroy');
Route::middleware('permission:izin-terlambat.approve')->group(function () {
    Route::post('izin-terlambat/{izinTerlambat}/approve', [IzinTerlambatController::class, 'approve'])->name('izin-terlambat.approve');
    Route::post('izin-terlambat/{izinTerlambat}/reject',  [IzinTerlambatController::class, 'reject'])->name('izin-terlambat.reject');
    Route::post('izin-terlambat/bulk-approve', [IzinTerlambatController::class, 'bulkApprove'])->name('izin-terlambat.bulk-approve');
    Route::post('izin-terlambat/bulk-reject',  [IzinTerlambatController::class, 'bulkReject'])->name('izin-terlambat.bulk-reject');
});

// ── Izin Pulang Cepat ────────────────────────────────────────────────────────
Route::get('izin-pulang-cepat', [IzinPulangCepatController::class, 'index'])->middleware('permission:izin-pulang-cepat.lihat')->name('izin-pulang-cepat.index');
Route::post('karyawan/{karyawan}/izin-pulang-cepat', [IzinPulangCepatController::class, 'store'])->middleware('permission:izin-pulang-cepat.tambah')->name('izin-pulang-cepat.store');
Route::delete('izin-pulang-cepat/{izinPulangCepat}', [IzinPulangCepatController::class, 'destroy'])->middleware('permission:izin-pulang-cepat.hapus')->name('izin-pulang-cepat.destroy');
Route::middleware('permission:izin-pulang-cepat.approve')->group(function () {
    Route::post('izin-pulang-cepat/{izinPulangCepat}/approve', [IzinPulangCepatController::class, 'approve'])->name('izin-pulang-cepat.approve');
    Route::post('izin-pulang-cepat/{izinPulangCepat}/reject',  [IzinPulangCepatController::class, 'reject'])->name('izin-pulang-cepat.reject');
    Route::post('izin-pulang-cepat/bulk-approve', [IzinPulangCepatController::class, 'bulkApprove'])->name('izin-pulang-cepat.bulk-approve');
    Route::post('izin-pulang-cepat/bulk-reject',  [IzinPulangCepatController::class, 'bulkReject'])->name('izin-pulang-cepat.bulk-reject');
});

// ── Saldo Cuti ───────────────────────────────────────────────────────────────
Route::middleware('permission:cuti.lihat')->group(function () {
    Route::get('cuti-saldo', [CutiSaldoController::class, 'index'])->name('cuti-saldo.index');
    Route::get('cuti-saldo/export-csv', [CutiSaldoController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('cuti-saldo.export-csv');
    Route::get('cuti-saldo/export-pdf', [CutiSaldoController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('cuti-saldo.export-pdf');
});
Route::middleware('permission:cuti.edit')->group(function () {
    Route::put('cuti-saldo/{karyawan}', [CutiSaldoController::class, 'update'])->name('cuti-saldo.update');
    Route::post('cuti-saldo/bulk', [CutiSaldoController::class, 'bulkSetJatah'])->name('cuti-saldo.bulk');
});

// ── Cuti & Leave Request ──────────────────────────────────────────────────────
Route::get('leaverequest', [LeaverequestController::class, 'index'])->middleware('permission:cuti.lihat')->name('leaverequest.index');
Route::get('leaverequest/export-csv', [LeaverequestController::class, 'exportCsv'])->middleware('permission:laporan.export')->name('leaverequest.export-csv');
Route::get('leaverequest/export-pdf', [LeaverequestController::class, 'exportPdf'])->middleware('permission:laporan.export')->name('leaverequest.export-pdf');
Route::get('leaverequest/{leaverequest}/detail', [LeaverequestController::class, 'detail'])->middleware('permission:cuti.lihat')->name('leaverequest.detail');
Route::post('karyawan/{karyawan}/leaverequest', [LeaverequestController::class, 'store'])->middleware('permission:cuti.tambah')->name('leaverequest.store');
Route::put('leaverequest/{leaverequest}', [LeaverequestController::class, 'update'])->middleware('permission:cuti.edit')->name('leaverequest.update');
Route::delete('leaverequest/{leaverequest}', [LeaverequestController::class, 'destroy'])->middleware('permission:cuti.hapus')->name('leaverequest.destroy');
Route::middleware('permission:cuti.approve')->group(function () {
    Route::post('leaverequest/bulk-approve', [LeaverequestController::class, 'bulkApprove'])->name('leaverequest.bulk-approve');
    Route::post('leaverequest/bulk-reject',  [LeaverequestController::class, 'bulkReject'])->name('leaverequest.bulk-reject');
    Route::post('leaverequest/{leaverequest}/approve', [LeaverequestController::class, 'approve'])->name('leaverequest.approve');
    Route::post('leaverequest/{leaverequest}/reject',  [LeaverequestController::class, 'reject'])->name('leaverequest.reject');
});

// ── Data Master Organisasi ────────────────────────────────────────────────────
Route::get('organisasi', [OrganisasiHubController::class, 'index'])
    ->middleware('permission:departemen.kelola,divisi.kelola,jabatan.kelola')
    ->name('organisasi.index');
Route::middleware('permission:departemen.kelola')->group(function () {
    Route::get('department', [DepartmentController::class, 'index'])->name('department.index');
    Route::post('department', [DepartmentController::class, 'store'])->name('department.store');
    Route::put('department/{department}', [DepartmentController::class, 'update'])->name('department.update');
    Route::delete('department/{department}', [DepartmentController::class, 'destroy'])->name('department.destroy');
});
Route::middleware('permission:divisi.kelola')->group(function () {
    Route::get('devision', [DevisionController::class, 'index'])->name('devision.index');
    Route::post('devision', [DevisionController::class, 'store'])->name('devision.store');
    Route::put('devision/{devision}', [DevisionController::class, 'update'])->name('devision.update');
    Route::delete('devision/{devision}', [DevisionController::class, 'destroy'])->name('devision.destroy');
});
Route::middleware('permission:jabatan.kelola')->group(function () {
    Route::get('position', [PositionController::class, 'index'])->name('position.index');
    Route::post('position', [PositionController::class, 'store'])->name('position.store');
    Route::put('position/{position}', [PositionController::class, 'update'])->name('position.update');
    Route::delete('position/{position}', [PositionController::class, 'destroy'])->name('position.destroy');
});

// ── User Management ───────────────────────────────────────────────────────────
Route::middleware('permission:users.lihat')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
});
Route::middleware('permission:users.kelola')->group(function () {
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::post('users/bulk-create', [UserController::class, 'bulkCreate'])->name('users.bulk-create');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/reset-password-random', [UserController::class, 'resetPasswordRandom'])->name('users.reset-password-random');
});

// ── Permintaan Reset Password (dari self-service karyawan) ─────────────────────
Route::get('reset-password-requests', [PasswordResetRequestController::class, 'index'])->middleware('permission:reset-password.lihat')->name('reset-password-requests.index');
Route::middleware('permission:reset-password.approve')->group(function () {
    Route::post('reset-password-requests/{resetPasswordRequest}/approve', [PasswordResetRequestController::class, 'approve'])->name('reset-password-requests.approve');
    Route::post('reset-password-requests/{resetPasswordRequest}/reject', [PasswordResetRequestController::class, 'reject'])->name('reset-password-requests.reject');
});

// ── Hari Libur ────────────────────────────────────────────────────────────────
Route::get('hari-libur', [HariLiburController::class, 'index'])->middleware('permission:hari-libur.lihat')->name('hari-libur.index');
Route::post('hari-libur', [HariLiburController::class, 'store'])->middleware('permission:hari-libur.tambah')->name('hari-libur.store');
Route::put('hari-libur/{hariLibur}', [HariLiburController::class, 'update'])->middleware('permission:hari-libur.edit')->name('hari-libur.update');
Route::delete('hari-libur/{hariLibur}', [HariLiburController::class, 'destroy'])->middleware('permission:hari-libur.hapus')->name('hari-libur.destroy');
Route::post('hari-libur/import-nasional', [HariLiburController::class, 'importNasional'])->middleware('permission:hari-libur.import')->name('hari-libur.import');

// ── Pengaturan Sistem ─────────────────────────────────────────────────────────
Route::get('pengaturan', [PengaturanController::class, 'index'])->middleware('permission:pengaturan.lihat')->name('pengaturan.index');
Route::put('pengaturan', [PengaturanController::class, 'update'])->middleware('permission:pengaturan.edit')->name('pengaturan.update');

// ── Audit Log ─────────────────────────────────────────────────────────────────
Route::get('log-sistem', [LogSistemController::class, 'index'])->middleware('permission:audit-log.lihat,login-history.lihat')->name('log-sistem.index');

// Digabung ke log-sistem (tab Audit Log / Riwayat Login) — redirect agar link/bookmark lama tetap berfungsi.
Route::get('audit-log', fn () => redirect()->route('admin.log-sistem.index', ['_tab' => 'tab-audit-log'] + request()->query()))->middleware('permission:audit-log.lihat')->name('audit-log.index');
Route::get('audit-log/export-csv', [AuditLogController::class, 'exportCsv'])->middleware('permission:audit-log.lihat')->name('audit-log.export-csv');
Route::get('login-history', fn () => redirect()->route('admin.log-sistem.index', ['_tab' => 'tab-login-history'] + request()->query()))->middleware('permission:login-history.lihat')->name('login-history.index');
Route::get('login-history/export-csv', [LoginHistoryController::class, 'exportCsv'])->middleware('permission:login-history.lihat')->name('login-history.export-csv');
Route::get('role-permission', [RolePermissionController::class, 'index'])->middleware('permission:role.kelola')->name('role-permission.index');
Route::put('role-permission/{role}', [RolePermissionController::class, 'update'])->middleware('permission:role.kelola')->name('role-permission.update');
Route::get('user-permission/{user}', [RolePermissionController::class, 'userPermission'])->middleware('permission:role.kelola')->name('user-permission.show');
Route::post('user-permission/{user}', [RolePermissionController::class, 'userPermissionUpdate'])->middleware('permission:role.kelola')->name('user-permission.update');

// ── Pengumuman & Memo ─────────────────────────────────────────────────────────
Route::get('pengumuman', [PengumumanController::class, 'index'])->middleware('permission:pengumuman.lihat')->name('pengumuman.index');
Route::middleware('permission:pengumuman.kelola')->group(function () {
    Route::post('pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::put('pengumuman/{pengumuman}', [PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::post('pengumuman/{pengumuman}/toggle-aktif', [PengumumanController::class, 'toggleAktif'])->name('pengumuman.toggle-aktif');
    Route::delete('pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
});

// ── Notifikasi In-App ─────────────────────────────────────────────────────────
Route::get('notifikasi',            [NotifikasiController::class, 'index'])->name('notifikasi.index');
Route::get('notifikasi/fetch',      [NotifikasiController::class, 'fetch'])->name('notifikasi.fetch');
Route::post('notifikasi/mark-read', [NotifikasiController::class, 'markRead'])->name('notifikasi.mark-read');
Route::delete('notifikasi',         [NotifikasiController::class, 'destroy'])->name('notifikasi.destroy');

// ── AJAX Endpoints ────────────────────────────────────────────────────────────
Route::get('/karyawan-by-dept', function (\Illuminate\Http\Request $req) {
    $list = \App\Models\Karyawan::with(['department', 'shift'])
        ->whereIn('status', ['kontrak','tetap','magang','training'])
        ->when($req->department_id, fn($q) => $q->where('department_id', $req->department_id))
        ->orderBy('nama')
        ->get(['id','nik','nama','departemen','jabatan','department_id','shift_id'])
        ->map(fn($k) => [
            'id'         => $k->id,
            'nik'        => $k->nik,
            'nama'       => $k->nama,
            'dept_name'  => $k->department?->name ?? $k->departemen,
            'jabatan'    => $k->jabatan,
            'shift_id'   => $k->shift_id,
            'shift_nama' => $k->shift?->nama_shift,
        ]);
    return response()->json($list);
})->name('karyawan-by-dept');

Route::get('/notif-count', function () {
    return response()->json([
        'cuti_pending' => \App\Models\Leaverequest::where('status', 'pending')->count(),
        'sp_aktif'     => \App\Models\Warninglettter::where('status', 'aktif')->count(),
    ]);
})->name('notif-count');

Route::get('/dashboard-realtime', function () {
    $totalKaryawan = \App\Models\Karyawan::whereIn('status', ['kontrak','tetap','magang'])->count();
    $sudahAbsen    = \App\Models\Absensi::whereDate('tanggal', today())->pluck('karyawan_id')->count();
    $belumAbsen    = max(0, $totalKaryawan - $sudahAbsen);
    $terlambat     = \App\Models\Absensi::whereDate('tanggal', today())->where('status', 'terlambat')->count();
    $hadir         = \App\Models\Absensi::whereDate('tanggal', today())->whereIn('status', ['hadir', 'terlambat'])->count();

    $terbaru = \App\Models\Absensi::with('karyawan.department')
        ->whereDate('tanggal', today())->latest()->take(5)->get()
        ->map(fn($a) => [
            'nama'       => $a->karyawan->nama ?? '-',
            'departemen' => $a->karyawan->department?->name ?? $a->karyawan->departemen ?? '-',
            'jam_masuk'  => $a->jam_masuk,
            'jam_keluar' => $a->jam_keluar,
            'status'     => $a->status,
            'metode'     => $a->metode ?? 'wajah',
        ]);

    return response()->json(compact('belumAbsen', 'terlambat', 'hadir', 'totalKaryawan', 'terbaru'));
})->name('dashboard-realtime');

// ── Surat & Formulir Hub ─────────────────────────────────────────────────────
Route::get('formulir', [FormulirHubController::class, 'index'])->name('formulir.index');

// Formulir Meninggalkan Kerja
Route::get('formulir/meninggalkan-kerja/export-excel', [FormulirMeninggalkanKerjaController::class, 'exportExcel'])->middleware('permission:laporan.export')->name('formulir.meninggalkan-kerja.export-excel');
Route::get('formulir/meninggalkan-kerja/create', [FormulirMeninggalkanKerjaController::class, 'create'])->middleware('permission:fmk.tambah')->name('formulir.meninggalkan-kerja.create');
Route::post('formulir/meninggalkan-kerja',       [FormulirMeninggalkanKerjaController::class, 'store'])->middleware('permission:fmk.tambah')->name('formulir.meninggalkan-kerja.store');
Route::middleware('permission:fmk.lihat')->group(function () {
    Route::get('formulir/meninggalkan-kerja',                     [FormulirMeninggalkanKerjaController::class, 'index'])->name('formulir.meninggalkan-kerja.index');
    Route::get('formulir/meninggalkan-kerja/{meninggalkanKerja}',  [FormulirMeninggalkanKerjaController::class, 'show'])->name('formulir.meninggalkan-kerja.show');
    Route::get('formulir/meninggalkan-kerja/{meninggalkanKerja}/pdf', [FormulirMeninggalkanKerjaController::class, 'pdf'])->name('formulir.meninggalkan-kerja.pdf');
});
Route::get('formulir/meninggalkan-kerja/{meninggalkanKerja}/edit', [FormulirMeninggalkanKerjaController::class, 'edit'])->middleware('permission:fmk.edit')->name('formulir.meninggalkan-kerja.edit');
Route::put('formulir/meninggalkan-kerja/{meninggalkanKerja}',      [FormulirMeninggalkanKerjaController::class, 'update'])->middleware('permission:fmk.edit')->name('formulir.meninggalkan-kerja.update');
Route::delete('formulir/meninggalkan-kerja/{meninggalkanKerja}',   [FormulirMeninggalkanKerjaController::class, 'destroy'])->middleware('permission:fmk.hapus')->name('formulir.meninggalkan-kerja.destroy');
Route::middleware('permission:fmk.approve')->group(function () {
    Route::post('formulir/meninggalkan-kerja/{meninggalkanKerja}/approve', [FormulirMeninggalkanKerjaController::class, 'approve'])->name('formulir.meninggalkan-kerja.approve');
    Route::post('formulir/meninggalkan-kerja/{meninggalkanKerja}/tolak',   [FormulirMeninggalkanKerjaController::class, 'tolak'])->name('formulir.meninggalkan-kerja.tolak');
    Route::post('formulir/meninggalkan-kerja/bulk-approve', [FormulirMeninggalkanKerjaController::class, 'bulkApprove'])->name('formulir.meninggalkan-kerja.bulk-approve');
    Route::post('formulir/meninggalkan-kerja/bulk-reject',  [FormulirMeninggalkanKerjaController::class, 'bulkReject'])->name('formulir.meninggalkan-kerja.bulk-reject');
});

// ── Surat Perintah Dinas ─────────────────────────────────────────────────────
Route::get('surat-dinas/export-excel',    [SuratDinasController::class, 'exportExcel'])->middleware('permission:laporan.export')->name('surat-dinas.export-excel');
Route::get('surat-dinas/export-pdf-bulk', [SuratDinasController::class, 'exportPdfBulk'])->middleware('permission:laporan.export')->name('surat-dinas.export-pdf-bulk');
Route::resource('surat-dinas', SuratDinasController::class)
    ->names('surat-dinas')
    ->parameters(['surat-dinas' => 'suratDinas']);
Route::get('surat-dinas/{suratDinas}/pdf',     [SuratDinasController::class, 'pdf'])->name('surat-dinas.pdf');
Route::post('surat-dinas/{suratDinas}/approve',[SuratDinasController::class, 'approve'])->name('surat-dinas.approve');
Route::post('surat-dinas/{suratDinas}/tolak',  [SuratDinasController::class, 'tolak'])->name('surat-dinas.tolak');

Route::get('/python-status', function () {
    try {
        $res = \Illuminate\Support\Facades\Http::timeout(3)->get(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/health');
        return response()->json(['online' => true, 'model' => $res->json()['model'] ?? 'AI']);
    } catch (\Exception $e) {
        return response()->json(['online' => false]);
    }
})->name('python-status');
