@extends('layouts.admin')
@section('title', 'Profil')
@section('page-title', 'Profil Akun')

@section('content')
@php
    $u          = auth()->user();
    $roleLabel  = ['superadmin'=>'Super Admin','admin'=>'Admin','hrd'=>'HRD','supervisor'=>'SPV/Manager','operator'=>'Operator','karyawan'=>'Karyawan'];
    $roleColor  = ['superadmin'=>'#e53e3e','admin'=>'#dd6b20','hrd'=>'#3182ce','supervisor'=>'#805ad5','operator'=>'#319795','karyawan'=>'#38a169'];
    $roleBg     = ['superadmin'=>'#fff5f5','admin'=>'#fffaf0','hrd'=>'#ebf8ff','supervisor'=>'#faf5ff','operator'=>'#e6fffa','karyawan'=>'#f0fff4'];
    $role       = $u->role ?? 'karyawan';
    $roleIcon   = ['superadmin'=>'fa-crown','admin'=>'fa-user-tie','hrd'=>'fa-user-nurse','supervisor'=>'fa-user-check','operator'=>'fa-headset','karyawan'=>'fa-user'];
    $initial    = strtoupper(substr($u->name, 0, 1));
@endphp

{{-- Alert --}}
@if(session('success_pass'))
<div style="background:#f0fff4;border:1px solid #9ae6b4;color:#276749;padding:12px 18px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
    <i class="fas fa-check-circle"></i> {{ session('success_pass') }}
</div>
@endif
@if(session('error_pass'))
<div style="background:#fff5f5;border:1px solid #fed7d7;color:#c53030;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error_pass') }}
</div>
@endif

{{-- Hero Header --}}
<div style="background:linear-gradient(135deg,#2d3748,#4a5568);border-radius:16px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
    <div style="width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,{{ $roleColor[$role] ?? '#4299e1' }},{{ $roleBg[$role] ?? '#ebf8ff' }});display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:800;color:white;flex-shrink:0;border:4px solid rgba(255,255,255,0.3);box-shadow:0 4px 20px rgba(0,0,0,0.3);">
        {{ $initial }}
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:22px;font-weight:800;color:white;margin-bottom:4px;">{{ $u->name }}</div>
        <div style="font-size:13px;color:rgba(255,255,255,0.65);margin-bottom:8px;"><i class="fas fa-envelope" style="margin-right:6px;"></i>{{ $u->email }}</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <span style="background:{{ $roleBg[$role] ?? '#f7fafc' }};color:{{ $roleColor[$role] ?? '#718096' }};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:800;display:inline-flex;align-items:center;gap:5px;">
                <i class="fas {{ $roleIcon[$role] ?? 'fa-user' }}"></i> {{ $roleLabel[$role] ?? ucfirst($role) }}
            </span>
            @if($karyawan)
            <span style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.85);padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:5px;">
                <i class="fas fa-id-card"></i> {{ $karyawan->nama }} &mdash; {{ $karyawan->department?->name ?? $karyawan->departemen }}
            </span>
            @endif
        </div>
    </div>
    <div style="text-align:right;color:rgba(255,255,255,0.55);font-size:11px;">
        <div><i class="fas fa-calendar-plus"></i> Bergabung</div>
        <div style="font-weight:700;color:rgba(255,255,255,0.85);font-size:13px;">{{ $u->created_at->translatedFormat('d F Y') }}</div>
    </div>
</div>

