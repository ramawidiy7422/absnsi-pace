<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Devision;
use App\Models\Karyawan;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\CutiSaldo;
use App\Models\Legaldoc;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Exports\KaryawanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode as ChillerQR;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;

class KaryawanController extends Controller
{
    // Urutan hierarki jabatan: index kecil = lebih tinggi
    private const JABATAN_ORDER = [
        'Direktur', 'Plant Manager', 'Manager', 'Asst. Manager', 'Supervisor',
        'Apoteker', 'Analis', 'AI INTERAKTIF',
        'Admin', 'HRD Staff', 'IT Staff', 'Staff', 'Staff Accounting', 'Med Rep',
        'Senior Operator', 'Teknisi', 'Operator', 'Pelaksana',
        'Satpam / Security', 'Sopir / Driver', 'Kebersihan / CS',
    ];

    private function jabatanOrderSql(string $col = 'jabatan'): string
    {
        $cases = implode(',', array_map(
            fn($j) => '"' . addslashes($j) . '"',
            self::JABATAN_ORDER
        ));
        return "FIELD({$col}, {$cases}) = 0, FIELD({$col}, {$cases})";
    }

    // Hanya superadmin boleh membuat akun superadmin/admin — cegah HRD/role lain
    // yang punya permission karyawan.tambah/buat-akun melakukan privilege escalation.
    private function assertRoleAssignable(string $role): void
    {
        if (in_array($role, ['superadmin', 'admin']) && auth()->user()->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat menetapkan role ini.');
        }
    }
    public function index(Request $request)
    {
        $today = today()->toDateString();
        $query = Karyawan::with([
            'shift', 'department', 'devision', 'position',
            'employeshifts' => fn($q) => $q->with('shift')->whereDate('date', $today),
        ]);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%")
                  ->orWhere('kode_karyawan', 'like', "%{$request->search}%")
                  ->orWhere('jabatan', 'like', "%{$request->search}%")
                  ->orWhere('departemen', 'like', "%{$request->search}%")
                  ->orWhere('alasan_keluar', 'like', "%{$request->search}%")
                  ->orWhereHas('department', fn($dq) => $dq->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('position',   fn($pq) => $pq->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('devision',   fn($dvq) => $dvq->where('name', 'like', "%{$request->search}%"));
            });
        }
        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null) {
            $query->whereIn('department_id', $deptIds);
        } elseif ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->position_id) {
            $query->where('position_id', $request->position_id);
        }
        if ($request->jenis_kelamin) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }
        if ($request->status === 'aktif') {
            $query->whereNotIn('status', ['resign', 'phk', 'nonaktif']);
        } elseif ($request->status === 'nonaktif') {
            $query->whereIn('status', ['resign', 'phk', 'nonaktif']);
        } elseif ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        } elseif (!$request->hasAny(['status', 'search', 'department_id', 'position_id', 'jenis_kelamin'])) {
            // Default pertama kali buka: sembunyikan resign/phk/nonaktif
            $query->whereNotIn('status', ['resign', 'phk', 'nonaktif']);
        }

        $karyawan = $query
            ->leftJoin('departments as d_sort', 'd_sort.id', '=', 'karyawan.department_id')
            ->orderBy('d_sort.name')
            ->orderByRaw($this->jabatanOrderSql())
            ->orderBy('karyawan.nama')
            ->select('karyawan.*')
            ->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $positions   = Position::orderBy('name')->get();

        $statusCount = Karyawan::selectRaw("
            COUNT(*) as total,
            SUM(status='tetap') as tetap,
            SUM(status='kontrak') as kontrak,
            SUM(status='magang') as magang,
            SUM(status='training') as training,
            SUM(status='outsourcing') as outsourcing,
            SUM(status='harian') as harian,
            SUM(status IN ('nonaktif','resign','phk')) as nonaktif
        ")->first();

        $kontrakHampirHabis = Karyawan::where('status', 'kontrak')
            ->whereNotNull('tanggal_kontrak_berakhir')
            ->whereDate('tanggal_kontrak_berakhir', '>=', $today)
            ->whereDate('tanggal_kontrak_berakhir', '<=', now()->addDays(30))
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->orderBy('tanggal_kontrak_berakhir')->get();

        $belumAbsenHariIni = Karyawan::whereIn('status', ['kontrak', 'tetap', 'magang', 'training'])
            ->whereDoesntHave('absensi', fn($q) => $q->whereDate('tanggal', $today))
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->count();

        return view('admin.karyawan.index', compact(
            'karyawan', 'departments', 'positions', 'statusCount',
            'kontrakHampirHabis', 'belumAbsenHariIni'
        ));
    }

    public function exportCsv(Request $request)
    {
        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null && empty($deptIds)) {
            abort(403);
        }

        return Excel::download(
            new KaryawanExport(
                $deptIds !== null ? $deptIds : ($request->department_id ? (int)$request->department_id : null),
                $request->status,
                $request->search
            ),
            'karyawan-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null && empty($deptIds)) {
            abort(403);
        }

        $query = Karyawan::with(['department', 'position', 'shift'])
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds))
            ->when($deptIds === null && $request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function($s) use ($request) {
                $s->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%");
            }))
            ->leftJoin('departments as d_sort', 'd_sort.id', '=', 'karyawan.department_id')
            ->orderBy('d_sort.name')
            ->orderByRaw($this->jabatanOrderSql())
            ->orderBy('karyawan.nama')
            ->select('karyawan.*')
            ->get();

        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $labels = [];
        if ($request->department_id) {
            $dept = \App\Models\Department::find($request->department_id);
            if ($dept) $labels[] = 'Dept: ' . $dept->name;
        }
        if ($request->status)  $labels[] = 'Status: ' . ucfirst($request->status);
        if ($request->search)  $labels[] = 'Cari: ' . $request->search;
        $filterLabel = $labels ? implode(' | ', $labels) : '';

        ini_set('memory_limit', '256M');
        $pdf = Pdf::loadView('admin.karyawan.export-pdf', compact(
            'query', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan', 'filterLabel'
        ))->setPaper('a4', 'landscape')->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        return $pdf->download('daftar-karyawan-' . now()->format('Ymd_His') . '.pdf');
    }

    public function create()
    {
        $shifts      = Shift::where('status', 'aktif')->get();
        $departments = Department::orderBy('name')->get();
        $devisions   = Devision::with('department')->orderBy('name')->get();
        $positions   = Position::orderBy('name')->get();
        return view('admin.karyawan.create', compact('shifts', 'departments', 'devisions', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_karyawan'       => 'nullable|unique:karyawan,kode_karyawan|max:20',
            'tanggal_masuk'       => 'required|date',
            'inisial'             => 'nullable|string|max:10',
            'nama'                => 'required|string|max:100',
            'department_id'       => 'required|exists:departments,id',
            'devision_id'         => 'nullable|exists:devisions,id',
            'position_id'         => 'required|exists:positions,id',
            'jenis_kelamin'       => 'required|in:L,P',
            'email'               => 'nullable|email|max:100',
            'pendidikan_terakhir' => 'nullable|in:SD,SMP,SMA/SMK,D1,D2,D3,S1,S2,S3',
            'status'                    => 'required|in:kontrak,tetap,magang,training,nonaktif,outsourcing,resign,phk,harian',
            'marital_status'            => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'jumlah_tanggungan'         => 'nullable|integer|min:0|max:20',
            'golongan_darah'            => 'nullable|in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'kontak_darurat_nama'       => 'nullable|string|max:100',
            'kontak_darurat_hubungan'   => 'nullable|string|max:50',
            'kontak_darurat_no_hp'      => 'nullable|string|max:20',
            'bank_nama'                 => 'nullable|string|max:50',
            'bank_no_rekening'          => 'nullable|string|max:30',
            'bank_atas_nama'            => 'nullable|string|max:100',
            'shift_id'                  => 'nullable|exists:shifts,id',
            'bebas_absen'               => 'nullable|boolean',
            'foto_wajah'                => 'nullable|image|max:2048',
            'buat_akun'           => 'nullable|boolean',
            'user_email'          => 'nullable|required_if:buat_akun,1|email|unique:users,email',
            'user_password'       => 'nullable|required_if:buat_akun,1|min:6',
            'user_role'           => 'nullable|in:superadmin,admin,hrd,supervisor,karyawan,operator',
        ]);

        // Sync teks departemen/jabatan dari master untuk backward-compat kolom lama
        $dept = Department::find($request->department_id);
        $pos  = Position::find($request->position_id);
        $request->merge([
            'departemen' => $dept?->name ?? '',
            'jabatan'    => $pos?->name ?? '',
        ]);

        $request->merge(['bebas_absen' => $request->boolean('bebas_absen')]);
        $data = $request->only([
            'kode_karyawan', 'inisial', 'nama', 'departemen', 'jabatan', 'jenis_kelamin',
            'no_hp', 'email', 'pendidikan_terakhir', 'alamat', 'golongan_darah', 'status', 'shift_id',
            'department_id', 'devision_id', 'position_id',
            'birth_date', 'birth_place', 'religion', 'marital_status', 'jumlah_tanggungan',
            'kontak_darurat_nama', 'kontak_darurat_hubungan', 'kontak_darurat_no_hp',
            'bank_nama', 'bank_no_rekening', 'bank_atas_nama', 'bebas_absen', 'tanggal_masuk',
        ]);

        // Auto-set bebas_absen jika jabatan termasuk kategori bebas
        if (in_array(trim($request->input('jabatan', '')), \App\Models\Karyawan::JABATAN_BEBAS_ABSEN, true)) {
            $data['bebas_absen'] = true;
        }

        // Auto-generate NIK dulu sebelum daftarkan foto ke Face API
        $data['nik'] = $this->generateIdKaryawan($request->tanggal_masuk);

        $fotoB64 = null;

        if ($request->filled('foto_webcam')) {
            $b64      = $request->input('foto_webcam');
            $imgData  = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
            $imgBin   = base64_decode($imgData);
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
                $res     = Http::timeout(30)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/daftarkan', [
                    'nik'  => $data['nik'],
                    'foto' => $fotoB64,
                ]);
                $resData = $res->json();
                if (isset($resData['success']) && !$resData['success']) {
                    return back()->withInput()
                                 ->with('error', 'Foto gagal didaftarkan: ' . ($resData['message'] ?? 'Error'));
                }
            } catch (\Exception $e) {
                // Python service tidak aktif, tetap simpan data
            }
        }

        // Auto-generate inisial dari nama jika tidak diisi
        if (empty($data['inisial']) && !empty($data['nama'])) {
            $words = array_filter(explode(' ', trim($data['nama'])));
            $data['inisial'] = implode('', array_map(fn($w) => strtoupper(substr($w, 0, 1)), $words));
        }

        $karyawan = Karyawan::create($data);

        // Inisialisasi saldo cuti
        CutiSaldo::getSaldo($karyawan->id);

        // Simpan dokumen identitas jika ada
        $docFields = ['doc_nik','doc_kk','doc_npwp','doc_bpjs_tk','doc_bpjs_kesehatan'];
        $hasDoc = collect($docFields)->some(fn($f) => $request->filled($f));
        if ($hasDoc) {
            Legaldoc::create([
                'karyawan_id'    => $karyawan->id,
                'nik'            => $request->doc_nik ?: null,
                'kk'             => $request->doc_kk ?: null,
                'npwp'           => $request->doc_npwp ?: null,
                'bpjs_tk'        => $request->doc_bpjs_tk ?: null,
                'bpjs_kesehatan' => $request->doc_bpjs_kesehatan ?: null,
            ]);
        }

        // Auto-buat akun user jika diminta
        if ($request->boolean('buat_akun') && $request->filled('user_email')) {
            $role = $request->user_role ?? 'karyawan';
            $this->assertRoleAssignable($role);
            $user = User::create([
                'name'         => $karyawan->nama,
                'email'        => $request->user_email,
                'password'     => Hash::make($request->user_password),
                'role'         => $role,
                'karyawan_id'  => $karyawan->id,
            ]);
        }

        AuditLog::catat('create', "Tambah karyawan: {$karyawan->nama} (NIK: {$karyawan->nik})", $karyawan);

        return redirect()->route('admin.karyawan.index')
                         ->with('success', 'Karyawan berhasil ditambahkan!' . (isset($user) ? ' Akun login juga telah dibuat.' : ''));
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load([
            'shift', 'department.spvUser', 'department.managerUser', 'devision', 'position',
            'educations', 'legaldoc', 'workhistories',
            'warningletters', 'leaverequests.approver',
            'employeshifts.shift', 'izin', 'pelatihans', 'lemburan',
        ]);
        $absensiTerbaru = $karyawan->absensi()->latest()->take(30)->get();
        $shifts         = Shift::where('status', 'aktif')->orderBy('nama_shift')->get();
        $akunUser       = User::where('karyawan_id', $karyawan->id)->first();

        // Generate QR inline sebagai base64 — hindari masalah auth/HTTPS pada <img src>
        $qrBase64 = null;
        try {
            $qrBase64 = $this->makeQrPng($karyawan->nik, 8);
        } catch (\Exception $e) {}

        return view('admin.karyawan.show', compact('karyawan', 'absensiTerbaru', 'shifts', 'akunUser', 'qrBase64'));
    }

    public function edit(Karyawan $karyawan)
    {
        $shifts      = Shift::where('status', 'aktif')->get();
        $departments = Department::orderBy('name')->get();
        $devisions   = Devision::with('department')->orderBy('name')->get();
        $positions   = Position::orderBy('name')->get();
        return view('admin.karyawan.edit', compact('karyawan', 'shifts', 'departments', 'devisions', 'positions'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'kode_karyawan'       => 'nullable|max:20|unique:karyawan,kode_karyawan,' . $karyawan->id,
            'nik'                 => 'required|max:20|unique:karyawan,nik,' . $karyawan->id,
            'inisial'             => 'nullable|string|max:10',
            'nama'                => 'required|string|max:100',
            'department_id'       => 'required|exists:departments,id',
            'devision_id'         => 'nullable|exists:devisions,id',
            'position_id'         => 'required|exists:positions,id',
            'jenis_kelamin'       => 'required|in:L,P',
            'email'               => 'nullable|email|max:100',
            'pendidikan_terakhir' => 'nullable|in:SD,SMP,SMA/SMK,D1,D2,D3,S1,S2,S3',
            'status'                   => 'required|in:kontrak,tetap,magang,training,nonaktif,outsourcing,resign,phk,harian',
            'marital_status'            => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'jumlah_tanggungan'         => 'nullable|integer|min:0|max:20',
            'golongan_darah'            => 'nullable|in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'kontak_darurat_nama'       => 'nullable|string|max:100',
            'kontak_darurat_hubungan'   => 'nullable|string|max:50',
            'kontak_darurat_no_hp'      => 'nullable|string|max:20',
            'bank_nama'                 => 'nullable|string|max:50',
            'bank_no_rekening'          => 'nullable|string|max:30',
            'bank_atas_nama'            => 'nullable|string|max:100',
            'shift_id'                  => 'nullable|exists:shifts,id',
            'bebas_absen'               => 'nullable|boolean',
            'foto_wajah'                => 'nullable|image|max:2048',
        ]);

        // Sync teks dari master
        $dept = Department::find($request->department_id);
        $pos  = Position::find($request->position_id);
        $request->merge([
            'departemen' => $dept?->name ?? '',
            'jabatan'    => $pos?->name ?? '',
        ]);

        $request->merge(['bebas_absen' => $request->boolean('bebas_absen')]);
        $data    = $request->only([
            'kode_karyawan', 'nik', 'inisial', 'nama', 'departemen', 'jabatan', 'jenis_kelamin',
            'no_hp', 'email', 'pendidikan_terakhir', 'alamat', 'golongan_darah', 'status', 'shift_id',
            'department_id', 'devision_id', 'position_id',
            'birth_date', 'birth_place', 'religion', 'marital_status', 'jumlah_tanggungan',
            'kontak_darurat_nama', 'kontak_darurat_hubungan', 'kontak_darurat_no_hp',
            'bank_nama', 'bank_no_rekening', 'bank_atas_nama', 'bebas_absen',
        ]);
        $fotoB64 = null;

        if ($request->filled('foto_webcam')) {
            if ($karyawan->foto_wajah) {
                Storage::disk('public')->delete($karyawan->foto_wajah);
            }
            $b64      = $request->input('foto_webcam');
            $imgData  = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
            $imgBin   = base64_decode($imgData);
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
                $res     = Http::timeout(30)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/daftarkan', [
                    'nik'  => $request->nik,
                    'foto' => $fotoB64,
                ]);
                $resData = $res->json();
                if (isset($resData['success']) && !$resData['success']) {
                    return back()->withInput()
                                 ->with('error', 'Foto gagal didaftarkan: ' . ($resData['message'] ?? 'Error'));
                }
            } catch (\Exception $e) {
                // Python service tidak aktif
            }
        }

        // Auto-set bebas_absen jika jabatan termasuk kategori bebas
        if (in_array(trim($data['jabatan'] ?? ''), \App\Models\Karyawan::JABATAN_BEBAS_ABSEN, true)) {
            $data['bebas_absen'] = true;
        }

        $karyawan->update($data);

        // Simpan / update dokumen identitas
        Legaldoc::updateOrCreate(
            ['karyawan_id' => $karyawan->id],
            [
                'nik'            => $request->doc_nik ?: null,
                'kk'             => $request->doc_kk ?: null,
                'npwp'           => $request->doc_npwp ?: null,
                'bpjs_tk'        => $request->doc_bpjs_tk ?: null,
                'bpjs_kesehatan' => $request->doc_bpjs_kesehatan ?: null,
            ]
        );

        AuditLog::catat('update', "Update data karyawan: {$karyawan->nama} (NIK: {$karyawan->nik})", $karyawan);

        return redirect()->route('admin.karyawan.index')
                         ->with('success', 'Data karyawan berhasil diupdate!');
    }

    public function updateFoto(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'foto_wajah'  => 'nullable|image|max:2048',
            'foto_webcam' => 'nullable|string',
        ]);

        $fotoB64 = null;

        if ($request->filled('foto_webcam')) {
            if ($karyawan->foto_wajah) Storage::disk('public')->delete($karyawan->foto_wajah);
            $b64      = $request->input('foto_webcam');
            $imgData  = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
            $filename = 'wajah/' . uniqid('wajah_') . '.jpg';
            Storage::disk('public')->put($filename, base64_decode($imgData));
            $karyawan->update(['foto_wajah' => $filename]);
            $fotoB64 = $b64;
        } elseif ($request->hasFile('foto_wajah')) {
            if ($karyawan->foto_wajah) Storage::disk('public')->delete($karyawan->foto_wajah);
            $path = $request->file('foto_wajah')->store('wajah', 'public');
            $karyawan->update(['foto_wajah' => $path]);
            $fotoB64 = 'data:image/jpeg;base64,' . base64_encode(
                file_get_contents($request->file('foto_wajah')->getRealPath())
            );
        } else {
            return back()->with('error', 'Tidak ada foto yang dipilih.');
        }

        // Daftarkan ke Python face recognition
        if ($fotoB64) {
            try {
                $res = Http::timeout(30)->post(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/daftarkan', [
                    'nik'  => $karyawan->nik,
                    'foto' => $fotoB64,
                ]);
                $resData = $res->json();
                if (isset($resData['success']) && !$resData['success']) {
                    return back()->with('warning', 'Foto disimpan, namun gagal didaftarkan ke sistem pengenalan wajah: ' . ($resData['message'] ?? 'Error'));
                }
            } catch (\Exception $e) {
                return back()->with('warning', 'Foto disimpan, namun layanan pengenalan wajah tidak aktif. Foto akan aktif setelah layanan dinyalakan.');
            }
        }

        AuditLog::catat('update', "Update foto wajah karyawan: {$karyawan->nama}", $karyawan);
        return back()->with('success', 'Foto wajah berhasil diperbarui dan didaftarkan ke sistem.');
    }

    private function makeQrPng(string $data, int $scale = 8): string
    {
        $opt = new QROptions;
        $opt->outputType = QRMarkupSVG::class;
        $opt->scale      = $scale;
        return (new ChillerQR($opt))->render($data);
    }

    public function generateQr(Karyawan $karyawan)
    {
        $svgData = $this->makeQrPng($karyawan->nik, 10);
        // svgData = "data:image/svg+xml;base64,..."
        $base64 = str_replace('data:image/svg+xml;base64,', '', $svgData);
        $svg    = base64_decode($base64);

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'inline; filename="qr-' . $karyawan->nik . '.svg"');
    }

    public function printQr(Karyawan $karyawan)
    {
        $karyawan->loadMissing('department');
        $qrPng          = $this->makeQrPng($karyawan->nik, 8);
        $namaPerusahaan = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        return view('admin.karyawan.print-qr', compact('karyawan', 'qrPng', 'namaPerusahaan'));
    }

    public function printQrSemua()
    {
        $deptIds = $this->scopedDepartmentIds();
        $karyawanList = Karyawan::with('department')
            ->whereIn('status', ['kontrak','tetap','magang','training'])
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->leftJoin('departments as d_sort', 'd_sort.id', '=', 'karyawan.department_id')
            ->orderBy('d_sort.name')
            ->orderByRaw($this->jabatanOrderSql())
            ->orderBy('karyawan.nama')
            ->select('karyawan.*')
            ->get();
        $namaPerusahaan = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $qrList = $karyawanList->map(fn($k) => [
            'karyawan' => $k,
            'qrPng'    => $this->makeQrPng($k->nik, 7),
        ]);
        return view('admin.karyawan.print-qr-semua', compact('qrList', 'namaPerusahaan'));
    }

    public function idCard(Karyawan $karyawan)
    {
        $karyawan->loadMissing('department');
        $qrPng           = $this->makeQrPng($karyawan->nik, 5);
        $namaPerusahaan  = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $logoPerusahaan  = \App\Models\Pengaturan::get('logo_perusahaan', '');
        return view('admin.karyawan.id-card', compact('karyawan', 'qrPng', 'namaPerusahaan', 'logoPerusahaan'));
    }

    public function idCardSemua()
    {
        $deptIds = $this->scopedDepartmentIds();
        $karyawanList = Karyawan::with('department')
            ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->leftJoin('departments as d_sort', 'd_sort.id', '=', 'karyawan.department_id')
            ->orderBy('d_sort.name')
            ->orderBy('karyawan.nama')
            ->select('karyawan.*')
            ->get();

        $namaPerusahaan = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $logoPerusahaan = \App\Models\Pengaturan::get('logo_perusahaan', '');

        $cards = $karyawanList->map(fn($k) => [
            'karyawan' => $k,
            'qrPng'    => $this->makeQrPng($k->nik, 4),
        ]);

        return view('admin.karyawan.id-card-semua', compact('cards', 'namaPerusahaan', 'logoPerusahaan'));
    }

    public function buatAkun(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:superadmin,admin,hrd,supervisor,karyawan,operator',
        ], [
            'email.unique'    => 'Email sudah digunakan oleh akun lain.',
            'password.min'    => 'Password minimal 6 karakter.',
        ]);

        $this->assertRoleAssignable($request->role);

        User::create([
            'name'        => $karyawan->nama,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'karyawan_id' => $karyawan->id,
        ]);

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Akun login untuk ' . $karyawan->nama . ' berhasil dibuat!');
    }

    public function bulkBuatAkun(Request $request)
    {
        $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'password'      => 'required|min:6',
            'domain_email'  => 'required|string',
        ]);

        $linkedIds = \DB::table('users')->whereNotNull('karyawan_id')->pluck('karyawan_id');

        $query = Karyawan::whereNotIn('status', ['resign', 'phk', 'nonaktif'])
                         ->whereNotIn('id', $linkedIds);

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null) {
            // Supervisor/manajer (kalau diberi permission ini): wajib dibatasi ke
            // departemennya sendiri, tidak boleh buat akun massal lintas departemen.
            $query->whereIn('department_id', $deptIds);
        } elseif ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $karyawanList = $query->get();
        $created = 0;
        $skipped = 0;
        $domain  = ltrim($request->domain_email, '@');

        foreach ($karyawanList as $k) {
            $email = \Illuminate\Support\Str::slug($k->nama, '.') . '@' . $domain;
            // ensure unique
            $base = $email; $suffix = 1;
            while (\DB::table('users')->where('email', $email)->exists()) {
                $email = $base . $suffix . '@' . $domain;
                $suffix++;
            }
            try {
                User::create([
                    'name'        => $k->nama,
                    'email'       => $email,
                    'password'    => Hash::make($request->password),
                    'role'        => 'karyawan',
                    'karyawan_id' => $k->id,
                ]);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        return back()->with('bulk_akun_result', "$created akun berhasil dibuat" . ($skipped ? ", $skipped dilewati." : "."));
    }

    // "Hapus" karyawan dari daftar TIDAK menghapus row/riwayat — hanya menonaktifkan.
    // Riwayat absensi/cuti/izin/SP karyawan tetap tersimpan untuk keperluan audit
    // kepatuhan (cascadeOnDelete di migration akan menghapus SEMUA riwayat itu kalau
    // row karyawan benar-benar dihapus, yang tidak diinginkan untuk kasus normal).
    public function destroy(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tanggal_keluar'          => 'nullable|date',
            'alasan_keluar_kategori'  => 'nullable|string|max:100',
            'alasan_keluar_catatan'   => 'nullable|string|max:255',
            // Kompatibilitas: jika dikirim langsung sebagai 'alasan_keluar' (mis. dari
            // integrasi/skrip lama), tetap diterima tanpa perlu kategori/catatan.
            'alasan_keluar'           => 'nullable|string|max:255',
        ]);

        $alasan = $request->alasan_keluar;
        if (!$alasan && $request->filled('alasan_keluar_kategori')) {
            $alasan = $request->alasan_keluar_kategori;
            if ($request->filled('alasan_keluar_catatan')) {
                $alasan .= ' — ' . $request->alasan_keluar_catatan;
            }
        }

        $karyawan->update([
            'status'        => 'resign',
            'tanggal_keluar'=> $request->tanggal_keluar ?: now(),
            'alasan_keluar' => $alasan,
        ]);

        // Nonaktifkan akun login, jangan hapus (riwayat AuditLog milik user itu ikut terjaga)
        User::where('karyawan_id', $karyawan->id)->update(['role' => 'karyawan']);

        try {
            Http::timeout(5)->delete(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/hapus/' . $karyawan->nik);
        } catch (\Exception $e) {}

        AuditLog::catat('update', "Nonaktifkan karyawan (resign): {$karyawan->nama} (NIK: {$karyawan->nik})", $karyawan);

        return redirect()->route('admin.karyawan.index')
                         ->with('success', 'Karyawan berhasil dinonaktifkan (data & riwayat tetap tersimpan).');
    }

    // Hapus permanen — hanya superadmin. Ini akan menghapus SEMUA riwayat terkait
    // (absensi/cuti/izin/koreksi/SP) karena cascadeOnDelete di migration. Gunakan
    // hanya untuk data yang benar-benar salah input/data uji, bukan karyawan resign biasa.
    public function destroyPermanent(Karyawan $karyawan)
    {
        abort_unless(auth()->user()->role === 'superadmin', 403, 'Hanya Superadmin yang dapat menghapus permanen.');

        $nama = $karyawan->nama;
        $nik  = $karyawan->nik;

        if ($karyawan->foto_wajah) {
            Storage::disk('public')->delete($karyawan->foto_wajah);
        }
        try {
            Http::timeout(5)->delete(env('FACE_API_URL', 'http://127.0.0.1:5000') . '/hapus/' . $karyawan->nik);
        } catch (\Exception $e) {}

        User::where('karyawan_id', $karyawan->id)->delete();
        $karyawan->delete();

        AuditLog::catat('delete', "Hapus PERMANEN karyawan: {$nama} (NIK: {$nik}) — seluruh riwayat terkait ikut terhapus");

        return redirect()->route('admin.karyawan.index')
                         ->with('success', "Karyawan {$nama} dan seluruh riwayatnya berhasil dihapus permanen!");
    }

    public function syncWajah()
    {
        $faceUrl  = env('FACE_API_URL', 'http://127.0.0.1:5000');
        $karyawan = Karyawan::whereNotNull('foto_wajah')
            ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->get(['id','nik','nama','foto_wajah']);

        $berhasil = 0;
        $gagal    = [];

        foreach ($karyawan as $k) {
            $path = Storage::disk('public')->path($k->foto_wajah);
            if (!file_exists($path)) {
                $gagal[] = $k->nama . ' (file foto tidak ditemukan)';
                continue;
            }
            try {
                $b64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
                $res = Http::timeout(20)->post($faceUrl . '/daftarkan', [
                    'nik'  => $k->nik,
                    'foto' => $b64,
                ]);
                $data = $res->json() ?? [];
                if ($res->successful() && ($data['success'] ?? false)) {
                    $berhasil++;
                } else {
                    $gagal[] = $k->nama . ': ' . ($data['message'] ?? 'Error tidak diketahui');
                }
            } catch (\Exception $e) {
                $gagal[] = $k->nama . ': ' . $e->getMessage();
            }
        }

        // Trigger reload cache di service
        try { Http::timeout(5)->post($faceUrl . '/reload'); } catch (\Exception $e) {}

        $msg = "Sync Face ID selesai: {$berhasil} wajah berhasil didaftarkan" .
               (count($gagal) ? ', ' . count($gagal) . ' gagal.' : '.');

        return back()->with('success', $msg)
                     ->with('sync_gagal', $gagal);
    }

    private function generateIdKaryawan(string $tanggalMasuk): string
    {
        $tgl    = \Carbon\Carbon::parse($tanggalMasuk);
        $prefix = $tgl->format('Ym'); // YYYYMM

        // Hitung berapa karyawan sudah masuk di bulan yang sama
        $count = Karyawan::whereYear('tanggal_masuk', $tgl->year)
            ->whereMonth('tanggal_masuk', $tgl->month)
            ->count();

        $urutan = $count + 1;
        $id     = $tgl->format('Y') . $tgl->format('m') . str_pad($urutan, 2, '0', STR_PAD_LEFT);

        // Pastikan unik (jika bentrok, increment)
        while (Karyawan::where('nik', $id)->exists()) {
            $urutan++;
            $id = $tgl->format('Y') . $tgl->format('m') . str_pad($urutan, 2, '0', STR_PAD_LEFT);
        }

        return $id;
    }
}
