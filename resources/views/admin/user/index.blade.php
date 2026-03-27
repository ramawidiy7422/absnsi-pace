@extends('layouts.admin')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-users-cog" style="color:#4299e1;"></i> Daftar User Sistem</h6>
        <button class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah User
        </button>
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
                        <th>Dibuat</th>
                        <th style="width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                    <tr>
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
                                $roleColors = ['admin'=>'#e53e3e','hrd'=>'#3182ce','supervisor'=>'#805ad5'];
                                $roleColor  = $roleColors[$user->role] ?? '#718096';
                            @endphp
                            <span style="background:{{ $roleColor }}1a;color:{{ $roleColor }};padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;border:1px solid {{ $roleColor }}40;">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td style="color:#a0aec0;font-size:12px;">{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick='openEditModal(@json($user))' title="Edit">
                                <i class="fas fa-edit"></i>
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
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-users" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                            Belum ada user
                        </td>
                    </tr>
                    @endforelse
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
        <div class="grid-3">
            <div style="background:#fff5f5;border:1px solid #fed7d7;border-radius:10px;padding:16px;">
                <div style="color:#c53030;font-weight:700;margin-bottom:8px;"><i class="fas fa-crown"></i> Admin</div>
                <ul style="color:#718096;font-size:13px;padding-left:16px;line-height:2;">
                    <li>Semua akses penuh</li>
                    <li>Kelola user sistem</li>
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
                <div style="color:#6b46c1;font-weight:700;margin-bottom:8px;"><i class="fas fa-user-check"></i> Supervisor</div>
                <ul style="color:#718096;font-size:13px;padding-left:16px;line-height:2;">
                    <li>Lihat data karyawan</li>
                    <li>Lihat data absensi</li>
                    <li>Lihat laporan & rekap</li>
                    <li>Tidak bisa ubah/hapus data</li>
                </ul>
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
                    <option value="admin"      {{ old('role')=='admin'      ? 'selected' : '' }}>Admin</option>
                    <option value="hrd"        {{ old('role')=='hrd'        ? 'selected' : '' }}>HRD</option>
                    <option value="supervisor" {{ old('role')=='supervisor' ? 'selected' : '' }}>Supervisor</option>
                </select>
                @error('role') <small style="color:#c53030;">{{ $message }}</small> @enderror
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
                    <option value="admin">Admin</option>
                    <option value="hrd">HRD</option>
                    <option value="supervisor">Supervisor</option>
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
@endsection

@push('scripts')
<script>
function openModal() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modalTambah').style.display = 'none';
}
function openEditModal(user) {
    document.getElementById('editName').value  = user.name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editRole').value  = user.role;
    document.getElementById('editPassword').value = '';
    document.getElementById('formEdit').action = '/admin/users/' + user.id;
    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

// Tutup modal saat klik backdrop
['modalTambah','modalEdit'].forEach(id => {
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
</script>
@endpush
