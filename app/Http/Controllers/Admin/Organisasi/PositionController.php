<?php

namespace App\Http\Controllers\Admin\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $query  = Position::query();
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('level', 'like', "%{$search}%")
                  ->orWhere('salary_grade', 'like', "%{$search}%");
        }
        $positions = $query->latest()->paginate(15)->withQueryString();
        return view('admin.position.index', compact('positions', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:positions,name',
            'level'        => 'nullable|string|max:50',
            'salary_grade' => 'nullable|string|max:50',
        ]);

        $position = Position::create($request->only('name', 'level', 'salary_grade'));
        AuditLog::catat('create', "Tambah jabatan: {$position->name}", $position);

        return redirect()->route('admin.position.index')
                         ->with('success', 'Jabatan berhasil ditambahkan!');
    }

    public function update(Request $request, Position $position)
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:positions,name,' . $position->id,
            'level'        => 'nullable|string|max:50',
            'salary_grade' => 'nullable|string|max:50',
        ]);

        $position->update($request->only('name', 'level', 'salary_grade'));
        AuditLog::catat('update', "Update jabatan: {$position->name}", $position);

        return redirect()->route('admin.position.index')
                         ->with('success', 'Jabatan berhasil diupdate!');
    }

    public function destroy(Position $position)
    {
        AuditLog::catat('delete', "Hapus jabatan: {$position->name}", $position);
        $position->delete();

        return redirect()->route('admin.position.index')
                         ->with('success', 'Jabatan berhasil dihapus!');
    }
}
