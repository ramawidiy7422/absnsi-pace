<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FormulirMeninggalkanKerja;
use App\Models\Izin;
use App\Models\Leaverequest;
use Carbon\Carbon;

abstract class Controller
{
    /**
     * Semua department_id yang boleh dilihat/dikelola user yang login:
     * departemen tempat dia sendiri terdaftar sebagai karyawan, DITAMBAH
     * setiap departemen yang menunjuknya sebagai spv_user_id atau
     * manager_user_id (seorang SPV/manajer bisa membawahi >1 departemen,
     * dan belum tentu dia sendiri karyawan di departemen yang dia awasi).
     * admin/hrd/superadmin TIDAK dibatasi — return null (artinya: tanpa filter).
     */
    protected function scopedDepartmentIds(): ?array
    {
        $user = auth()->user();
        if (in_array($user->role, ['superadmin', 'admin', 'hrd'])) {
            return null;
        }

        $ids = Department::where('spv_user_id', $user->id)
            ->orWhere('manager_user_id', $user->id)
            ->pluck('id')
            ->toArray();

        if ($user->karyawan?->department_id) {
            $ids[] = $user->karyawan->department_id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Resolve department_id TUNGGAL yang BOLEH dipakai user untuk query laporan
     * yang hanya mendukung satu filter departemen sekaligus (dropdown tunggal).
     * Role selain admin/hrd/superadmin (mis. supervisor) dipaksa ke salah satu
     * departemen miliknya (yang pertama dari scopedDepartmentIds()), terlepas
     * dari department_id yang dikirim di request — mencegah supervisor mengintip
     * data departemen lain lewat ganti parameter URL. Dipakai di halaman yang
     * secara desain cuma filter 1 departemen; untuk scoping WAJIB (bukan filter
     * opsional) pakai scopedDepartmentIds() supaya dept kedua/ketiga ikut kebawa.
     * Return: department_id yang harus dipakai, atau null (tidak dibatasi/tanpa filter).
     */
    protected function scopedDepartmentId(?int $requested): ?int
    {
        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds === null) {
            return $requested;
        }

        return $deptIds[0] ?? null;
    }

    /**
     * Cek apakah karyawan sudah punya Cuti/Izin/FMK aktif (pending atau disetujui)
     * yang tumpang tindih dengan rentang tanggal ini — LINTAS jenis pengajuan,
     * bukan cuma sesama jenis yang sama. Tanpa ini karyawan bisa punya cuti
     * disetujui tanggal 1-5 tapi tetap lolos ajukan izin sakit di tanggal 3,
     * dan keduanya bisa disetujui terpisah (data ganda di laporan/rekap).
     * Return: deskripsi pengajuan yang bentrok, atau null kalau tidak ada bentrok.
     */
    protected function cekBentrokPengajuan(int $karyawanId, Carbon $start, Carbon $end, ?int $kecualiLeaverequestId = null, ?int $kecualiIzinId = null, ?int $kecualiFmkId = null): ?string
    {
        $cuti = Leaverequest::where('karyawan_id', $karyawanId)
            ->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd', 'disetujui'])
            ->when($kecualiLeaverequestId, fn($q) => $q->where('id', '!=', $kecualiLeaverequestId))
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->first();
        if ($cuti) {
            return "{$cuti->type} ({$cuti->start_date->format('d/m/Y')} – {$cuti->end_date->format('d/m/Y')})";
        }

        $izin = Izin::where('karyawan_id', $karyawanId)
            ->whereIn('status', ['pending', 'disetujui'])
            ->when($kecualiIzinId, fn($q) => $q->where('id', '!=', $kecualiIzinId))
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->first();
        if ($izin) {
            return "{$izin->jenis} ({$izin->tanggal->format('d/m/Y')})";
        }

        // FMK cuma 1 hari (tanggal tunggal), cek overlap kalau tanggalnya ada di rentang.
        $fmk = FormulirMeninggalkanKerja::where('karyawan_id', $karyawanId)
            ->whereNotIn('status', ['ditolak'])
            ->when($kecualiFmkId, fn($q) => $q->where('id', '!=', $kecualiFmkId))
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->first();
        if ($fmk) {
            return "FMK {$fmk->nomor_fmk} ({$fmk->tanggal->format('d/m/Y')})";
        }

        return null;
    }
}
