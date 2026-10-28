<?php
namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Insert/sync semua permissions dari definisi model
        foreach (Permission::allPermissions() as $kategori => $perms) {
            foreach ($perms as $name => $label) {
                Permission::firstOrCreate(
                    ['name' => $name],
                    ['label' => $label, 'kategori' => $kategori]
                );
            }
        }

        // Default permissions per role
        $defaults = [
            'admin' => [
                'dashboard.lihat',
                // Karyawan
                'karyawan.lihat','karyawan.tambah','karyawan.edit','karyawan.hapus',
                'karyawan.import','karyawan.buat-akun',
                // Shift
                'shift.lihat','shift.kelola','shift.jadwal',
                // Absensi
                'absensi.lihat','absensi.tambah','absensi.edit','absensi.hapus','absensi.export',
                // Cuti & Izin
                'cuti.lihat','cuti.tambah','cuti.edit','cuti.hapus','cuti.approve',
                'izin.lihat','izin.tambah','izin.hapus','izin.approve',
                'izin-terlambat.lihat','izin-terlambat.tambah','izin-terlambat.hapus','izin-terlambat.approve',
                'izin-pulang-cepat.lihat','izin-pulang-cepat.tambah','izin-pulang-cepat.hapus','izin-pulang-cepat.approve',
                'lembur.lihat','lembur.tambah','lembur.edit','lembur.hapus','lembur.approve',
                'fmk.lihat','fmk.tambah','fmk.edit','fmk.hapus','fmk.approve',
                // Laporan
                'laporan.lihat','laporan.rekap-bulanan','laporan.rekap-dept',
                'laporan.rekap-karyawan','laporan.rekap-cuti','laporan.export','laporan.jadwal-shift',
                // Organisasi
                'departemen.kelola','divisi.kelola','jabatan.kelola',
                // Kalender
                'hari-libur.lihat','hari-libur.tambah','hari-libur.edit','hari-libur.hapus','hari-libur.import',
                // Pengumuman
                'pengumuman.lihat','pengumuman.kelola',
                // Sistem
                'users.lihat','users.kelola',
                'pengaturan.lihat','pengaturan.edit',
                'audit-log.lihat','login-history.lihat',
            ],
            'hrd' => [
                'dashboard.lihat',
                // Karyawan
                'karyawan.lihat','karyawan.tambah','karyawan.edit','karyawan.import',
                // Shift
                'shift.lihat','shift.jadwal',
                // Absensi
                'absensi.lihat','absensi.tambah','absensi.edit','absensi.export',
                // Cuti & Izin
                'cuti.lihat','cuti.tambah','cuti.edit','cuti.hapus','cuti.approve',
                'izin.lihat','izin.tambah','izin.hapus','izin.approve',
                'izin-terlambat.lihat','izin-terlambat.tambah','izin-terlambat.hapus','izin-terlambat.approve',
                'izin-pulang-cepat.lihat','izin-pulang-cepat.tambah','izin-pulang-cepat.hapus','izin-pulang-cepat.approve',
                'lembur.lihat','lembur.tambah','lembur.edit','lembur.hapus','lembur.approve',
                'fmk.lihat','fmk.tambah','fmk.edit','fmk.hapus','fmk.approve',
                // Laporan
                'laporan.lihat','laporan.rekap-bulanan','laporan.rekap-dept',
                'laporan.rekap-karyawan','laporan.rekap-cuti','laporan.export','laporan.jadwal-shift',
                // Organisasi
                'departemen.kelola',
                // Kalender
                'hari-libur.lihat','hari-libur.tambah','hari-libur.edit','hari-libur.hapus','hari-libur.import',
                // Pengumuman
                'pengumuman.lihat','pengumuman.kelola',
                // Reset Password
                'reset-password.lihat','reset-password.approve',
            ],
            'supervisor' => [
                'dashboard.lihat',
                'karyawan.lihat',
                'shift.lihat',
                'absensi.lihat','absensi.export',
                'cuti.lihat','cuti.approve',
                'izin.lihat','izin.approve',
                'izin-terlambat.lihat','izin-terlambat.approve',
                'izin-pulang-cepat.lihat','izin-pulang-cepat.approve',
                'lembur.lihat','lembur.approve',
                'fmk.lihat','fmk.approve',
                'laporan.lihat','laporan.rekap-bulanan','laporan.rekap-dept',
                'laporan.rekap-karyawan','laporan.rekap-cuti','laporan.jadwal-shift',
                'hari-libur.lihat',
                'pengumuman.lihat',
            ],
            'operator' => [
                'dashboard.lihat',
                'karyawan.lihat',
                'absensi.lihat','absensi.tambah',
                'pengumuman.lihat',
            ],
            'karyawan' => [
                'dashboard.lihat',
                'pengumuman.lihat',
            ],
        ];

        $permMap = Permission::pluck('id', 'name')->toArray();

        foreach ($defaults as $role => $perms) {
            DB::table('role_permissions')->where('role', $role)->delete();

            $rows = [];
            foreach ($perms as $perm) {
                if (isset($permMap[$perm])) {
                    $rows[] = ['role' => $role, 'permission_id' => $permMap[$perm]];
                }
            }
            if ($rows) {
                DB::table('role_permissions')->insert($rows);
            }
        }

        $this->command->info('Permissions seeded & default role permissions assigned.');
    }
}
