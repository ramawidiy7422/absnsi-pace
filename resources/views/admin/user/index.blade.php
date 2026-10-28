@extends('layouts.admin')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-users-cog" style="color:#4299e1;"></i> Daftar User Sistem</h6>
        <div style="display:flex;gap:8px;">
            @if(auth()->user()->hasPermission('users.kelola'))
            <button class="btn btn-success btn-sm" onclick="openBulkModal()">
                <i class="fas fa-users"></i> Bulk Buat Akun
            </button>
            <button class="btn btn-primary btn-sm" onclick="openModal()">
                <i class="fas fa-plus"></i> Tambah User
            </button>
            @endif
        </div>
    </div>
    <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div style="position:relative;flex:1;min-width:180px;">
            <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a0aec0;font-size:13px;pointer-events:none;"></i>
            <input type="text" id="userSearch" oninput="filterUsers()" placeholder="Cari nama, email, karyawan..."
                style="width:100%;padding:7px 10px 7px 32px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;">
        </div>
        <select id="userRoleFilter" onchange="filterUsers()" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#4a5568;">
            <option value="">Semua Role</option>
            <option value="superadmin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="hrd">HRD</option>
            <option value="supervisor">SPV/Manager</option>
            <option value="karyawan">Karyawan</option>
            <option value="operator">Operator</option>
        </select>
        <span id="userCount" style="font-size:12px;color:#a0aec0;white-space:nowrap;">{{ $users->count() }} user</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Karyawan</th>
                        <th>Dibuat</th>
                        <th style="width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    @forelse($users as $i => $user)
                    <tr class="user-row"
                        data-nama="{{ strtolower($user->name) }}"
                        data-email="{{ strtolower($user->email) }}"
                        data-role="{{ $user->role }}"
                        data-karyawan="{{ strtolower($user->karyawan?->nama ?? '') }}"
                        data-nik="{{ $user->karyawan?->nik ?? '' }}"
                    >
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#4299e1,#667eea);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;color:#2d3748;">{{ $user->name }}</div>
                                    @if($user->id === auth()->id())
                                        <small style="color:#48bb78;font-size:10px;"><i class="fas fa-check-circle"></i> Akun Anda</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="color:#718096;">{{ $user->email }}</td>
                        <td>
                            @php
                                $roleColors = ['superadmin'=>'#c05621','admin'=>'#e53e3e','hrd'=>'#3182ce','supervisor'=>'#805ad5','karyawan'=>'#2f855a','operator'=>'#718096'];
                                $roleLabel  = ['superadmin'=>'Super Admin','admin'=>'Admin','hrd'=>'HRD','supervisor'=>'SPV/Manager','karyawan'=>'Karyawan','operator'=>'Operator'];
                                $roleColor  = $roleColors[$user->role] ?? '#718096';
                            @endphp
                            <span style="background:{{ $roleColor }}1a;color:{{ $roleColor }};padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;border:1px solid {{ $roleColor }}40;">
                                {{ $roleLabel[$user->role] ?? ucfirst($user->role) }}
                            </span>
                        </td>
                        <td style="font-size:12px;">
                            @if($user->karyawan)
                                <span style="color:#2d3748;font-weight:600;">{{ $user->karyawan->nama }}</span><br>
                                <small style="color:#a0aec0;">{{ $user->karyawan->nik }}</small>
                            @else
                                <span style="color:#cbd5e0;">— tidak terhubung</span>
                            @endif
                        </td>
                        <td style="color:#a0aec0;font-size:12px;">{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick='openEditModal(@json($user))' title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-info btn-sm" onclick='openResetModal({{ $user->id }}, "{{ addslashes($user->name) }}")' title="Reset Password" style="background:#3182ce;border-color:#3182ce;">
                                <i class="fas fa-key"></i>
                            </button>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline;"
                                  onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <button class="btn btn-secondary btn-sm" disabled title="Tidak bisa hapus akun sendiri">
                                <i class="fas fa-lock"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="7" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-users" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                            Belum ada user
                        </td>
                    </tr>
                    @endforelse
                    <tr id="noResultRow" style="display:none;">
                        <td colspan="7" style="text-align:center;padding:30px;color:#a0aec0;">
                            <i class="fas fa-search" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                            Tidak ada user yang cocok
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Info Role --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-info-circle" style="color:#4299e1;"></i> Hak Akses per Role</h6>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
            <div style="background:#fffaf0;border:1px solid #f6ad55;border-radius:10px;padding:16px;">
                <div style="color:#c05621;font-weight:700;margin-bottom:8px;"><i class="fas fa-shield-alt"></i> Super Admin</div>
                <ul style="color:#718096;font-size:13px;padding-left:16px;line-height:2;">
                    <li>Akses penuh tanpa batas</li>
                    <li>Kelola semua user & role</li>
                    <li>Buat akun Super Admin baru</li>
                    <li>Semua fitur Admin</li>
                </ul>
            </div>
            <div style="background:#fff5f5;border:1px solid #fed7d7;border-radius:10px;padding:16px;">
                <div style="color:#c53030;font-weight:700;margin-bottom:8px;"><i class="fas fa-crown"></i> Admin</div>
                <ul style="color:#718096;font-size:13px;padding-left:16px;line-height:2;">
                    <li>Kelola user (kecuali superadmin)</li>
                    <li>Hapus data karyawan, shift, absensi</li>
                    <li>Lihat semua laporan</li>
                </ul>
            </div>
            <div style="background:#ebf8ff;border:1px solid #bee3f8;border-radius:10px;padding:16px;">
                <div style="color:#2b6cb0;font-weight:700;margin-bottom:8px;"><i class="fas fa-user-tie"></i> HRD</div>
                <ul style="color:#718096;font-size:13px;padding-left:16px;line-height:2;">
                    <li>Input & edit karyawan</li>
                    <li>Input & edit absensi</li>
                    <li>Input & edit shift</li>
                    <li>Lihat semua laporan</li>
                </ul>
            </div>
            <div style="background:#f3e8ff;border:1px solid #e9d8fd;border-radius:10px;padding:16px;">
                <div style="color:#6b46c1;font-weight:700;margin-bottom:8px;"><i class="fas fa-user-check"></i> SPV/Manager</div>
                <ul style="color:#718096;font-size:13px;padding-left:16px;line-height:2;">
                    <li>Lihat data karyawan departemen sendiri</li>
                    <li>Lihat data absensi departemen sendiri</li>
                    <li>Approve cuti/izin/lembur departemen sendiri</li>
                    <li>Tidak bisa ubah/hapus data</li>
                </ul>
            </div>
            <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:16px;">
                <div style="color:#276749;font-weight:700;margin-bottom:8px;"><i class="fas fa-id-badge"></i> Karyawan</div>
                <ul style="color:#718096;font-size:13px;padding-left:16px;line-height:2;">
                    <li>Lihat jadwal sendiri</li>
                    <li>Ajukan cuti & izin</li>
                    <li>Lihat status pengajuan</li>
                    <li>Tidak bisa lihat data karyawan lain</li>
                </ul>
            </div>
        </div>
    </div>
</div>


{{-- Modal Reset Password --}}
<div id="modalReset" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;width:420px;max-width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:18px 22px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;color:#2d3748;"><i class="fas fa-key" style="color:#3182ce;"></i> Reset Password</h6>
            <button onclick="closeResetModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <div style="padding:22px;">
            <p style="color:#718096;margin-bottom:16px;">Reset password untuk: <strong id="resetUserName"></strong></p>

            {{-- Form reset manual --}}
            <form method="POST" id="formReset">
                @csrf
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password" id="resetPassword" class="form-control" minlength="6" required placeholder="Min. 6 karakter">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="resetPasswordConfirm" class="form-control" required placeholder="Ulangi password">
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px;">
                    <button type="button" onclick="closeResetModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Password</button>
                </div>
            </form>

            <div style="border-top:1px solid #f0f0f0;margin-top:16px;padding-top:16px;">
                <p style="font-size:12px;color:#a0aec0;margin-bottom:10px;">Atau reset ke password acak (akan ditampilkan sekali):</p>
                <form method="POST" id="formResetRandom" onsubmit="return confirm('Reset password ke acak untuk user ini?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;">
                        <i class="fas fa-dice"></i> Generate Password Acak
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalTambah" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;width:460px;max-width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:18px 22px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;color:#2d3748;"><i class="fas fa-user-plus" style="color:#4299e1;"></i> Tambah User Baru</h6>
            <button onclick="closeModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" style="padding:22px;">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name') <small style="color:#c53030;">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email') <small style="color:#c53030;">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    @if(auth()->user()->role === 'superadmin')
                    <option value="superadmin" {{ old('role')=='superadmin' ? 'selected' : '' }}>Super Admin</option>
                    @endif
                    @if(auth()->user()->hasPermission('users.kelola'))
                    <option value="admin"      {{ old('role')=='admin'      ? 'selected' : '' }}>Admin</option>
                    @endif
                    <option value="hrd"        {{ old('role')=='hrd'        ? 'selected' : '' }}>HRD</option>
                    <option value="supervisor" {{ old('role')=='supervisor' ? 'selected' : '' }}>SPV/Manager</option>
                    <option value="karyawan"   {{ old('role','karyawan')=='karyawan' ? 'selected' : '' }}>Karyawan</option>
                </select>
                @error('role') <small style="color:#c53030;">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
                <label>Hubungkan ke Karyawan <small style="color:#a0aec0;">(opsional — agar bisa lihat jadwal sendiri)</small></label>
                <select name="karyawan_id" class="form-select">
                    <option value="">— Tidak dihubungkan —</option>
                    @foreach($karyawan as $k)
                        <option value="{{ $k->id }}" {{ old('karyawan_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} ({{ $k->nik }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                    @error('password') <small style="color:#c53030;">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEdit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;width:460px;max-width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:18px 22px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;color:#2d3748;"><i class="fas fa-user-edit" style="color:#ed8936;"></i> Edit User</h6>
            <button onclick="closeEditModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form method="POST" id="formEdit" style="padding:22px;">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" id="editName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="editEmail" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="editRole" class="form-select" required>
                    @if(auth()->user()->role === 'superadmin')
                    <option value="superadmin">Super Admin</option>
                    @endif
                    @if(auth()->user()->hasPermission('users.kelola'))
                    <option value="admin">Admin</option>
                    @endif
                    <option value="hrd">HRD</option>
                    <option value="supervisor">SPV/Manager</option>
                    <option value="karyawan">Karyawan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Hubungkan ke Karyawan <small style="color:#a0aec0;">(opsional)</small></label>
                <select name="karyawan_id" id="editKaryawanId" class="form-select">
                    <option value="">— Tidak dihubungkan —</option>
                    @foreach($karyawan as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->nik }})</option>
                    @endforeach
                </select>
            </div>
            <div style="background:#fffbeb;border:1px solid #fbd38d;border-radius:8px;padding:12px;margin-bottom:16px;">
                <small style="color:#c05621;font-weight:600;"><i class="fas fa-info-circle"></i> Isi password hanya jika ingin mengubah password</small>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Password Baru <small style="color:#a0aec0;">(opsional)</small></label>
                    <input type="password" name="password" id="editPassword" class="form-control" minlength="6">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL BULK BUAT AKUN ===================== --}}
