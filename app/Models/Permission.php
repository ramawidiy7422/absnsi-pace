<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $fillable = ['name', 'label', 'kategori'];

    // Semua daftar permissions yang tersedia di sistem
    public static function allPermissions(): array
    {
        return [
            'Dashboard' => [
                'dashboard.lihat'    => 'Lihat Dashboard',
            ],
            'Karyawan' => [
                'karyawan.lihat'     => 'Lihat Data Karyawan',
                'karyawan.tambah'    => 'Tambah Karyawan',
                'karyawan.edit'      => 'Edit Karyawan',
                'karyawan.hapus'     => 'Hapus Karyawan',
                'karyawan.import'    => 'Import Karyawan (CSV)',
                'karyawan.buat-akun' => 'Buat Akun untuk Karyawan',
            ],
            'Shift Kerja' => [
                'shift.lihat'        => 'Lihat Shift Kerja',
                'shift.kelola'       => 'Tambah/Edit/Hapus Shift',
                'shift.jadwal'       => 'Atur Jadwal Shift Karyawan',
            ],
            'Absensi' => [
                'absensi.lihat'      => 'Lihat Data Absensi',
                'absensi.tambah'     => 'Input Absensi Manual',
                'absensi.edit'       => 'Edit Absensi',
                'absensi.hapus'      => 'Hapus Absensi',
                'absensi.export'     => 'Export Absensi (PDF/CSV)',
            ],
            'Cuti & Izin' => [
                'cuti.lihat'         => 'Lihat Pengajuan Cuti',
                'cuti.tambah'        => 'Buat Pengajuan Cuti (atas nama karyawan)',
                'cuti.edit'          => 'Edit Pengajuan Cuti',
                'cuti.hapus'         => 'Hapus Pengajuan Cuti',
                'cuti.approve'       => 'Approve/Tolak Cuti',
                'izin.lihat'         => 'Lihat Izin / Sakit',
                'izin.tambah'        => 'Buat Izin / Sakit',
                'izin.hapus'         => 'Hapus Izin / Sakit',
                'izin.approve'       => 'Approve/Tolak Izin',
                'izin-terlambat.lihat'   => 'Lihat Izin Terlambat (via WA)',
                'izin-terlambat.tambah'  => 'Input Izin Terlambat (via WA)',
                'izin-terlambat.hapus'   => 'Hapus Izin Terlambat',
                'izin-terlambat.approve' => 'Approve/Tolak Izin Terlambat',
                'izin-pulang-cepat.lihat'   => 'Lihat Izin Pulang Cepat',
                'izin-pulang-cepat.tambah'  => 'Input Izin Pulang Cepat',
                'izin-pulang-cepat.hapus'   => 'Hapus Izin Pulang Cepat',
                'izin-pulang-cepat.approve' => 'Approve/Tolak Izin Pulang Cepat',
                'lembur.lihat'       => 'Lihat Lembur',
                'lembur.tambah'      => 'Buat Pengajuan Lembur',
                'lembur.edit'        => 'Edit Lembur',
                'lembur.hapus'       => 'Hapus Lembur',
                'lembur.approve'     => 'Approve/Tolak Lembur',
            ],
            'Formulir Meninggalkan Kerja' => [
                'fmk.lihat'    => 'Lihat Formulir Meninggalkan Kerja',
                'fmk.tambah'   => 'Buat FMK (atas nama karyawan)',
                'fmk.edit'     => 'Edit FMK',
                'fmk.hapus'    => 'Hapus FMK',
                'fmk.approve'  => 'Approve/Tolak FMK',
            ],
            'Laporan' => [
                'laporan.lihat'          => 'Lihat Laporan Absensi',
                'laporan.rekap-bulanan'  => 'Lihat Rekap Bulanan',
                'laporan.rekap-dept'     => 'Lihat Rekap Departemen',
                'laporan.rekap-karyawan' => 'Lihat Rekap per Karyawan',
                'laporan.rekap-cuti'     => 'Lihat Rekap Cuti & Izin',
                'laporan.export'         => 'Export Laporan PDF/CSV',
                'laporan.jadwal-shift'   => 'Lihat Jadwal Shift',
            ],
            'Organisasi' => [
                'departemen.kelola'  => 'Kelola Departemen',
                'divisi.kelola'      => 'Kelola Divisi',
                'jabatan.kelola'     => 'Kelola Jabatan',
            ],
            'Kalender' => [
                'hari-libur.lihat'   => 'Lihat Hari Libur',
                'hari-libur.tambah'  => 'Tambah Hari Libur',
                'hari-libur.edit'    => 'Edit Hari Libur',
                'hari-libur.hapus'   => 'Hapus Hari Libur',
                'hari-libur.import'  => 'Import Hari Libur Nasional',
            ],
            'Pengumuman' => [
                'pengumuman.lihat'   => 'Lihat Pengumuman',
                'pengumuman.kelola'  => 'Buat/Edit/Hapus Pengumuman',
            ],
            'Pengaturan Sistem' => [
                'users.lihat'        => 'Lihat Manajemen User',
                'users.kelola'       => 'Tambah/Edit/Hapus User',
                'role.kelola'        => 'Kelola Role & Permission',
                'pengaturan.lihat'   => 'Lihat Pengaturan Sistem',
                'pengaturan.edit'    => 'Edit Pengaturan Sistem',
                'audit-log.lihat'    => 'Lihat Audit Log',
                'login-history.lihat'=> 'Lihat Riwayat Login',
                'reset-password.lihat'   => 'Lihat Permintaan Reset Password',
                'reset-password.approve' => 'Approve/Tolak Permintaan Reset Password',
            ],
            'Kiosk' => [
                'kiosk.akses' => 'Akses Menu Absensi Digital di Portal',
            ],
        ];
    }
}
