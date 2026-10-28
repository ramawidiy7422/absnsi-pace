@php $isKaryawan = auth()->user()?->role === 'karyawan'; @endphp
<div class="topbar">
    <div class="topbar-left">
        <div class="sidebar-toggle" onclick="toggleSidebar()" title="Buka/Tutup Sidebar" id="sidebarToggle">
            <i class="fas fa-bars" id="toggleIcon"></i>
        </div>
        <h5 id="pageTitleText">@yield('page-title', 'Dashboard')</h5>
    </div>
    <div class="topbar-right">
        <div class="topbar-clock">
            <span class="clock-time" id="topbarTime">00:00:00</span>
            <span class="clock-date" id="topbarDate"></span>
        </div>

        {{-- Approval Badge (cuti/izin/lembur pending) --}}
        @php
            $totalApprovalPending = 0;
            if (auth()->user()->hasPermission('cuti.approve'))   $totalApprovalPending += \App\Models\Leaverequest::where('status','pending')->count();
            if (auth()->user()->hasPermission('izin.approve'))   $totalApprovalPending += \App\Models\Izin::where('status','pending')->count();
            if (auth()->user()->hasPermission('lembur.approve')) $totalApprovalPending += \App\Models\Lemburan::where('status','pending')->count();
        @endphp
        @if($totalApprovalPending > 0)
        <div style="position:relative;display:flex;align-items:center;gap:6px;">
            <a href="{{ route('admin.leaverequest.index', ['status'=>'pending']) }}"
               title="{{ $totalApprovalPending }} pengajuan menunggu persetujuan"
               style="display:flex;align-items:center;gap:6px;padding:5px 12px;background:rgba(229,62,62,0.15);border:1px solid rgba(229,62,62,0.3);border-radius:20px;text-decoration:none;color:#e53e3e;font-size:12px;font-weight:700;white-space:nowrap;">
                <i class="fas fa-clock" style="font-size:12px;"></i>
                <span>{{ $totalApprovalPending }} Pending</span>
            </a>
        </div>
        @endif

        {{-- Bell Notifikasi --}}
        <div class="notif-bell-wrapper" style="position:relative;">
            <div class="notif-bell" id="notifBellBtn" onclick="toggleNotifDropdown()" title="Notifikasi"
                 style="cursor:pointer;position:relative;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:rgba(255,255,255,0.1);transition:background .2s;">
                <i class="fas fa-bell" style="font-size:16px;"></i>
                <span id="notifBadge" style="display:none;position:absolute;top:4px;right:4px;background:#e53e3e;color:white;font-size:9px;font-weight:800;min-width:16px;height:16px;border-radius:8px;text-align:center;line-height:16px;padding:0 3px;"></span>
            </div>
            <div id="notifDropdown" style="display:none;position:fixed;top:60px;right:12px;width:340px;max-width:calc(100vw - 24px);background:white;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.15);z-index:999;overflow:hidden;border:1px solid #f0f0f0;">
                {{-- Header --}}
                <div style="padding:14px 16px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-weight:700;font-size:14px;color:#2d3748;"><i class="fas fa-bell" style="color:#4299e1;margin-right:6px;"></i>Notifikasi</div>
                    <button onclick="markAllRead()" id="markAllBtn" style="display:none;font-size:11px;color:#4299e1;background:none;border:none;cursor:pointer;font-weight:600;">Tandai semua dibaca</button>
                </div>
                {{-- List --}}
                <div id="notifList" style="max-height:320px;overflow-y:auto;">
                    <div style="text-align:center;padding:30px;color:#a0aec0;font-size:13px;">
                        <i class="fas fa-spinner fa-spin"></i> Memuat...
                    </div>
                </div>
                {{-- Footer --}}
                <div style="padding:10px 16px;border-top:1px solid #f0f0f0;text-align:center;">
                    <a href="{{ $isKaryawan ? route('portal.notifikasi') : route('admin.notifikasi.index') }}" style="font-size:12px;color:#4299e1;font-weight:600;text-decoration:none;">
                        Lihat semua notifikasi →
                    </a>
                </div>
            </div>
        </div>

        {{-- Dark Mode Toggle --}}
        <button id="dmToggle" onclick="toggleDarkMode()" title="Ganti Tema"
            style="width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--notif-bg);color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:15px;transition:all 0.2s;flex-shrink:0;">
            <i id="dmIcon" class="fas fa-moon"></i>
        </button>

        {{-- Ganti Password (portal karyawan) --}}
        @if(auth()->user()->role === 'karyawan')
        <a href="{{ route('portal.ganti-password') }}" title="Ganti Password"
           style="width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--notif-bg);color:var(--text-sub);display:flex;align-items:center;justify-content:center;font-size:14px;text-decoration:none;flex-shrink:0;">
            <i class="fas fa-key"></i>
        </a>
        @endif

        {{-- User Info --}}
        @php $fotoWajahUser = auth()->user()->karyawan?->foto_wajah; @endphp
        <a href="{{ $isKaryawan ? route('portal.profil-lengkap') : route('admin.profil') }}" class="user-info" style="text-decoration:none;" title="Profil Akun">
            @if($fotoWajahUser)
                <div class="user-avatar" style="overflow:hidden;padding:0;">
                    <img src="{{ asset('storage/'.$fotoWajahUser) }}" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.outerHTML='<div class=&quot;user-avatar&quot;>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>'">
                </div>
            @else
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
            <div style="display:flex;flex-direction:column;line-height:1.2;min-width:0;">
                <span class="user-name">{{ auth()->user()->name }}</span>
                @php
                    $roleLabel = ['superadmin'=>'Super Admin','admin'=>'Admin','hrd'=>'HRD','supervisor'=>'SPV/Manager'];
                    $roleColor = ['superadmin'=>'#e53e3e','admin'=>'#dd6b20','hrd'=>'#3182ce','supervisor'=>'#805ad5'];
                    $role = auth()->user()->role;
                @endphp
                <span class="user-role-label" style="font-size:10px;font-weight:700;color:{{ $roleColor[$role] ?? '#718096' }};text-transform:uppercase;letter-spacing:0.5px;">
                    {{ $roleLabel[$role] ?? ucfirst($role) }}
                </span>
            </div>
        </a>

        <form method="POST" action="{{ route('logout') }}" style="display:inline;" onsubmit="clearPopupStorage()">
            @csrf
            <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
        <script>
        function clearPopupStorage() {
            Object.keys(localStorage).filter(function(k){ return k.startsWith('popup_'); }).forEach(function(k){ localStorage.removeItem(k); });
            Object.keys(sessionStorage).filter(function(k){ return k.startsWith('popup_'); }).forEach(function(k){ sessionStorage.removeItem(k); });
        }
        </script>
    </div>
</div>