<div id="modalBulk" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1050;align-items:flex-start;justify-content:center;overflow-y:auto;padding:30px 0;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:720px;margin:auto;">
        <div style="background:linear-gradient(135deg,#276749,#38a169);border-radius:12px 12px 0 0;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
            <h6 style="margin:0;color:#fff;font-weight:700;"><i class="fas fa-users"></i> Bulk Buat Akun Karyawan</h6>
            <button type="button" onclick="closeBulkModal()" style="background:rgba(255,255,255,.2);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:16px;">&times;</button>
        </div>

        {{-- Hasil bulk create sebelumnya --}}
        @if(session('bulk_results'))
        <div style="padding:16px 20px;background:#f0fff4;border-bottom:1px solid #c6f6d5;">
            <p style="font-weight:700;color:#276749;margin-bottom:8px;"><i class="fas fa-check-circle"></i> Akun berhasil dibuat:</p>
            <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#276749;color:#fff;">
                        <th style="padding:6px 10px;text-align:left;">Nama</th>
                        <th style="padding:6px 10px;text-align:left;">NIK</th>
                        <th style="padding:6px 10px;text-align:left;">Email</th>
                        <th style="padding:6px 10px;text-align:left;">Password</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(session('bulk_results') as $r)
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:6px 10px;">{{ $r['nama'] }}</td>
                        <td style="padding:6px 10px;font-family:monospace;">{{ $r['nik'] }}</td>
                        <td style="padding:6px 10px;font-size:12px;">{{ $r['email'] }}</td>
                        <td style="padding:6px 10px;font-family:monospace;font-weight:700;color:#c05621;">{{ $r['password'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <p style="margin-top:8px;font-size:11px;color:#718096;"><i class="fas fa-exclamation-triangle"></i> Catat/simpan password di atas — tidak bisa dilihat lagi setelah halaman ini ditutup.</p>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.users.bulk-create') }}">
            @csrf
            <div style="padding:20px;">

                {{-- Filter departemen --}}
                @php
                    $deptList = $karyawanTanpaAkun->pluck('departemen')->filter()->unique()->sort()->values();
                @endphp

                @if($karyawanTanpaAkun->isEmpty())
                <div style="text-align:center;padding:30px;color:#718096;">
                    <i class="fas fa-check-circle" style="font-size:32px;color:#48bb78;margin-bottom:8px;display:block;"></i>
                    Semua karyawan aktif sudah memiliki akun login.
                </div>
                @else

                <div style="margin-bottom:12px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <select id="filter-dept" onchange="filterDept()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
                        <option value="">Semua Departemen</option>
                        @foreach($deptList as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="filter-nama" oninput="filterNama()" placeholder="Cari nama..." style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;flex:1;">
                    <span id="selected-count" style="font-size:13px;color:#718096;white-space:nowrap;">0 karyawan dipilih</span>
                </div>

                <div style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:16px;">
                    <div style="background:#f7fafc;padding:8px 12px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" id="chk-all" onchange="toggleAll(this)" style="width:15px;height:15px;">
                        <label for="chk-all" style="margin:0;font-size:13px;font-weight:600;cursor:pointer;">Pilih Semua</label>
                        <span style="font-size:12px;color:#a0aec0;">({{ $karyawanTanpaAkun->count() }} karyawan belum punya akun)</span>
                    </div>
                    <div style="max-height:240px;overflow-y:auto;" id="kary-list">
                        @foreach($karyawanTanpaAkun as $k)
                        <label class="kary-row" data-dept="{{ $k->departemen }}" data-nama="{{ strtolower($k->nama) }}"
                            style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-bottom:1px solid #f0f0f0;cursor:pointer;margin:0;">
                            <input type="checkbox" name="karyawan_ids[]" value="{{ $k->id }}" class="chk-kary" onchange="updateCount()" style="width:15px;height:15px;">
                            <div style="flex:1;">
                                <div style="font-weight:600;font-size:13px;">{{ $k->nama }}</div>
                                <div style="font-size:11px;color:#a0aec0;">{{ $k->nik }} · {{ $k->departemen ?: 'Tanpa Dept.' }} · {{ $k->jabatan }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid-2" style="margin-bottom:14px;">
                    <div class="form-group">
                        <label style="font-weight:600;font-size:13px;">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="karyawan">Karyawan</option>
                            <option value="operator">Operator</option>
                            <option value="supervisor">SPV/Manager</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600;font-size:13px;">Tipe Password</label>
                        <div style="display:flex;flex-direction:column;gap:6px;padding-top:4px;">
                            <label style="display:flex;align-items:center;gap:6px;font-weight:400;margin:0;cursor:pointer;">
                                <input type="radio" name="password_type" value="nik" checked onchange="togglePw(this)"> Gunakan NIK karyawan
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-weight:400;margin:0;cursor:pointer;">
                                <input type="radio" name="password_type" value="random" onchange="togglePw(this)"> Generate acak (8 karakter)
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-weight:400;margin:0;cursor:pointer;">
                                <input type="radio" name="password_type" value="custom" onchange="togglePw(this)"> Password kustom
                            </label>
                        </div>
                    </div>
                </div>

                <div id="custom-pw-wrap" style="display:none;margin-bottom:14px;">
                    <div class="form-group">
                        <label style="font-weight:600;font-size:13px;">Password Kustom <span style="color:#e53e3e;">*</span></label>
                        <input type="password" name="custom_password" id="custom_password" class="form-control" placeholder="Min. 6 karakter" minlength="6">
                    </div>
                </div>

                <div style="background:#fffbeb;border:1px solid #fbd38d;border-radius:8px;padding:10px 14px;margin-bottom:14px;">
                    <small style="color:#c05621;"><i class="fas fa-info-circle"></i>
                        Email akun akan menggunakan email karyawan (jika ada) atau dibuat otomatis: <code>nama.nik@pace.internal</code>
                    </small>
                </div>

                @endif
            </div>

            @if(!$karyawanTanpaAkun->isEmpty())
            <div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeBulkModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success" id="btn-bulk-submit" disabled>
                    <i class="fas fa-users"></i> Buat Akun
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@push('scripts')
<script>
function openModal() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modalTambah').style.display = 'none';
}
function openEditModal(user) {
    document.getElementById('editName').value        = user.name;
    document.getElementById('editEmail').value       = user.email;
    document.getElementById('editRole').value        = user.role;
    document.getElementById('editKaryawanId').value  = user.karyawan_id || '';
    document.getElementById('editPassword').value    = '';
    document.getElementById('formEdit').action       = '/admin/users/' + user.id;
    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

function openResetModal(userId, userName) {
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('resetPassword').value = '';
    document.getElementById('resetPasswordConfirm').value = '';
    document.getElementById('formReset').action = '{{ route("admin.users.reset-password", ":id") }}'.replace(':id', userId);
    document.getElementById('formResetRandom').action = '{{ route("admin.users.reset-password-random", ":id") }}'.replace(':id', userId);
    document.getElementById('modalReset').style.display = 'flex';
}
function closeResetModal() {
    document.getElementById('modalReset').style.display = 'none';
}

function openBulkModal() {
    document.getElementById('modalBulk').style.display = 'flex';
}
function closeBulkModal() {
    document.getElementById('modalBulk').style.display = 'none';
}

// Tutup modal saat klik backdrop
['modalTambah','modalEdit','modalReset','modalBulk'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});

// Buka modal otomatis jika ada error validasi
@if($errors->any())
    openModal();
@endif

function togglePw(radio) {
    document.getElementById('custom-pw-wrap').style.display = radio.value === 'custom' ? 'block' : 'none';
}

function toggleAll(chk) {
    document.querySelectorAll('.chk-kary:not([style*="display:none"])').forEach(c => {
        const row = c.closest('.kary-row');
        if (!row || row.style.display !== 'none') c.checked = chk.checked;
    });
    updateCount();
}

function updateCount() {
    const n   = document.querySelectorAll('.chk-kary:checked').length;
    const el  = document.getElementById('selected-count');
    const btn = document.getElementById('btn-bulk-submit');
    if (el)  el.textContent = n + ' karyawan dipilih';
    if (btn) btn.disabled = n === 0;
}

function filterDept() {
    const dept = document.getElementById('filter-dept').value.toLowerCase();
    const nama = document.getElementById('filter-nama').value.toLowerCase();
    document.querySelectorAll('.kary-row').forEach(row => {
        const matchDept = !dept || row.dataset.dept.toLowerCase() === dept;
        const matchNama = !nama || row.dataset.nama.includes(nama);
        row.style.display = matchDept && matchNama ? '' : 'none';
    });
    uncheckHidden();
}

function filterNama() {
    const dept = document.getElementById('filter-dept').value.toLowerCase();
    const nama = document.getElementById('filter-nama').value.toLowerCase();
    document.querySelectorAll('.kary-row').forEach(row => {
        const matchDept = !dept || row.dataset.dept.toLowerCase() === dept;
        const matchNama = !nama || row.dataset.nama.includes(nama);
        row.style.display = matchDept && matchNama ? '' : 'none';
    });
    uncheckHidden();
}

function uncheckHidden() {
    document.querySelectorAll('.kary-row').forEach(row => {
        if (row.style.display === 'none') {
            const chk = row.querySelector('.chk-kary');
            if (chk) chk.checked = false;
        }
    });
    updateCount();
}

// Auto-buka modal bulk jika ada bulk_results
@if(session('bulk_results'))
    openBulkModal();
@endif

function filterUsers() {
    const q    = document.getElementById('userSearch').value.toLowerCase().trim();
    const role = document.getElementById('userRoleFilter').value;
    const rows = document.querySelectorAll('.user-row');
    let visible = 0;

    rows.forEach(row => {
        const matchQ = !q ||
            row.dataset.nama.includes(q) ||
            row.dataset.email.includes(q) ||
            row.dataset.karyawan.includes(q) ||
            row.dataset.nik.includes(q);
        const matchRole = !role || row.dataset.role === role;
        const show = matchQ && matchRole;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const noResult = document.getElementById('noResultRow');
    if (noResult) noResult.style.display = visible === 0 && rows.length > 0 ? '' : 'none';

    const countEl = document.getElementById('userCount');
    if (countEl) countEl.textContent = visible + ' user';

    // Update nomor urut
    let no = 1;
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const td = row.querySelector('td:first-child');
            if (td) td.textContent = no++;
        }
    });
}
</script>
@endpush
@endsection

