<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;

class KaryawanTestingSeeder extends Seeder
{
    public function run(): void
    {
        $fotoWajah = 'wajah/wajah_69c6216540250.jpg'; // Foto wajah Rama

        $departemen = ['Produksi', 'QC', 'QA', 'Gudang', 'Teknik', 'HRD', 'Keuangan', 'Logistik', 'PPIC', 'R&D'];
        $jabatan    = ['Operator', 'Supervisor', 'Staff', 'Analis', 'Teknisi', 'Admin', 'Leader', 'Koordinator'];
        $statusList = ['kontrak', 'tetap', 'magang', 'training'];
        $shiftIds   = [1, 2, 3, 4];

        $namaDepan = [
            'Agus','Budi','Citra','Dewi','Eko','Fitri','Galih','Heni','Irwan','Joko',
            'Kartika','Laila','Mulyadi','Nisa','Oki','Putri','Qori','Rini','Sari','Tono',
            'Usman','Vina','Wahyu','Xena','Yanto','Zahra','Andi','Bagas','Clara','Dian',
            'Erwin','Fauzi','Gita','Haris','Indah','Johan','Kurnia','Lina','Maya','Nanda',
            'Oscar','Prima','Rani','Sigit','Tari','Utari','Vira','Wendi','Yuli','Zaki',
            'Ahmad','Bella','Cahyo','Dita','Evan','Fanny','Guntur','Hana','Ivan','Jeni',
            'Kevin','Leni','Mia','Nova','Rian','Sandi','Tina','Udin','Veni','Wati',
            'Yoga','Zara','Anton','Bunga','Candra','Deni','Elsa','Feri','Gilang','Hilda',
        ];

        $namaBelakang = [
            'Santoso','Wijaya','Kusuma','Pratama','Utama','Setiawan','Rahayu','Hidayat',
            'Nugroho','Purnama','Wibowo','Saputra','Kurniawan','Lestari','Wahyudi',
            'Prabowo','Susanto','Hartono','Siregar','Nasution','Halim','Salim','Gunawan',
            'Suryadi','Handoko','Mahendra','Sudirman','Permana','Firmansyah','Mulyono',
        ];

        $existing = Karyawan::where('nik', 'like', 'TST%')->pluck('nik')->toArray();
        $count = 0;
        $i = 1;

        while ($count < 299 && $i <= 400) {
            $nik = 'TST' . str_pad($i, 5, '0', STR_PAD_LEFT);
            if (in_array($nik, $existing)) { $i++; continue; }

            $nama = $namaDepan[array_rand($namaDepan)] . ' ' . $namaBelakang[array_rand($namaBelakang)];
            $dept = $departemen[($i - 1) % count($departemen)];
            $jbt  = $jabatan[($i - 1) % count($jabatan)];
            $shft = $shiftIds[($i - 1) % count($shiftIds)];
            $sts  = $statusList[($i - 1) % count($statusList)];

            // Variasi tanggal masuk (beberapa < 1 tahun, sebagian > 1 tahun)
            if ($i % 5 === 0) {
                $tglMasuk = now()->subMonths(rand(3, 11))->format('Y-m-d'); // < 1 tahun
            } else {
                $tglMasuk = now()->subMonths(rand(12, 60))->format('Y-m-d'); // > 1 tahun
            }

            $tglKontrak = ($sts === 'kontrak')
                ? now()->addMonths(rand(1, 12))->format('Y-m-d')
                : null;

            Karyawan::create([
                'nik'                     => $nik,
                'nama'                    => strtoupper($nama),
                'departemen'              => $dept,
                'jabatan'                 => $jbt,
                'jenis_kelamin'           => ($i % 3 === 0) ? 'P' : 'L',
                'status'                  => $sts,
                'shift_id'                => $shft,
                'foto_wajah'              => $fotoWajah, // semua pakai foto Rama
                'tanggal_masuk'           => $tglMasuk,
                'tanggal_kontrak_berakhir'=> $tglKontrak,
                'alamat'                  => 'Semarang, Jawa Tengah',
                'no_hp'                   => '08' . rand(100000000, 999999999),
            ]);

            $count++;
            $i++;

            if ($count % 50 === 0) {
                echo "Dibuat: {$count}/299\n";
            }
        }

        echo "Selesai! Total karyawan testing dibuat: {$count}\n";
    }
}