{{-- Layout 2 Kolom --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    {{-- KOLOM KIRI --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Edit Profil --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-user-edit" style="color:#4299e1;"></i> Edit Profil</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profil.nama') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $u->name) }}" required>
                        @error('name') <small style="color:#c53030;">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="{{ $u->email }}" disabled style="background:#f7fafc;color:#718096;">
                        <small style="color:#a0aec0;">Email tidak dapat diubah dari sini</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>

        {{-- Ganti Password --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-lock" style="color:#e53e3e;"></i> Ganti Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profil.password') }}">
                    @csrf
                    <div class="form-group">
                        <label>Password Lama</label>
                        <div style="position:relative;">
                            <input type="password" name="password_lama" id="pwdLama" class="form-control" required style="padding-right:40px;">
                            <i class="fas fa-eye" onclick="togglePwd('pwdLama',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#a0aec0;cursor:pointer;"></i>
                        </div>
                        @error('password_lama') <small style="color:#c53030;">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <div style="position:relative;">
                            <input type="password" name="password_baru" id="pwdBaru" class="form-control" required style="padding-right:40px;" oninput="checkStrength(this.value)">
                            <i class="fas fa-eye" onclick="togglePwd('pwdBaru',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#a0aec0;cursor:pointer;"></i>
                        </div>
                        <div id="strengthBar" style="height:4px;border-radius:2px;margin-top:6px;transition:all 0.3s;background:#e2e8f0;"></div>
                        <div id="strengthText" style="font-size:11px;color:#a0aec0;margin-top:3px;"></div>
                        @error('password_baru') <small style="color:#c53030;">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <div style="position:relative;">
                            <input type="password" name="password_baru_confirmation" id="pwdKonfirm" class="form-control" required style="padding-right:40px;" oninput="checkMatch()">
                            <i class="fas fa-eye" onclick="togglePwd('pwdKonfirm',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#a0aec0;cursor:pointer;"></i>
                        </div>
                        <div id="matchText" style="font-size:11px;margin-top:3px;"></div>
                    </div>
                    <button type="submit" class="btn btn-danger" style="width:100%;"><i class="fas fa-key"></i> Ganti Password</button>
                </form>
            </div>
        </div>

        {{-- Info Karyawan Terhubung --}}
        @if($karyawan)
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-id-badge" style="color:#38a169;"></i> Data Karyawan Terhubung</h6>
                <a href="{{ route('admin.karyawan.show', $karyawan) }}" style="font-size:11px;color:#4299e1;text-decoration:none;">Lihat Detail</a>
            </div>
            <div class="card-body" style="padding:0;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
                    @php
                        $infos = [
                            ['label'=>'NIK','value'=>$karyawan->nik,'icon'=>'fa-id-card'],
                            ['label'=>'Departemen','value'=>$karyawan->department?->name ?? $karyawan->departemen,'icon'=>'fa-building'],
                            ['label'=>'Jabatan','value'=>$karyawan->jabatan,'icon'=>'fa-briefcase'],
                            ['label'=>'Status','value'=>ucfirst($karyawan->status),'icon'=>'fa-circle'],
                            ['label'=>'Shift','value'=>$karyawan->shift?->nama_shift ?? '-','icon'=>'fa-clock'],
                            ['label'=>'Masuk Kerja','value'=>optional($karyawan->workhistories->sortBy('start_date')->first())->start_date?->translatedFormat('d M Y') ?? '-','icon'=>'fa-calendar'],
                        ];
                    @endphp
                    @foreach($infos as $info)
                    <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;{{ $loop->iteration % 2 == 0 ? 'border-left:1px solid #f0f0f0;' : '' }}">
                        <div style="font-size:10px;text-transform:uppercase;color:#a0aec0;letter-spacing:0.5px;margin-bottom:3px;">
                            <i class="fas {{ $info['icon'] }}"></i> {{ $info['label'] }}
                        </div>
                        <div style="font-weight:600;font-size:13px;color:#2d3748;">{{ $info['value'] ?: '-' }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- KOLOM KANAN --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Info login terakhir (semua role) --}}
        @php $lastLogin = $loginHistory->first(); @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div style="background:linear-gradient(135deg,#ebf8ff,#bee3f8);border-radius:12px;padding:16px 18px;">
                <div style="font-size:10px;text-transform:uppercase;color:#2b6cb0;letter-spacing:0.5px;margin-bottom:6px;font-weight:700;">Login Terakhir</div>
                <div style="font-size:14px;font-weight:800;color:#2c5282;">
                    {{ $lastLogin ? $lastLogin->login_at->translatedFormat('d M Y') : '-' }}
                </div>
                <div style="font-size:11px;color:#4299e1;margin-top:2px;">
                    {{ $lastLogin ? $lastLogin->login_at->format('H:i') . ' WIB' : '' }}
                </div>
            </div>
            <div style="background:linear-gradient(135deg,#f0fff4,#c6f6d5);border-radius:12px;padding:16px 18px;">
                <div style="font-size:10px;text-transform:uppercase;color:#276749;letter-spacing:0.5px;margin-bottom:6px;font-weight:700;">Sesi Aktif</div>
                <div style="font-size:14px;font-weight:800;color:#22543d;">{{ now()->translatedFormat('d M Y') }}</div>
                <div style="font-size:11px;color:#38a169;margin-top:2px;">{{ now()->format('H:i') }} WIB</div>
            </div>
        </div>

        {{-- Akses Cepat (semua role) --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-bolt" style="color:#d69e2e;"></i> Akses Cepat</h6>
            </div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                @if($karyawan)
                <a href="{{ route('portal.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#f7fafc;border-radius:10px;text-decoration:none;color:#2d3748;border:1px solid #e2e8f0;" onmouseover="this.style.background='#ebf8ff'" onmouseout="this.style.background='#f7fafc'">
                    <i class="fas fa-user-circle" style="font-size:20px;color:#4299e1;"></i>
                    <div>
                        <div style="font-size:12px;font-weight:700;">Portal Saya</div>
                        <div style="font-size:10px;color:#718096;">Absensi & cuti</div>
                    </div>
                </a>
                <a href="{{ route('admin.profil.jadwal') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#f7fafc;border-radius:10px;text-decoration:none;color:#2d3748;border:1px solid #e2e8f0;" onmouseover="this.style.background='#faf5ff'" onmouseout="this.style.background='#f7fafc'">
                    <i class="fas fa-calendar-week" style="font-size:20px;color:#805ad5;"></i>
                    <div>
                        <div style="font-size:12px;font-weight:700;">Jadwal Saya</div>
                        <div style="font-size:10px;color:#718096;">Lihat shift kerja</div>
                    </div>
                </a>
                @endif
                <a href="{{ route('admin.notifikasi.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#f7fafc;border-radius:10px;text-decoration:none;color:#2d3748;border:1px solid #e2e8f0;" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='#f7fafc'">
                    <i class="fas fa-bell" style="font-size:20px;color:#d69e2e;"></i>
                    <div>
                        <div style="font-size:12px;font-weight:700;">Notifikasi</div>
                        <div style="font-size:10px;color:#718096;">Pesan & alert</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Riwayat Login — hanya superadmin / admin --}}
        @if(in_array($role, ['superadmin','admin','hrd']))
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-sign-in-alt" style="color:#805ad5;"></i> Riwayat Login Terakhir</h6>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($loginHistory as $log)
                <div style="padding:10px 16px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;gap:8px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:{{ $log->status === 'success' ? '#f0fff4' : '#fff5f5' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas {{ $log->status === 'success' ? 'fa-check' : 'fa-times' }}" style="font-size:12px;color:{{ $log->status === 'success' ? '#38a169' : '#e53e3e' }};"></i>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#2d3748;">
                                {{ $log->login_at->translatedFormat('d M Y, H:i') }}
                            </div>
                            <div style="font-size:11px;color:#a0aec0;">
                                <i class="fas fa-desktop" style="font-size:9px;"></i> {{ $log->user_agent ? Str::limit($log->user_agent, 40) : '-' }}
                            </div>
                        </div>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:{{ $log->status === 'success' ? '#f0fff4' : '#fff5f5' }};color:{{ $log->status === 'success' ? '#276749' : '#c53030' }};">
                        {{ $log->status === 'success' ? 'BERHASIL' : 'GAGAL' }}
                    </span>
                </div>
                @empty
                <div style="padding:24px;text-align:center;color:#a0aec0;font-size:13px;">
                    Belum ada riwayat login
                </div>
                @endforelse
                @if($loginHistory->count() >= 5)
                <div style="padding:10px 16px;text-align:center;border-top:1px solid #f0f0f0;">
                    <a href="{{ route('admin.login-history.index') }}" style="font-size:12px;color:#4299e1;text-decoration:none;">Lihat semua &rarr;</a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Aktivitas Terakhir — hanya superadmin / admin / hrd --}}
        @if(in_array($role, ['superadmin','admin']))
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-history" style="color:#38a169;"></i> Aktivitas Sistem Terakhir</h6>
                <span style="font-size:10px;color:#a0aec0;">Hanya terlihat oleh Admin</span>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($auditLog as $log)
                @php
                    $actionIcon  = ['create'=>'fa-plus-circle','update'=>'fa-edit','delete'=>'fa-trash','approve'=>'fa-check-circle','reject'=>'fa-times-circle','import'=>'fa-file-upload'];
                    $actionColor = ['create'=>'#38a169','update'=>'#3182ce','delete'=>'#e53e3e','approve'=>'#38a169','reject'=>'#e53e3e','import'=>'#805ad5'];
                @endphp
                <div style="padding:10px 16px;border-bottom:1px solid #f0f0f0;display:flex;gap:10px;align-items:flex-start;">
                    <div style="width:28px;height:28px;border-radius:50%;background:#f7fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                        <i class="fas {{ $actionIcon[$log->action] ?? 'fa-circle' }}" style="font-size:11px;color:{{ $actionColor[$log->action] ?? '#718096' }};"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;color:#2d3748;line-height:1.4;">{{ $log->deskripsi }}</div>
                        <div style="font-size:11px;color:#a0aec0;margin-top:2px;">{{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:24px;text-align:center;color:#a0aec0;font-size:13px;">Belum ada aktivitas</div>
                @endforelse
                @if($auditLog->count() >= 10)
                <div style="padding:10px 16px;text-align:center;border-top:1px solid #f0f0f0;">
                    <a href="{{ route('admin.audit-log.index') }}" style="font-size:12px;color:#4299e1;text-decoration:none;">Lihat semua &rarr;</a>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>
@push('scripts')
<script>
function togglePwd(id, icon) {
    const el = document.getElementById(id);
    if (el.type === 'password') {
        el.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        el.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score  = 0;
    if (val.length >= 8)              score++;
    if (/[A-Z]/.test(val))            score++;
    if (/[0-9]/.test(val))            score++;
    if (/[^A-Za-z0-9]/.test(val))    score++;

    const configs = [
        {pct:0,  bg:'#e2e8f0', label:''},
        {pct:25, bg:'#e53e3e', label:'Sangat Lemah'},
        {pct:50, bg:'#dd6b20', label:'Lemah'},
        {pct:75, bg:'#d69e2e', label:'Cukup Kuat'},
        {pct:100,bg:'#38a169', label:'Kuat'},
    ];
    const cfg = configs[score] || configs[0];
    bar.style.width  = cfg.pct + '%';
    bar.style.background = cfg.bg;
    text.textContent = cfg.label;
    text.style.color = cfg.bg;
}

function checkMatch() {
    const baru    = document.getElementById('pwdBaru').value;
    const konfirm = document.getElementById('pwdKonfirm').value;
    const el      = document.getElementById('matchText');
    if (!konfirm) { el.textContent = ''; return; }
    if (baru === konfirm) {
        el.textContent = '✓ Password cocok';
        el.style.color = '#38a169';
    } else {
        el.textContent = '✗ Password tidak cocok';
        el.style.color = '#e53e3e';
    }
}
</script>
@endpush
@endsection

