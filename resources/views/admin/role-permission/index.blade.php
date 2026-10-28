@extends('layouts.admin')
@section('title','Kelola Role & Permission')
@section('page-title','Kelola Role & Permission')

@push('styles')
<style>
/* ── MODE TABS (Role / Per User) ── */
.mode-tabs {
    display:flex; gap:0; margin-bottom:20px;
    background:#f1f5f9; border-radius:12px; padding:4px;
}
.mode-tab {
    flex:1; padding:10px 18px; border-radius:9px; cursor:pointer;
    font-weight:700; font-size:13px; border:none; background:transparent;
    color:#64748b; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:8px;
}
.mode-tab.active { background:#fff; color:#2b6cb0; box-shadow:0 2px 8px rgba(0,0,0,0.08); }

/* ── ROLE TABS ── */
.role-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
.role-tab {
    padding:10px 20px; border-radius:10px; cursor:pointer; font-weight:700; font-size:13px;
    border:2px solid #e2e8f0; background:white; color:#4a5568; transition:all .2s;
    display:flex; align-items:center; gap:8px;
}
.role-tab:hover { border-color:#4299e1; color:#2b6cb0; }
.role-tab.active { background:#2b6cb0; color:white; border-color:#2b6cb0; }
.role-tab .user-count {
    background:rgba(255,255,255,0.25); border-radius:10px; padding:1px 7px; font-size:11px;
}
.role-tab:not(.active) .user-count { background:#e2e8f0; color:#718096; }

.perm-panel { display:none; }
.perm-panel.active { display:block; }

.kategori-block {
    background:white; border-radius:12px; margin-bottom:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.05); overflow:hidden;
}
.kategori-header {
    background:#f7fafc; padding:12px 16px; border-bottom:1px solid #e2e8f0;
    display:flex; align-items:center; justify-content:space-between;
}
.kategori-header h6 { margin:0; font-weight:800; color:#2d3748; font-size:13px; }
.kategori-toggle { font-size:12px; color:#4299e1; cursor:pointer; font-weight:600; user-select:none; }

.perm-grid {
    display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr));
    gap:0; padding:8px 12px;
}
.perm-item {
    display:flex; align-items:center; gap:10px; padding:10px 8px;
    border-radius:8px; cursor:pointer; transition:background .15s;
}
.perm-item:hover { background:#f7fafc; }
.perm-item input[type=checkbox] {
    width:16px; height:16px; cursor:pointer; accent-color:#2b6cb0; flex-shrink:0;
}
.perm-item label { font-size:13px; color:#4a5568; cursor:pointer; margin:0; line-height:1.3; }
.perm-item label small { display:block; font-size:10px; color:#a0aec0; font-family:monospace; }

.save-bar {
    /* Statis (bukan sticky) — daftar permission per role bisa sangat panjang
       (60+ item), sticky-bottom terhadap viewport membuat bar ini menimpa
       konten di tengah scroll alih-alih hanya muncul di akhir daftar. */
    margin-top:16px; background:white; border-top:2px solid #e2e8f0;
    padding:14px 20px; display:flex; justify-content:space-between; align-items:center;
    border-radius:0 0 14px 14px;
}

/* ── PER USER ── */
#panel-user { display:none; }
#panel-user.active { display:block; }

.user-list-item {
    display:flex; align-items:center; gap:12px; padding:12px 16px;
    border-radius:10px; cursor:pointer; transition:background .15s;
    border:2px solid transparent;
}
.user-list-item:hover { background:#f7fafc; border-color:#e2e8f0; }
.user-list-item.selected { background:#ebf8ff; border-color:#4299e1; }
.user-list-item .avatar {
    width:38px; height:38px; border-radius:50%; background:#e2e8f0;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:700; color:#4a5568; flex-shrink:0;
}
.user-list-item .badge-override {
    margin-left:auto; background:#f0fff4; border:1px solid #9ae6b4;
    color:#276749; border-radius:8px; padding:2px 8px; font-size:10px; font-weight:700;
}
.badge-role {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 10px; border-radius:8px; font-size:11px; font-weight:700;
}
.badge-role.superadmin { background:#faf5ff; color:#6b21a8; border:1px solid #e9d5ff; }
.badge-role.admin      { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
.badge-role.hrd        { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
.badge-role.supervisor { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.badge-role.operator   { background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; }
.badge-role.karyawan   { background:#f8fafc; color:#475569; border:1px solid #e2e8f0; }

/* Per-user permission item: 3 state — inherit / grant / deny */
.up-item {
    display:flex; align-items:center; gap:10px; padding:10px 14px;
    border-bottom:1px solid #f0f4f8; transition:all .15s;
    position:relative;
}
.up-item:last-child { border-bottom:none; }
.up-item:hover { background:#f8fafc; }
.up-item.state-grant  { background:#f0fdf4; border-left:3px solid #4ade80; }
.up-item.state-deny   { background:#fff5f5; border-left:3px solid #fc8181; }
.up-item.state-inherit{ border-left:3px solid transparent; }
.up-item .up-label { flex:1; font-size:13px; color:#2d3748; min-width:0; }
.up-item .up-label small { display:block; font-size:10px; color:#a0aec0; font-family:monospace; margin-top:1px; }
.up-item.state-grant .up-label  { color:#166534; }
.up-item.state-deny  .up-label  { color:#991b1b; }

/* Segmented pill for 3 states */
.up-seg {
    display:inline-flex; border-radius:8px; overflow:hidden;
    border:1.5px solid #e2e8f0; flex-shrink:0;
}
.up-seg-btn {
    padding:5px 13px; font-size:11px; font-weight:600; cursor:pointer;
    border:none; background:#f8fafc; color:#94a3b8;
    transition:all .15s; white-space:nowrap; line-height:1.3;
    display:flex; align-items:center; gap:5px;
}
.up-seg-btn + .up-seg-btn { border-left:1px solid #e2e8f0; }
.up-seg-btn:hover { background:#f1f5f9; color:#475569; }

/* Active states */
.up-seg-btn.active-inherit { background:#e2e8f0; color:#374151; font-weight:700; }
.up-seg-btn.active-grant   { background:#16a34a; color:#fff;    font-weight:700; }
.up-seg-btn.active-deny    { background:#dc2626; color:#fff;    font-weight:700; }

.role-dot {
    width:8px; height:8px; border-radius:50%; flex-shrink:0;
}

/* Counter chips */
.override-counter {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;
}
.override-counter.grant { background:#dcfce7; color:#166534; }
.override-counter.deny  { background:#fee2e2; color:#991b1b; }
.override-counter.clean { background:#f1f5f9; color:#64748b; }

/* Kategori header up */
.up-kat-header {
    background:#f7fafc; padding:10px 14px; border-bottom:1px solid #e2e8f0;
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
}
.up-kat-header h6 { margin:0; font-weight:800; color:#2d3748; font-size:13px; flex:1; min-width:100px; }
.up-kat-btns { display:flex; gap:4px; }
.up-kat-btn {
    padding:4px 11px; border-radius:6px; font-size:11px; font-weight:700;
    cursor:pointer; border:1.5px solid; display:inline-flex; align-items:center; gap:4px;
    transition:all .15s;
}
.up-kat-btn:hover { opacity:.85; transform:translateY(-1px); }
.up-kat-btn.btn-inherit { background:#f1f5f9; color:#475569; border-color:#cbd5e1; }
.up-kat-btn.btn-grant   { background:#dcfce7; color:#166534; border-color:#86efac; }
.up-kat-btn.btn-deny    { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#553c9a,#805ad5);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-shield-alt"></i> Kelola Role & Permission</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Atur hak akses per role sebagai template, atau kustomisasi langsung per user.</p>
    </div>
</div>

{{-- Mode toggle: Per Role / Per User --}}
<div class="mode-tabs">
    <button class="mode-tab active" id="btn-mode-role" onclick="switchMode('role')">
        <i class="fas fa-users-cog"></i> Per Role
        <span style="background:#dbeafe;color:#1d4ed8;border-radius:8px;padding:1px 8px;font-size:11px;">{{ array_sum($userCount->toArray()) }} user</span>
    </button>
    <button class="mode-tab" id="btn-mode-user" onclick="switchMode('user')">
        <i class="fas fa-user-shield"></i> Override Per User
        <span style="background:#dcfce7;color:#166534;border-radius:8px;padding:1px 8px;font-size:11px;" id="override-badge">{{ count($usersWithOverride) }} diatur</span>
    </button>
</div>

{{-- ═══════════════ MODE: PER ROLE ═══════════════ --}}
<div id="panel-role">

<div style="background:#fffbeb;border:1px solid #fef08a;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#854d0e;">
    <i class="fas fa-info-circle"></i>
    <strong>Template Role:</strong> Permission di sini berlaku untuk <em>semua</em> user dengan role tersebut, kecuali user yang punya override khusus di tab "Per User".
</div>

<div class="role-tabs">
    @foreach($roles as $roleKey => $roleLabel)
    <button class="role-tab {{ $loop->first ? 'active' : '' }}" onclick="switchRole('{{ $roleKey }}', this)">
        <i class="fas fa-user-tag"></i> {{ $roleLabel }}
        <span class="user-count">{{ $userCount[$roleKey] ?? 0 }} user</span>
    </button>
    @endforeach
</div>

@foreach($roles as $roleKey => $roleLabel)
<div id="panel-{{ $roleKey }}" class="perm-panel {{ $loop->first ? 'active' : '' }}">
    <div class="card" style="border-radius:14px;overflow:visible;">
        <div class="card-body" style="padding:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                <div style="font-size:13px;color:#718096;">
                    <span id="count-{{ $roleKey }}" style="font-weight:800;color:#2b6cb0;font-size:18px;">{{ count($rolePerms[$roleKey]) }}</span>
                    dari <strong>{{ $permissions->flatten()->count() }}</strong> permission aktif
                </div>
                <div style="display:flex;gap:8px;">
                    <button onclick="checkAll('{{ $roleKey }}', true)" class="btn btn-sm" style="background:#e6f4ea;color:#276749;font-weight:600;">
                        <i class="fas fa-check-square"></i> Centang Semua
                    </button>
                    <button onclick="checkAll('{{ $roleKey }}', false)" class="btn btn-sm" style="background:#fff5f5;color:#c53030;font-weight:600;">
                        <i class="far fa-square"></i> Hapus Semua
                    </button>
                </div>
            </div>

            @foreach($permissions as $kategori => $perms)
            <div class="kategori-block">
                <div class="kategori-header">
                    <h6><i class="fas fa-tag" style="color:#805ad5;margin-right:6px;"></i>{{ $kategori }}</h6>
                    <span class="kategori-toggle" onclick="toggleKategori('{{ $roleKey }}','{{ Str::slug($kategori) }}')">Pilih semua</span>
                </div>
                <div class="perm-grid" id="grid-{{ $roleKey }}-{{ Str::slug($kategori) }}">
                    @foreach($perms as $perm)
                    <div class="perm-item" onclick="togglePerm(this)">
                        <input type="checkbox"
                            id="perm-{{ $roleKey }}-{{ $perm->id }}"
                            data-role="{{ $roleKey }}"
                            data-id="{{ $perm->id }}"
                            value="{{ $perm->id }}"
                            {{ in_array($perm->name, $rolePerms[$roleKey]) ? 'checked' : '' }}
                            onclick="event.stopPropagation();"
                            onchange="updateCount('{{ $roleKey }}')">
                        <label for="perm-{{ $roleKey }}-{{ $perm->id }}">
                            {{ $perm->label }}
                            <small>{{ $perm->name }}</small>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="save-bar">
            <div style="font-size:13px;color:#718096;" id="save-status-{{ $roleKey }}"></div>
            <button onclick="savePermissions('{{ $roleKey }}')" class="btn btn-primary" style="padding:10px 28px;">
                <i class="fas fa-save"></i> Simpan <span style="opacity:.7;font-size:12px;">{{ $roleLabel }}</span>
            </button>
        </div>
    </div>
</div>
@endforeach

</div>{{-- /panel-role --}}

{{-- ═══════════════ MODE: PER USER ═══════════════ --}}
<div id="panel-user">

<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#1e40af;">
    <i class="fas fa-info-circle"></i>
    <strong>Override Per User:</strong> Pilih <span style="background:#dcfce7;color:#166534;font-weight:700;padding:1px 7px;border-radius:5px;">Aktifkan</span> untuk memberi permission tambahan di luar role,
    atau <span style="background:#fee2e2;color:#991b1b;font-weight:700;padding:1px 7px;border-radius:5px;">Nonaktifkan</span> untuk mencabut permission dari role.
    Biarkan <span style="background:#f1f5f9;color:#475569;font-weight:700;padding:1px 7px;border-radius:5px;">Ikut Role</span> jika tidak ada pengecualian.
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:16px;align-items:start;">

    {{-- Kolom kiri: daftar user --}}
    <div class="card" style="border-radius:14px;position:sticky;top:80px;">
        <div class="card-body" style="padding:12px;">
            <div style="font-size:12px;font-weight:700;color:#718096;margin-bottom:10px;padding:0 4px;">
                <i class="fas fa-users"></i> PILIH USER
            </div>
            <input type="text" id="user-search" placeholder="Cari nama / email..."
                onkeyup="filterUsers(this.value)"
                style="width:100%;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;margin-bottom:10px;">
            <div id="user-list" style="max-height:480px;overflow-y:auto;">
                @foreach($allUsers as $u)
                <div class="user-list-item {{ in_array($u->id, $usersWithOverride) ? '' : '' }}"
                     data-user-id="{{ $u->id }}"
                     data-name="{{ strtolower($u->name) }}"
                     data-email="{{ strtolower($u->email) }}"
                     onclick="selectUser({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ $u->role }}', this)">
                    <div class="avatar" style="background:{{ ['admin'=>'#fff7ed','hrd'=>'#ecfdf5','supervisor'=>'#eff6ff','operator'=>'#f5f3ff','karyawan'=>'#f8fafc'][$u->role] ?? '#e2e8f0' }};">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:#2d3748;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $u->name }}</div>
                        <div style="font-size:11px;color:#718096;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $u->email }}</div>
                    </div>
                    <span class="badge-role {{ $u->role }}">{{ ucfirst($u->role) }}</span>
                    @if(in_array($u->id, $usersWithOverride))
                    <span class="badge-override"><i class="fas fa-sliders-h"></i></span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Kolom kanan: permission editor --}}
    <div id="user-perm-panel">
        {{-- Empty state --}}
        <div id="user-perm-empty" class="card" style="border-radius:14px;">
            <div class="card-body" style="padding:48px;text-align:center;color:#a0aec0;">
                <i class="fas fa-user-shield" style="font-size:48px;display:block;margin-bottom:16px;color:#e2e8f0;"></i>
                <p style="font-size:14px;font-weight:600;color:#718096;">Pilih user di sebelah kiri</p>
                <p style="font-size:13px;">untuk mengatur permission khusus user tersebut.</p>
            </div>
        </div>

        {{-- Editor (hidden until user selected) --}}
        <div id="user-perm-editor" style="display:none;">
            <div class="card" style="border-radius:14px;overflow:visible;">
                <div class="card-body" style="padding:16px;">

                    {{-- User info bar --}}
                    <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#f8fafc;border-radius:12px;margin-bottom:16px;flex-wrap:wrap;border:1.5px solid #e2e8f0;">
                        <div id="up-avatar" style="width:46px;height:46px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#4a5568;flex-shrink:0;"></div>
                        <div style="flex:1;min-width:0;">
                            <div id="up-name" style="font-size:15px;font-weight:800;color:#2d3748;"></div>
                            <div id="up-email" style="font-size:12px;color:#718096;"></div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:11px;color:#718096;">Role:</span>
                                <span id="up-role-badge" class="badge-role"></span>
                                <span style="font-size:11px;color:#718096;">→ template <strong id="up-role-name" style="color:#2b6cb0;"></strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Bar atas: mirip Per Role (counter + bulk tombol) --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;">
                        {{-- Counter kiri --}}
                        <div style="font-size:13px;color:#718096;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <span>Override aktif:</span>
                            <span class="override-counter grant" id="ctr-grant" style="font-size:13px;"><i class="fas fa-check"></i> <span id="ctr-grant-num">0</span> ditambah</span>
                            <span class="override-counter deny"  id="ctr-deny"  style="font-size:13px;"><i class="fas fa-ban"></i>  <span id="ctr-deny-num">0</span> dicabut</span>
                        </div>
                        {{-- Tombol bulk kanan — mirip "Centang Semua / Hapus Semua" di Per Role --}}
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <button type="button" onclick="bulkSetAll('inherit')" class="btn btn-sm" style="background:#f1f5f9;color:#374151;font-weight:600;border:1.5px solid #cbd5e1;">
                                <i class="fas fa-undo"></i> Reset Semua ke Role
                            </button>
                            <button type="button" onclick="bulkSetAll('grant')" class="btn btn-sm" style="background:#dcfce7;color:#166534;font-weight:600;border:1.5px solid #86efac;">
                                <i class="fas fa-check-square"></i> Aktifkan Semua
                            </button>
                            <button type="button" onclick="bulkSetAll('deny')" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;font-weight:600;border:1.5px solid #fca5a5;">
                                <i class="fas fa-ban"></i> Nonaktifkan Semua
                            </button>
                        </div>
                    </div>

                    {{-- Keterangan singkat 3 state (compact) --}}
                    <div style="display:flex;gap:8px;font-size:12px;color:#718096;margin-bottom:12px;flex-wrap:wrap;align-items:center;">
                        <span style="font-weight:700;color:#4a5568;">Keterangan per baris:</span>
                        <span style="background:#e2e8f0;color:#374151;padding:2px 9px;border-radius:5px;font-weight:600;"><i class="fas fa-minus" style="font-size:9px;"></i> Ikut Role</span>
                        <span style="background:#16a34a;color:#fff;padding:2px 9px;border-radius:5px;font-weight:600;"><i class="fas fa-check" style="font-size:9px;"></i> Aktifkan</span>
                        <span style="background:#dc2626;color:#fff;padding:2px 9px;border-radius:5px;font-weight:600;"><i class="fas fa-ban" style="font-size:9px;"></i> Nonaktifkan</span>
                        <span style="color:#a0aec0;margin-left:4px;font-size:11px;">· Baris hijau = ditambah · Baris merah = dicabut</span>
                    </div>

                    <div id="up-perm-container"></div>

                </div>
                <div class="save-bar">
                    <div style="font-size:13px;color:#718096;" id="up-save-status"></div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="resetUserPerms()" class="btn btn-sm" style="background:#fff5f5;color:#c53030;font-weight:600;">
                            <i class="fas fa-undo"></i> Reset ke Default Role
                        </button>
                        <button onclick="saveUserPerms()" class="btn btn-primary" style="padding:10px 28px;">
                            <i class="fas fa-save"></i> Simpan Override
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>{{-- /panel-user --}}
@push('scripts')
<script>
const CSRF   = '{{ csrf_token() }}';
@php
$_permFlat = $permissions->map(fn($perms, $kat) => $perms->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'label'=>$p->label,'kategori'=>$p->kategori])->values())->values()->flatten(1);
@endphp
const PERMISSIONS = @json($_permFlat);
const ROLE_LABELS = @json($roles);

// ── MODE SWITCH ───────────────────────────────────────────────────────────────
function switchMode(mode) {
    document.getElementById('panel-role').style.display = mode === 'role' ? 'block' : 'none';
    document.getElementById('panel-user').style.display = mode === 'user' ? 'block' : 'none';
    document.getElementById('btn-mode-role').classList.toggle('active', mode === 'role');
    document.getElementById('btn-mode-user').classList.toggle('active', mode === 'user');
}

// ── PER ROLE ──────────────────────────────────────────────────────────────────
function switchRole(role, btn) {
    document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.perm-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('panel-' + role).classList.add('active');
}
function togglePerm(item) {
    const cb = item.querySelector('input[type=checkbox]');
    cb.checked = !cb.checked;
    updateCount(cb.dataset.role);
}
function updateCount(role) {
    const checked = document.querySelectorAll(`input[data-role="${role}"]:checked`).length;
    document.getElementById('count-' + role).textContent = checked;
}
function checkAll(role, state) {
    document.querySelectorAll(`input[data-role="${role}"]`).forEach(cb => cb.checked = state);
    updateCount(role);
}
function toggleKategori(role, slug) {
    const grid = document.getElementById(`grid-${role}-${slug}`);
    if (!grid) return;
    const cbs = grid.querySelectorAll(`input[data-role="${role}"]`);
    const allChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
    updateCount(role);
}
function savePermissions(role) {
    const ids = [...document.querySelectorAll(`input[data-role="${role}"]:checked`)].map(cb => cb.value);
    const statusEl = document.getElementById('save-status-' + role);
    statusEl.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...';
    const form = new FormData();
    ids.forEach(id => form.append('permissions[]', id));
    form.append('_token', CSRF);
    form.append('_method', 'PUT');
    fetch(`/admin/role-permission/${role}`, { method: 'POST', body: form })
    .then(r => r.json())
    .then(data => {
        statusEl.innerHTML = data.success
            ? `<span style="color:#276749"><i class="fas fa-check-circle"></i> ${data.message}</span>`
            : `<span style="color:#c53030"><i class="fas fa-times-circle"></i> Gagal menyimpan.</span>`;
        setTimeout(() => statusEl.innerHTML = '', 4000);
    })
    .catch(() => { statusEl.innerHTML = '<span style="color:#c53030">Terjadi kesalahan.</span>'; });
}

// ── PER USER ──────────────────────────────────────────────────────────────────
let currentUserId   = null;
let currentRolePerms = [];
let upState = {}; // permId -> 'inherit' | 'grant' | 'deny'

function filterUsers(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#user-list .user-list-item').forEach(el => {
        const match = el.dataset.name.includes(q) || el.dataset.email.includes(q);
        el.style.display = match ? 'flex' : 'none';
    });
}

function selectUser(userId, name, email, role, el) {
    document.querySelectorAll('.user-list-item').forEach(e => e.classList.remove('selected'));
    el.classList.add('selected');
    currentUserId = userId;

    document.getElementById('up-avatar').textContent = name.charAt(0).toUpperCase();
    document.getElementById('up-avatar').style.background = {'admin':'#fff7ed','hrd':'#ecfdf5','supervisor':'#eff6ff','operator':'#f5f3ff','karyawan':'#f8fafc'}[role] || '#e2e8f0';
    document.getElementById('up-name').textContent  = name;
    document.getElementById('up-email').textContent = email;
    document.getElementById('up-role-badge').textContent = role.charAt(0).toUpperCase() + role.slice(1);
    document.getElementById('up-role-badge').className = 'badge-role ' + role;
    document.getElementById('up-role-name').textContent = ROLE_LABELS[role] || role;

    document.getElementById('user-perm-empty').style.display  = 'none';
    document.getElementById('user-perm-editor').style.display = 'block';
    document.getElementById('up-perm-container').innerHTML    = '<div style="text-align:center;padding:20px;color:#a0aec0;"><i class="fas fa-circle-notch fa-spin"></i> Memuat...</div>';

    fetch(`/admin/user-permission/${userId}`)
    .then(r => r.json())
    .then(data => {
        currentRolePerms = data.rolePerms;
        upState = {};
        PERMISSIONS.forEach(p => {
            if (data.denied.includes(p.name))        upState[p.id] = 'deny';
            else if (data.granted.includes(p.name))  upState[p.id] = 'grant';
            else                                      upState[p.id] = 'inherit';
        });
        renderUserPerms();
        updateCounters();
    });
}

function renderUserPerms() {
    // Group by kategori
    const byKat = {};
    PERMISSIONS.forEach(p => {
        if (!byKat[p.kategori]) byKat[p.kategori] = [];
        byKat[p.kategori].push(p);
    });

    let html = '';
    for (const [kat, perms] of Object.entries(byKat)) {
        const katSlug  = kat.replace(/[^a-z0-9]/gi, '-').toLowerCase();
        const grantCnt = perms.filter(p => (upState[p.id]||'inherit') === 'grant').length;
        const denyCnt  = perms.filter(p => (upState[p.id]||'inherit') === 'deny').length;
        const katBadge = grantCnt > 0 || denyCnt > 0
            ? `<span style="background:#faf5ff;color:#7c3aed;border:1px solid #c4b5fd;border-radius:10px;padding:1px 8px;font-size:10px;font-weight:700;margin-left:6px;">
                ${grantCnt > 0 ? `+${grantCnt}` : ''}${grantCnt > 0 && denyCnt > 0 ? ' · ' : ''}${denyCnt > 0 ? `−${denyCnt}` : ''}
               </span>`
            : '';
        html += `<div class="kategori-block" id="kat-block-${katSlug}">
            <div class="up-kat-header">
                <h6><i class="fas fa-tag" style="color:#805ad5;margin-right:6px;"></i>${kat}
                    <span style="font-weight:400;color:#a0aec0;font-size:11px;">(${perms.length})</span>
                    ${katBadge}
                </h6>
                <div class="up-kat-btns">
                    <button type="button" onclick="bulkSetKat('inherit','${katSlug}')" class="up-kat-btn btn-inherit" title="Set semua di kategori ini ke Ikut Role">
                        <i class="fas fa-minus" style="font-size:9px;"></i> Ikut Role
                    </button>
                    <button type="button" onclick="bulkSetKat('grant','${katSlug}')" class="up-kat-btn btn-grant" title="Aktifkan semua di kategori ini">
                        <i class="fas fa-check" style="font-size:9px;"></i> Aktifkan
                    </button>
                    <button type="button" onclick="bulkSetKat('deny','${katSlug}')" class="up-kat-btn btn-deny" title="Nonaktifkan semua di kategori ini">
                        <i class="fas fa-ban" style="font-size:9px;"></i> Nonaktifkan
                    </button>
                </div>
            </div>
            <div data-kat="${katSlug}">`;
        perms.forEach(p => {
            const inRole   = currentRolePerms.includes(p.name);
            const state    = upState[p.id] || 'inherit';
            const dotColor = inRole ? '#4ade80' : '#cbd5e1';
            const dotTitle = inRole ? '✓ Ada di template role' : '✗ Tidak ada di template role';
            const roleHint = inRole
                ? `<span style="font-size:10px;color:#16a34a;font-weight:600;"><i class="fas fa-circle" style="font-size:7px;"></i> dari role</span>`
                : `<span style="font-size:10px;color:#94a3b8;"><i class="far fa-circle" style="font-size:7px;"></i> tidak di role</span>`;
            html += `<div class="up-item state-${state}" data-perm-id="${p.id}" data-kat-item="${katSlug}" id="upitem-${p.id}">
                <div class="up-label">
                    ${p.label}
                    <small>${p.name}</small>
                </div>
                ${roleHint}
                <div class="up-seg" data-perm-id="${p.id}">
                    <button type="button" class="up-seg-btn ${state==='inherit'?'active-inherit':''}"
                        title="Ikut template role — tidak ada pengecualian"
                        onclick="setUpState(${p.id},'inherit',this.closest('.up-seg'))">
                        <i class="fas fa-minus" style="font-size:9px;"></i> Ikut Role
                    </button>
                    <button type="button" class="up-seg-btn ${state==='grant'?'active-grant':''}"
                        title="Aktifkan: beri permission ini meskipun role tidak punya"
                        onclick="setUpState(${p.id},'grant',this.closest('.up-seg'))">
                        <i class="fas fa-check" style="font-size:9px;"></i> Aktifkan
                    </button>
                    <button type="button" class="up-seg-btn ${state==='deny'?'active-deny':''}"
                        title="Nonaktifkan: cabut permission ini meskipun role punya"
                        onclick="setUpState(${p.id},'deny',this.closest('.up-seg'))">
                        <i class="fas fa-ban" style="font-size:9px;"></i> Nonaktifkan
                    </button>
                </div>
            </div>`;
        });
        html += `</div></div>`;
    }
    document.getElementById('up-perm-container').innerHTML = html;
}

function setUpState(permId, state, segEl) {
    upState[permId] = state;
    applySegStyle(segEl, state);
    // update row background
    const row = document.getElementById('upitem-' + permId);
    if (row) {
        row.classList.remove('state-inherit','state-grant','state-deny');
        row.classList.add('state-' + state);
    }
    updateCounters();
}

function saveUserPerms() {
    if (!currentUserId) return;
    const granted = [], denied = [];
    Object.entries(upState).forEach(([id, state]) => {
        if (state === 'grant') granted.push(id);
        if (state === 'deny')  denied.push(id);
    });

    const statusEl = document.getElementById('up-save-status');
    statusEl.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...';

    const body = new FormData();
    body.append('_token', CSRF);
    granted.forEach(id => body.append('granted[]', id));
    denied.forEach(id  => body.append('denied[]',  id));

    fetch(`/admin/user-permission/${currentUserId}`, { method: 'POST', body })
    .then(r => r.json())
    .then(data => {
        statusEl.innerHTML = data.success
            ? `<span style="color:#276749"><i class="fas fa-check-circle"></i> ${data.message}</span>`
            : `<span style="color:#c53030"><i class="fas fa-times-circle"></i> Gagal.</span>`;
        setTimeout(() => statusEl.innerHTML = '', 4000);

        // Update badge di list user
        const listItem = document.querySelector(`.user-list-item[data-user-id="${currentUserId}"]`);
        const hasOverride = granted.length > 0 || denied.length > 0;
        const existingBadge = listItem?.querySelector('.badge-override');
        if (hasOverride && !existingBadge && listItem) {
            listItem.insertAdjacentHTML('beforeend', '<span class="badge-override"><i class="fas fa-sliders-h"></i></span>');
        } else if (!hasOverride && existingBadge) {
            existingBadge.remove();
        }

        // Update counter badge
        const allOverrides = document.querySelectorAll('#user-list .badge-override').length;
        document.getElementById('override-badge').textContent = allOverrides + ' diatur';
    })
    .catch(() => { statusEl.innerHTML = '<span style="color:#c53030">Terjadi kesalahan.</span>'; });
}

function resetUserPerms() {
    if (!currentUserId) return;
    if (!confirm('Reset semua override untuk user ini?\nUser akan mengikuti template role sepenuhnya.')) return;
    PERMISSIONS.forEach(p => upState[p.id] = 'inherit');
    renderUserPerms();
    saveUserPerms();
}

// ── UPDATE COUNTER ────────────────────────────────────────────────────────────
function updateCounters() {
    const grantCnt = Object.values(upState).filter(s => s === 'grant').length;
    const denyCnt  = Object.values(upState).filter(s => s === 'deny').length;
    document.getElementById('ctr-grant').innerHTML = `<i class="fas fa-check"></i> ${grantCnt} ditambah`;
    document.getElementById('ctr-deny').innerHTML  = `<i class="fas fa-ban"></i> ${denyCnt} dicabut`;
    document.getElementById('ctr-grant').className = 'override-counter ' + (grantCnt > 0 ? 'grant' : 'clean');
    document.getElementById('ctr-deny').className  = 'override-counter '  + (denyCnt  > 0 ? 'deny'  : 'clean');
}

// ── BULK SET SEMUA PERMISSION ─────────────────────────────────────────────────
function bulkSetAll(state) {
    const labels = {'inherit':'Ikut Role semua','grant':'Aktifkan SEMUA','deny':'Nonaktifkan SEMUA'};
    if (state !== 'inherit') {
        if (!confirm(`Set semua permission menjadi "${labels[state]}"?\nIni akan mengubah seluruh ${PERMISSIONS.length} permission sekaligus.`)) return;
    }
    PERMISSIONS.forEach(p => {
        upState[p.id] = state;
        const seg = document.querySelector(`.up-seg[data-perm-id="${p.id}"]`);
        if (seg) applySegStyle(seg, state);
        const row = document.getElementById('upitem-' + p.id);
        if (row) { row.classList.remove('state-inherit','state-grant','state-deny'); row.classList.add('state-' + state); }
    });
    updateCounters();
}

// ── BULK SET PER KATEGORI ─────────────────────────────────────────────────────
function bulkSetKat(state, katSlug) {
    const items = document.querySelectorAll(`[data-kat-item="${katSlug}"]`);
    items.forEach(item => {
        const permId = parseInt(item.dataset.permId);
        upState[permId] = state;
        const seg = item.querySelector('.up-seg');
        if (seg) applySegStyle(seg, state);
        item.classList.remove('state-inherit','state-grant','state-deny');
        item.classList.add('state-' + state);
    });
    updateCounters();
}

// Helper: update visual state of a segmented button group
function applySegStyle(segEl, state) {
    const btns = segEl.querySelectorAll('.up-seg-btn');
    btns[0].className = 'up-seg-btn' + (state === 'inherit' ? ' active-inherit' : '');
    btns[1].className = 'up-seg-btn' + (state === 'grant'   ? ' active-grant'   : '');
    btns[2].className = 'up-seg-btn' + (state === 'deny'    ? ' active-deny'    : '');
}
</script>
@endpush
@endsection

