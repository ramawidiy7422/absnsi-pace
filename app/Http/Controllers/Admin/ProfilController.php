<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        return view('admin.profil');
    }

    public function updateNama(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        Auth::user()->update(['name' => $request->name]);
        return back()->with('success', 'Nama berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:6|confirmed',
        ], [
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_baru.min'       => 'Password baru minimal 6 karakter.',
        ]);

        if (!Hash::check($request->password_lama, Auth::user()->password)) {
            return back()->with('error_pass', 'Password lama tidak sesuai.');
        }

        Auth::user()->update(['password' => Hash::make($request->password_baru)]);
        return back()->with('success_pass', 'Password berhasil diubah.');
    }
}
