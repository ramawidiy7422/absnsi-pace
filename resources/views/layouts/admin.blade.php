<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - PT Global Multi Pharmalab</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; min-height: 100vh; }

        /* ===== SIDEBAR ===== */
        :root { --sb-width: 260px; --sb-collapsed: 68px; --sb-transition: 0.25s ease; }

        .sidebar {
            width: var(--sb-width);
            background: linear-gradient(180deg, #1a1f36 0%, #2d3561 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0;
            z-index: 100;
            transition: width var(--sb-transition);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .sidebar.collapsed { width: var(--sb-collapsed); }

        /* Brand */
        .sidebar-brand {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 72px;
            flex-shrink: 0;
        }
        .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4299e1, #667eea);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .brand-text { overflow: hidden; white-space: nowrap; }
        .brand-text h4 { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3; }
        .brand-text small { color: #a0aec0; font-size: 10px; display: block; }

        /* Toggle button — dipindah ke topbar, bukan di dalam sidebar */
        .sidebar-toggle {
            width: 34px; height: 34px;
            background: #f7fafc;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 14px;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .sidebar-toggle:hover { background: #edf2f7; color: #2d3748; }

        /* Menu */
        .sidebar-menu { padding: 12px 0; flex: 1; overflow-y: auto; overflow-x: hidden; }
        .sidebar-menu::-webkit-scrollbar { width: 3px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }

        .menu-label {
            padding: 8px 18px 4px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #4a5568;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity var(--sb-transition), padding var(--sb-transition);
        }
        .sidebar.collapsed .menu-label { opacity: 0; padding: 4px 0; }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 13px;
            white-space: nowrap;
            position: relative;
        }
        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(255,255,255,0.08);
            color: white;
            border-right: 3px solid #4299e1;
        }
        .menu-item a i {
            width: 20px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .menu-text {
            overflow: hidden;
            transition: opacity var(--sb-transition), max-width var(--sb-transition);
            max-width: 200px;
        }
        .sidebar.collapsed .menu-text { opacity: 0; max-width: 0; }

        /* Tooltip saat collapsed — pakai JS untuk posisi fixed */
        .menu-item { position: relative; }
        .sidebar-tooltip {
            position: fixed;
            background: #1a1f36;
            color: #fff;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            z-index: 9999;
        }
        .sidebar-tooltip.show { opacity: 1; }

        /* Divider */
        .menu-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.06);
            margin: 6px 16px;
        }

        /* Footer sidebar */
        .sidebar-footer {
            padding: 12px 18px;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 11px;
            color: #4a5568;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity var(--sb-transition);
        }
        .sidebar.collapsed .sidebar-footer { opacity: 0; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sb-width);
            flex: 1;
            transition: margin-left var(--sb-transition);
        }
        .main-content.expanded { margin-left: var(--sb-collapsed); }

        .topbar {
            background: white;
            padding: 13px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .topbar h5 { font-size: 17px; font-weight: 600; color: #2d3748; }
        .topbar-breadcrumb { font-size: 11px; color: #a0aec0; margin-top: 1px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-clock {
            display: flex; flex-direction: column; align-items: flex-end;
            background: #f7fafc; border-radius: 8px; padding: 5px 12px;
            border: 1px solid #e2e8f0;
        }
        .topbar-clock .clock-time { font-size: 16px; font-weight: 700; color: #2d3748; letter-spacing: 1px; line-height: 1; }
        .topbar-clock .clock-date { font-size: 10px; color: #a0aec0; margin-top: 2px; }

        .user-info {
            display: flex; align-items: center; gap: 8px;
            background: #f7fafc; border-radius: 8px;
            padding: 6px 12px; font-size: 12px; color: #4a5568;
        }
        .user-avatar {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #4299e1, #667eea);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 12px; font-weight: 700;
        }

        .btn-logout {
            background: #e53e3e; color: white; border: none;
            padding: 7px 14px; border-radius: 6px;
            cursor: pointer; font-size: 12px;
            text-decoration: none;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-logout:hover { background: #c53030; }

        .content-area { padding: 22px; }

        /* ===== CARDS ===== */
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px; }
        .card-header { padding: 14px 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .card-header h6 { font-weight: 600; color: #2d3748; margin: 0; }
        .card-body { padding: 20px; }

        /* Stat Cards */
        .stat-card { border-radius: 12px; padding: 20px; color: white; }
        .stat-card.blue   { background: linear-gradient(135deg, #4299e1, #2b6cb0); }
        .stat-card.green  { background: linear-gradient(135deg, #48bb78, #276749); }
        .stat-card.yellow { background: linear-gradient(135deg, #ed8936, #c05621); }
        .stat-card.red    { background: linear-gradient(135deg, #fc8181, #c53030); }
        .stat-card .stat-number { font-size: 36px; font-weight: 700; }
        .stat-card .stat-label  { font-size: 13px; opacity: 0.9; }
        .stat-card .stat-icon   { font-size: 36px; opacity: 0.3; }

        /* Table */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        thead th { background: #f7fafc; padding: 12px; text-align: left; font-weight: 600; color: #4a5568; border-bottom: 2px solid #e2e8f0; }
        tbody td { padding: 12px; border-bottom: 1px solid #f0f0f0; color: #4a5568; }
        tbody tr:hover { background: #f7fafc; }

        /* Buttons */
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-primary   { background: #4299e1; color: white; } .btn-primary:hover   { background: #2b6cb0; }
        .btn-success   { background: #48bb78; color: white; } .btn-success:hover   { background: #276749; }
        .btn-warning   { background: #ed8936; color: white; } .btn-warning:hover   { background: #c05621; }
        .btn-danger    { background: #fc8181; color: white; } .btn-danger:hover    { background: #c53030; }
        .btn-secondary { background: #718096; color: white; } .btn-secondary:hover { background: #4a5568; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        /* Badges */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-hadir    { background: #c6f6d5; color: #276749; }
        .badge-terlambat{ background: #feebc8; color: #c05621; }
        .badge-izin     { background: #bee3f8; color: #2c5282; }
        .badge-sakit    { background: #fed7e2; color: #97266d; }
        .badge-alpha    { background: #fed7e2; color: #c53030; }
        .badge-aktif    { background: #c6f6d5; color: #276749; }
        .badge-nonaktif { background: #fed7e2; color: #c53030; }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; color: #4a5568; }
        .form-control { width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; color: #2d3748; }
        .form-control:focus { outline: none; border-color: #4299e1; box-shadow: 0 0 0 3px rgba(66,153,225,0.15); }
        .form-select { width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }

        /* Alert */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #c6f6d5; color: #276749; border: 1px solid #9ae6b4; }
        .alert-danger  { background: #fed7e2; color: #c53030; border: 1px solid #feb2b2; }

        /* Pagination */
        .pagination { display: flex; gap: 5px; justify-content: center; margin-top: 15px; }
        .pagination a, .pagination span { padding: 6px 12px; border-radius: 4px; font-size: 13px; text-decoration: none; border: 1px solid #e2e8f0; color: #4a5568; }
        .pagination .active span { background: #4299e1; color: white; border-color: #4299e1; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-capsules"></i>
            </div>
            <div class="brand-text">
                <h4>PT Global Multi<br>Pharmalab</h4>
                <small>Sistem Absensi Digital</small>
            </div>
        </div>

        <!-- Menu -->
        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>

            <div class="menu-item" data-tooltip="Dashboard">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </div>

            <div class="menu-item" data-tooltip="Terminal Absensi">
                <a href="{{ route('kiosk.index') }}" target="_blank">
                    <i class="fas fa-id-card-alt"></i>
                    <span class="menu-text">Terminal Absensi</span>
                </a>
            </div>

            <hr class="menu-divider">
            <div class="menu-label">Data Master</div>

            <div class="menu-item" data-tooltip="Karyawan">
                <a href="{{ route('admin.karyawan.index') }}" class="{{ request()->routeIs('admin.karyawan*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Karyawan</span>
                </a>
            </div>

            <div class="menu-item" data-tooltip="Shift Kerja">
                <a href="{{ route('admin.shift.index') }}" class="{{ request()->routeIs('admin.shift*') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span class="menu-text">Shift Kerja</span>
                </a>
            </div>

            <hr class="menu-divider">
            <div class="menu-label">Absensi</div>

            <div class="menu-item" data-tooltip="Data Absensi">
                <a href="{{ route('admin.absensi.index') }}" class="{{ request()->routeIs('admin.absensi*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="menu-text">Data Absensi</span>
                </a>
            </div>

            <div class="menu-item" data-tooltip="Laporan Absensi">
                <a href="{{ route('admin.laporan.index') }}" class="{{ request()->routeIs('admin.laporan.index') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">Laporan Absensi</span>
                </a>
            </div>

            <div class="menu-item" data-tooltip="Rekap Bulanan">
                <a href="{{ route('admin.laporan.rekap-bulanan') }}" class="{{ request()->routeIs('admin.laporan.rekap-bulanan') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i>
                    <span class="menu-text">Rekap Bulanan</span>
                </a>
            </div>

            @if(auth()->user()->role === 'admin')
            <hr class="menu-divider">
            <div class="menu-label">Pengaturan</div>

            <div class="menu-item" data-tooltip="Manajemen User">
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span class="menu-text">Manajemen User</span>
                </a>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="sidebar-footer">
            &copy; {{ date('Y') }} PT Global Multi Pharmalab
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content" id="mainContent">
        <div class="topbar">
            <div class="topbar-left">
                <div class="sidebar-toggle" onclick="toggleSidebar()" title="Buka/Tutup Sidebar" id="sidebarToggle">
                    <i class="fas fa-bars" id="toggleIcon"></i>
                </div>
                <h5>@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="topbar-right">
                <div class="topbar-clock">
                    <span class="clock-time" id="topbarTime">00:00:00</span>
                    <span class="clock-date" id="topbarDate"></span>
                </div>
                <a href="{{ route('admin.profil') }}" class="user-info" style="text-decoration:none;" title="Profil Akun">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <span>{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>

    <!-- Global tooltip -->
    <div class="sidebar-tooltip" id="sidebarTooltip"></div>

    <script>
    // Jam real-time di topbar
    function updateTopbarClock() {
        const now   = new Date();
        const jam   = now.toLocaleTimeString('id-ID');
        const hari  = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
        const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const tgl   = `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`;
        document.getElementById('topbarTime').textContent = jam;
        document.getElementById('topbarDate').textContent = tgl;
    }
    updateTopbarClock();
    setInterval(updateTopbarClock, 1000);

    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const tooltip     = document.getElementById('sidebarTooltip');

    // Restore state dari localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }

    function toggleSidebar() {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded', isCollapsed);
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }

    // Tooltip untuk menu saat collapsed
    document.querySelectorAll('.menu-item[data-tooltip]').forEach(item => {
        const label = item.dataset.tooltip;
        item.addEventListener('mouseenter', (e) => {
            if (!sidebar.classList.contains('collapsed')) return;
            tooltip.textContent = label;
            tooltip.classList.add('show');
        });
        item.addEventListener('mousemove', (e) => {
            if (!sidebar.classList.contains('collapsed')) return;
            tooltip.style.left = (e.clientX + 14) + 'px';
            tooltip.style.top  = (e.clientY - 10) + 'px';
        });
        item.addEventListener('mouseleave', () => {
            tooltip.classList.remove('show');
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
