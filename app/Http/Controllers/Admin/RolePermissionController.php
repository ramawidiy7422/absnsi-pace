<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    // Daftar role yang bisa dikelola (superadmin tidak diatur via UI)
    private array $roles = [
        'admin'      => 'Admin',
        'hrd'        => 'HRD',
        'supervisor' => 'Supervisor',
        'operator'   => 'Operator',
        'karyawan'   => 'Karyawan',
    ];

    public function index()
    {
        $roles       = $this->roles;
        $permissions = Permission::orderBy('kategori')->orderBy('name')->get()->groupBy('kategori');
        $allPermDefs = Permission::allPermissions();

        // Ambil permission per role
        $rolePerms = [];
        foreach (array_keys($roles) as $role) {
            $rolePerms[$role] = DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role', $role)
                ->pluck('permissions.name')
                ->toArray();
        }

        // Jumlah user per role
        $userCount = User::whereIn('role', array_keys($roles))
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        // User yang punya custom permission (untuk badge notif)
        $usersWithOverride = DB::table('user_permissions')
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        // Semua user non-superadmin untuk dropdown per-user
        $allUsers = User::where('role', '!=', 'superadmin')
            ->orderBy('name')
            ->get(['id','name','email','role']);

        return view('admin.role-permission.index', compact(
            'roles', 'permissions', 'rolePerms', 'userCount', 'allPermDefs',
            'usersWithOverride', 'allUsers'
        ));
    }

    public function update(Request $request, string $role)
    {
        if (!array_key_exists($role, $this->roles)) {
            abort(404);
        }

        $permIds = $request->input('permissions', []);

        // Validasi permission id valid
        $validIds = Permission::pluck('id')->toArray();
        $permIds  = array_filter($permIds, fn($id) => in_array((int)$id, $validIds));

        DB::table('role_permissions')->where('role', $role)->delete();

        $rows = array_map(fn($id) => ['role' => $role, 'permission_id' => (int)$id], $permIds);
        if ($rows) {
            DB::table('role_permissions')->insert($rows);
        }

        // Hapus cache untuk role ini
        User::clearRolePermissionCache($role);

        AuditLog::catat('update', "Update permission role: {$role} (".count($rows)." permissions)");

        return response()->json(['success' => true, 'message' => "Permission role {$this->roles[$role]} berhasil disimpan."]);
    }

    public function userPermission(User $user)
    {
        $permissions  = Permission::orderBy('kategori')->orderBy('name')->get()->groupBy('kategori');
        $allPermDefs  = Permission::allPermissions();
        $rolePerms    = DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.role', $user->role)
            ->pluck('permissions.name')
            ->toArray();

        $overrides = UserPermission::where('user_id', $user->id)
            ->with('permission')
            ->get()
            ->groupBy('mode')
            ->map(fn($g) => $g->pluck('permission.name')->toArray());

        $granted = $overrides->get('grant', []);
        $denied  = $overrides->get('deny', []);

        return response()->json([
            'user'       => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            'rolePerms'  => $rolePerms,
            'granted'    => $granted,
            'denied'     => $denied,
            'permissions'=> Permission::orderBy('kategori')->orderBy('name')->get(['id','name','label','kategori']),
        ]);
    }

    public function userPermissionUpdate(Request $request, User $user)
    {
        if ($user->role === 'superadmin') {
            return response()->json(['success' => false, 'message' => 'Superadmin tidak dapat diubah.'], 403);
        }

        $granted = $request->input('granted', []);
        $denied  = $request->input('denied', []);

        // Validasi semua id valid
        $validIds = Permission::pluck('id')->toArray();
        $granted  = array_filter(array_map('intval', $granted), fn($id) => in_array($id, $validIds));
        $denied   = array_filter(array_map('intval', $denied),  fn($id) => in_array($id, $validIds));

        // Tidak boleh di-grant dan di-deny sekaligus
        $denied = array_diff($denied, $granted);

        DB::table('user_permissions')->where('user_id', $user->id)->delete();

        $rows = [];
        foreach ($granted as $id) $rows[] = ['user_id' => $user->id, 'permission_id' => $id, 'mode' => 'grant', 'created_at' => now(), 'updated_at' => now()];
        foreach ($denied  as $id) $rows[] = ['user_id' => $user->id, 'permission_id' => $id, 'mode' => 'deny',  'created_at' => now(), 'updated_at' => now()];

        if ($rows) DB::table('user_permissions')->insert($rows);

        User::clearUserPermissionCache($user->id);

        AuditLog::catat('update', "Update permission user: {$user->name} (grant:".count($granted).", deny:".count($denied).")");

        return response()->json(['success' => true, 'message' => "Permission {$user->name} berhasil disimpan."]);
    }
}
