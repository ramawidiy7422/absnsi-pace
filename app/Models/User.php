<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'karyawan_id',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Ambil permission dari role (di-cache per role, 5 menit)
     */
    public function getRolePermissions(): array
    {
        if ($this->role === 'superadmin') return ['*'];

        return Cache::remember("role_permissions_{$this->role}", 300, function () {
            return \DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role', $this->role)
                ->pluck('permissions.name')
                ->toArray();
        });
    }

    /**
     * Ambil override per-user: ['grant' => [...], 'deny' => [...]]
     */
    public function getUserOverrides(): array
    {
        return Cache::remember("user_permissions_{$this->id}", 300, function () {
            $rows = \DB::table('user_permissions')
                ->join('permissions', 'permissions.id', '=', 'user_permissions.permission_id')
                ->where('user_permissions.user_id', $this->id)
                ->select('permissions.name', 'user_permissions.mode')
                ->get();

            $grant = $rows->where('mode', 'grant')->pluck('name')->toArray();
            $deny  = $rows->where('mode', 'deny')->pluck('name')->toArray();
            return compact('grant', 'deny');
        });
    }

    /**
     * Cek apakah user punya permission tertentu.
     * Urutan: superadmin → user deny → user grant → role
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'superadmin') return true;

        $overrides = $this->getUserOverrides();

        // Jika di-deny secara eksplisit untuk user ini → tolak
        if (in_array($permission, $overrides['deny'])) return false;

        // Jika di-grant secara eksplisit untuk user ini → izinkan
        if (in_array($permission, $overrides['grant'])) return true;

        // Fallback ke role permission
        $rolePerms = $this->getRolePermissions();
        return in_array('*', $rolePerms) || in_array($permission, $rolePerms);
    }

    /**
     * Hapus cache role permission (dipanggil saat update role permission)
     */
    public static function clearRolePermissionCache(string $role): void
    {
        Cache::forget("role_permissions_{$role}");
    }

    /**
     * Hapus cache permission user tertentu (dipanggil saat update user permission)
     */
    public static function clearUserPermissionCache(int $userId): void
    {
        Cache::forget("user_permissions_{$userId}");
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
