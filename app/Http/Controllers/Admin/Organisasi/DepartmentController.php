<?php

namespace App\Http\Controllers\Admin\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class DepartmentController extends Controller
{
    /** Role yang boleh dijadikan SPV/Manajer approval berjenjang */
    private const ROLE_APPROVER = ['supervisor', 'admin', 'hrd', 'superadmin'];

    public function index(Request $request)
    {
        $search = $request->search;
        $query  = Department::withCount('devisions')->with('spvUser', 'managerUser');
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        $departments   = $query->latest()->paginate(15)->withQueryString();
        $calonApprover = User::whereIn('role', self::ROLE_APPROVER)->orderBy('name')->get();
        return view('admin.department.index', compact('departments', 'search', 'calonApprover'));
    }

    private function approverRule(): Exists
    {
        return Rule::exists('users', 'id')->whereIn('role', self::ROLE_APPROVER);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:departments,name',
            'description'     => 'nullable|string|max:255',
            'spv_user_id'     => ['nullable', $this->approverRule()],
            'manager_user_id' => ['nullable', $this->approverRule()],
        ]);

        Department::create($request->only('name', 'description', 'spv_user_id', 'manager_user_id'));

        return redirect()->route('admin.department.index')
                         ->with('success', 'Departemen berhasil ditambahkan!');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:departments,name,' . $department->id,
            'description'     => 'nullable|string|max:255',
            'spv_user_id'     => ['nullable', $this->approverRule()],
            'manager_user_id' => ['nullable', $this->approverRule()],
        ]);

        $department->update($request->only('name', 'description', 'spv_user_id', 'manager_user_id'));

        return redirect()->route('admin.department.index')
                         ->with('success', 'Departemen berhasil diupdate!');
    }

    public function destroy(Department $department)
    {
        if ($department->devisions()->exists()) {
            return redirect()->route('admin.department.index')
                             ->with('error', 'Departemen tidak dapat dihapus karena masih memiliki divisi!');
        }

        if (Karyawan::where('department_id', $department->id)->exists()) {
            return redirect()->route('admin.department.index')
                             ->with('error', 'Departemen tidak dapat dihapus karena masih digunakan oleh karyawan!');
        }

        $department->delete();

        return redirect()->route('admin.department.index')
                         ->with('success', 'Departemen berhasil dihapus!');
    }
}
