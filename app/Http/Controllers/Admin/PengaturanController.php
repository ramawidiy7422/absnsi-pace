<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = Pengaturan::orderBy('group')->orderBy('id')->get()->groupBy('group');
        return view('admin.pengaturan.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_perusahaan'     => 'nullable|string|max:100',
            'alamat_perusahaan'   => 'nullable|string|max:255',
            'kota_perusahaan'     => 'nullable|string|max:100',
            'no_telp_perusahaan'  => 'nullable|string|max:20',
            'email_hr'            => 'nullable|email|max:100',
            'website_perusahaan'  => 'nullable|url|max:100',
            'cutoff_jam_masuk'              => 'nullable|integer|min:1|max:6',
            'toleransi_masuk_awal_menit'    => 'nullable|integer|min:0|max:120',
            'toleransi_pulang_cepat_menit'  => 'nullable|integer|min:0|max:60',
            'jam_generate_alpha'            => 'nullable|date_format:H:i',
            'jam_rekap_harian'              => 'nullable|date_format:H:i',
            'kiosk_cooldown_detik'          => 'nullable|integer|min:2|max:30',
            'wajib_selfie_keluar'           => 'nullable|in:0,1',
            'notif_terlambat_aktif'         => 'nullable|in:0,1',
            'alasan_terlambat_wajib'        => 'nullable|in:0,1',
            'jatah_cuti_tahunan'            => 'nullable|integer|min:0|max:365',
            'max_carry_over_cuti'           => 'nullable|integer|min:0|max:365',
            'tarif_lembur_per_jam'          => 'nullable|integer|min:0',
            'min_menit_keluar'              => 'nullable|integer|min:10|max:240',
            'wajib_izin_pulang_cepat'       => 'nullable|in:0,1',
            'min_lembur_otomatis_menit'     => 'nullable|integer|min:10|max:120',
            'max_jam_lembur_harian'         => 'nullable|integer|min:0|max:8',
            'batas_pengajuan_lembur_jam'    => 'nullable|integer|min:0|max:48',
            'lembur_wajib_spk'              => 'nullable|in:0,1',
            'email_notif_aktif'             => 'nullable|in:0,1',
            'slip_footer'                   => 'nullable|string|max:500',
            'logo_perusahaan'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'favicon'             => 'nullable|file|mimes:png,ico,jpg,jpeg|max:512',
        ], [
            'email_hr.email'                 => 'Format email HRD tidak valid.',
            'website_perusahaan.url'         => 'Format website tidak valid (harus https://...).',
            'logo_perusahaan.image'          => 'File harus berupa gambar.',
            'logo_perusahaan.mimes'          => 'Logo harus format PNG, JPG, atau JPEG.',
            'logo_perusahaan.max'            => 'Ukuran logo maksimal 2MB.',
        ]);

        $allowed = [
            'nama_perusahaan', 'alamat_perusahaan', 'kota_perusahaan', 'no_telp_perusahaan',
            'email_hr', 'website_perusahaan', 'cutoff_jam_masuk',
            'toleransi_masuk_awal_menit', 'toleransi_pulang_cepat_menit', 'jam_generate_alpha', 'jam_rekap_harian',
            'kiosk_cooldown_detik', 'wajib_selfie_keluar', 'notif_terlambat_aktif',
            'alasan_terlambat_wajib', 'jatah_cuti_tahunan', 'max_carry_over_cuti',
            'min_menit_keluar', 'wajib_izin_pulang_cepat', 'tarif_lembur_per_jam', 'min_lembur_otomatis_menit', 'max_jam_lembur_harian',
            'batas_pengajuan_lembur_jam', 'lembur_wajib_spk', 'email_notif_aktif', 'slip_footer',
        ];

        foreach ($allowed as $key) {
            if ($request->has($key)) {
                Pengaturan::set($key, $request->input($key) ?? '');
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo_perusahaan')) {
            $file     = $request->file('logo_perusahaan');
            $filename = 'logo-perusahaan.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            Pengaturan::set('logo_perusahaan', '/images/' . $filename . '?v=' . time());
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $ext  = strtolower($file->getClientOriginalExtension());
            if ($ext === 'ico') {
                $file->move(public_path(), 'favicon.ico');
            } else {
                $tmpPath  = $file->getPathname();
                $destPath = public_path('favicon.ico');
                $cmd = sprintf(
                    'python3 -c %s',
                    escapeshellarg("from PIL import Image; img=Image.open('{$tmpPath}').convert('RGBA'); img.save('{$destPath}', format='ICO', sizes=[(16,16),(32,32),(48,48)])")
                );
                shell_exec($cmd);
            }
        }

        AuditLog::catat('update', 'Update pengaturan sistem');
        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
