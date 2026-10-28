<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifikasi = $user->notifications()->latest()->paginate(30)->withQueryString();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return view('admin.notifikasi.index', compact('notifikasi'));
    }

    public function fetch()
    {
        $user = Auth::user();
        $items = $user->notifications()->latest()->take(15)->get()->map(function ($n) {
            return [
                'id'      => $n->id,
                'data'    => $n->data,
                'unread'  => is_null($n->read_at),
                'time'    => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $items,
        ]);
    }

    public function markRead(Request $request)
    {
        $user = Auth::user();
        if ($request->id) {
            $user->notifications()->where('id', $request->id)->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }
        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($request->hapus_semua) {
            $user->notifications()->delete();
            return redirect()->route('admin.notifikasi.index')->with('success', 'Semua notifikasi berhasil dihapus.');
        }

        $user->notifications()->where('id', $request->id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back();
    }
}
