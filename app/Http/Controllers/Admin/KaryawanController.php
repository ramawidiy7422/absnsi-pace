<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use chillerlan\QRCode\QRCode as ChillerQR;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::with('shift');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%");
            });
        }
        if ($request->departemen) {
            $query->where('departemen', $request->departemen);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $karyawan   = $query->latest()->paginate(10);
        $departemen = Karyawan::distinct()->pluck('departemen');

        return view('admin.karyawan.index', compact('karyawan', 'departemen'));
    }

    public function create()
    {
        $shifts = Shift::where('status', 'aktif')->get();
        return view('admin.karyawan.create', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'           => 'required|unique:karyawan,nik|max:20',
            'nama'          => 'required|string|max:100',
            'departemen'    => 'required|string|max:50',
            'jabatan'       => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_masuk' => 'required|date',
            'shift_id'      => 'nullable|exists:shifts,id',
            'foto_wajah'    => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['foto_wajah', 'foto_webcam']);

        $fotoB64 = null;

        // Prioritas: webcam capture > file upload
        if ($request->filled('foto_webcam')) {
            $b64 = $request->input('foto_webcam');
            // Simpan ke storage
            $imgData = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
            $imgBin  = base64_decode($imgData);
            $filename = 'wajah/' . uniqid('wajah_') . '.jpg';
            Storage::disk('public')->put($filename, $imgBin);
            $data['foto_wajah'] = $filename;
            $fotoB64 = $b64;
        } elseif ($request->hasFile('foto_wajah')) {
            $path = $request->file('foto_wajah')->store('wajah', 'public');
            $data['foto_wajah'] = $path;
            $fotoB64 = 'data:image/jpeg;base64,' . base64_encode(
                file_get_contents($request->file('foto_wajah')->getRealPath())
            );
        }

        if ($fotoB64) {
            try {
                $res = Http::timeout(30)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/daftarkan', [
                    'nik'  => $request->nik,
                    'foto' => $fotoB64,
                ]);
                $resData = $res->json();
                if (isset($resData['success']) && !$resData['success']) {
                    return back()->withInput()
                                 ->with('error', 'Foto gagal didaftarkan: ' . ($resData['message'] ?? 'Error'));
                }
            } catch (\Exception $e) {
                // Service Python belum aktif, data tetap disimpan
            }
        }

        Karyawan::create($data);

        return redirect()->route('admin.karyawan.index')
                         ->with('success', 'Karyawan berhasil ditambahkan!');
    }

    public function show(Karyawan $karyawan)
    {
        $absensiTerbaru = $karyawan->absensi()->latest()->take(30)->get();
        return view('admin.karyawan.show', compact('karyawan', 'absensiTerbaru'));
    }

    public function edit(Karyawan $karyawan)
    {
        $shifts = Shift::where('status', 'aktif')->get();
        return view('admin.karyawan.edit', compact('karyawan', 'shifts'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nik'           => 'required|max:20|unique:karyawan,nik,' . $karyawan->id,
            'nama'          => 'required|string|max:100',
            'departemen'    => 'required|string|max:50',
            'jabatan'       => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_masuk' => 'required|date',
            'shift_id'      => 'nullable|exists:shifts,id',
            'foto_wajah'    => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['foto_wajah', 'foto_webcam']);

        $fotoB64 = null;

        // Prioritas: webcam capture > file upload
        if ($request->filled('foto_webcam')) {
            if ($karyawan->foto_wajah) {
                Storage::disk('public')->delete($karyawan->foto_wajah);
            }
            $b64 = $request->input('foto_webcam');
            $imgData = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
            $imgBin  = base64_decode($imgData);
            $filename = 'wajah/' . uniqid('wajah_') . '.jpg';
            Storage::disk('public')->put($filename, $imgBin);
            $data['foto_wajah'] = $filename;
            $fotoB64 = $b64;
        } elseif ($request->hasFile('foto_wajah')) {
            if ($karyawan->foto_wajah) {
                Storage::disk('public')->delete($karyawan->foto_wajah);
            }
            $path = $request->file('foto_wajah')->store('wajah', 'public');
            $data['foto_wajah'] = $path;
            $fotoB64 = 'data:image/jpeg;base64,' . base64_encode(
                file_get_contents($request->file('foto_wajah')->getRealPath())
            );
        }

        if ($fotoB64) {
            try {
                $res = Http::timeout(30)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/daftarkan', [
                    'nik'  => $request->nik,
                    'foto' => $fotoB64,
                ]);
                $resData = $res->json();
                if (isset($resData['success']) && !$resData['success']) {
                    return back()->withInput()
                                 ->with('error', 'Foto gagal didaftarkan: ' . ($resData['message'] ?? 'Error'));
                }
            } catch (\Exception $e) {
                // Service Python belum aktif
            }
        }

        $karyawan->update($data);

        return redirect()->route('admin.karyawan.index')
                         ->with('success', 'Data karyawan berhasil diupdate!');
    }

    // Helper: generate QR PNG base64 (bisa dibaca kamera/jsQR)
    private function makeQrPng(string $data, int $scale = 8): string
    {
        $opt = new QROptions;
        $opt->outputType  = QRGdImagePNG::class;
        $opt->scale       = $scale;
        $opt->imageTransparent = false;
        return (new ChillerQR($opt))->render($data);
        // Returns "data:image/png;base64,..." string
    }

    public function generateQr(Karyawan $karyawan)
    {
        // Return PNG image (bukan SVG) agar bisa dibaca jsQR di kiosk
        $opt = new QROptions;
        $opt->outputType       = QRGdImagePNG::class;
        $opt->scale            = 10;
        $opt->imageTransparent = false;
        $pngData = (new ChillerQR($opt))->render($karyawan->nik);

        // pngData = "data:image/png;base64,..." — ambil binary-nya
        $base64 = str_replace('data:image/png;base64,', '', $pngData);
        $binary = base64_decode($base64);

        return response($binary, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="qr-' . $karyawan->nik . '.png"');
    }

    public function printQr(Karyawan $karyawan)
    {
        $qrPng = $this->makeQrPng($karyawan->nik, 8);
        return view('admin.karyawan.print-qr', compact('karyawan', 'qrPng'));
    }

    public function printQrSemua()
    {
        $karyawanList = Karyawan::where('status', 'aktif')->orderBy('nama')->get();

        $qrList = $karyawanList->map(function ($k) {
            return [
                'karyawan' => $k,
                'qrPng'    => $this->makeQrPng($k->nik, 7),
            ];
        });

        return view('admin.karyawan.print-qr-semua', compact('qrList'));
    }

    public function destroy(Karyawan $karyawan)
    {
        if ($karyawan->foto_wajah) {
            Storage::disk('public')->delete($karyawan->foto_wajah);
        }

        try {
            Http::timeout(5)->delete(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/hapus/' . $karyawan->nik);
        } catch (\Exception $e) {
            // Service Python belum aktif
        }

        $karyawan->delete();

        return redirect()->route('admin.karyawan.index')
                         ->with('success', 'Karyawan berhasil dihapus!');
    }
}
