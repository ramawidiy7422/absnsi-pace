<?php
namespace App\Helpers;

use App\Models\Pengaturan;
use App\Models\User;

class NotifHelper
{
    /**
     * Apakah email notification aktif di pengaturan sistem
     */
    public static function emailAktif(): bool
    {
        return (bool) Pengaturan::get('email_notif_aktif', '0');
    }

    /**
     * Kirim notifikasi ke karyawan berdasarkan karyawan_id.
     * Database notification selalu dikirim, email hanya jika email_notif_aktif = true.
     */
    public static function keKaryawan(int $karyawanId, $notification): void
    {
        $user = User::where('karyawan_id', $karyawanId)->first();
        if (!$user) return;

        // Selalu kirim database notification
        try { $user->notify($notification); } catch (\Exception $e) {}
    }

    /**
     * Kirim notifikasi ke HRD/Admin.
     * Database notification selalu dikirim.
     */
    public static function keHrd($notification): void
    {
        $emailHr = Pengaturan::get('email_hr');
        if ($emailHr) {
            $user = User::where('email', $emailHr)->first();
            if ($user) {
                try { $user->notify($notification); } catch (\Exception $e) {}
                return;
            }
        }
        User::whereIn('role', ['hrd','admin','superadmin'])->get()->each(function($u) use ($notification) {
            try { $u->notify(clone $notification); } catch (\Exception $e) {}
        });
    }

    /**
     * Kirim notifikasi ke satu user tertentu. Aman dipanggil dengan user null (no-op).
     */
    public static function keUser(?User $user, $notification): void
    {
        if (!$user) return;
        try { $user->notify($notification); } catch (\Exception $e) {}
    }

    /**
     * Kirim notifikasi ke beberapa user sekaligus (mis. semua SPV departemen
     * yang bisa lebih dari satu orang). Aman dipanggil dengan collection
     * kosong/null (no-op).
     */
    public static function keUsers(?iterable $users, $notification): void
    {
        if (!$users) return;
        foreach ($users as $user) {
            if (!$user) continue;
            try { $user->notify(clone $notification); } catch (\Exception $e) {}
        }
    }
}
