<?php

namespace App\Http\Controllers\Admin\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Devision;
use Illuminate\Http\Request;

class DevisionController extends Controller
{
    public function index()
    {
        $devisions   = Devision::with('department')->latest()->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        return view('admin.devision.index', compact('devisions', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        $devision = Devision::create($request->only('name', 'department_id'));
        AuditLog::catat('create', "Tambah divisi: {$devision->name}", $devision);

        return redirect()->route('admin.devision.index')
                         ->with('success', 'Divisi berhasil ditambahkan!');
    }

    public function update(Request $request, Devision $devision)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        $devision->update($request->only('name', 'department_id'));
        AuditLog::catat('update', "Update divisi: {$devision->name}", $devision);

        return redirect()->route('admin.devision.index')
                         ->with('success', 'Divisi berhasil diupdate!');
    }

    public function destroy(Devision $devision)
    {
        AuditLog::catat('delete', "Hapus divisi: {$devision->name}", $devision);
        $devision->delete();

        return redirect()->route('admin.devision.index')
                         ->with('success', 'Divisi berhasil dihapus!');
    }
}
