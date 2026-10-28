<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Devision;
use App\Models\Position;
use Illuminate\Http\Request;

class OrganisasiHubController extends Controller
{
    public function index()
    {
        $u = auth()->user();

        $totalDepartment = $departmentTerbaru = null;
        if ($u->hasPermission('departemen.kelola')) {
            $totalDepartment = Department::count();
            $departmentTerbaru = Department::withCount('devisions')->latest()->limit(4)->get();
        }

        $totalDevision = $devisionTerbaru = null;
        if ($u->hasPermission('divisi.kelola')) {
            $totalDevision = Devision::count();
            $devisionTerbaru = Devision::with('department')->latest()->limit(4)->get();
        }

        $totalPosition = $positionTerbaru = null;
        if ($u->hasPermission('jabatan.kelola')) {
            $totalPosition = Position::count();
            $positionTerbaru = Position::latest()->limit(4)->get();
        }

        return view('admin.organisasi.index', compact(
            'totalDepartment', 'totalDevision', 'totalPosition',
            'departmentTerbaru', 'devisionTerbaru', 'positionTerbaru'
        ));
    }
}
