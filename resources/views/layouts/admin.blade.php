<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $namaPerusahaan }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $logoPerusahaan ? asset('favicon.ico') : asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
    <script>
        // Apply dark mode BEFORE CSS renders — prevent flash
        (function(){
            var t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();

        // Lacak setInterval/setTimeout dari SEMUA script (termasuk script halaman
        // yang ada di initial page load) supaya navigasi tanpa-reload nanti bisa
        // menghentikan timer "hantu" milik halaman sebelumnya. Harus dipasang
        // paling awal, sebelum script halaman manapun sempat memanggil setInterval.
        (function () {
            window.__pageTimers = { intervals: [], timeouts: [] };
            var origSetInterval = window.setInterval;
            var origSetTimeout  = window.setTimeout;
            window.setInterval = function () {
                var id = origSetInterval.apply(window, arguments);
                window.__pageTimers.intervals.push(id);
                return id;
            };
            window.setTimeout = function () {
                var id = origSetTimeout.apply(window, arguments);
                window.__pageTimers.timeouts.push(id);
                return id;
            };
        })();
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ===== DARK MODE VARIABLES ===== */
        :root {
            --bg-page:      #f0f2f5;
            --bg-card:      #ffffff;
            --bg-topbar:    #ffffff;
            --bg-input:     #ffffff;
            --bg-thead:     #f7fafc;
            --bg-hover:     #f7fafc;
            --bg-badge-def: #f7fafc;
            --text-main:    #2d3748;
            --text-sub:     #4a5568;
            --text-muted:   #718096;
            --text-faint:   #a0aec0;
            --border:       #e2e8f0;
            --border-input: #e2e8f0;
            --shadow:       rgba(0,0,0,0.07);
            --shadow-lg:    rgba(0,0,0,0.15);
            --notif-bg:     #f7fafc;
            --notif-border: #e2e8f0;
            --notif-hover:  #edf2f7;
            --dm-toggle-bg: #edf2f7;
        }
        [data-theme="dark"] {
            --bg-page:      #0f1117;
            --bg-card:      #1a1d27;
            --bg-topbar:    #13151f;
            --bg-input:     #22263a;
            --bg-thead:     #1e2235;
            --bg-hover:     #1e2235;
            --bg-badge-def: #22263a;
            --text-main:    #e2e8f0;
            --text-sub:     #cbd5e0;
            --text-muted:   #94a3b8;
            --text-faint:   #64748b;
            --border:       #2d3448;
            --border-input: #3d4460;
            --shadow:       rgba(0,0,0,0.3);
            --shadow-lg:    rgba(0,0,0,0.5);
            --notif-bg:     #1a1d27;
            --notif-border: #2d3448;
            --notif-hover:  #1e2235;
            --dm-toggle-bg: #2d3448;
        }

        body { font-family: 'Segoe UI', sans-serif; background: var(--bg-page); display: flex; min-height: 100vh; color: var(--text-sub); transition: background 0.3s, color 0.3s; }

        /* ===== SIDEBAR ===== */
        :root { --sb-width: 260px; --sb-collapsed: 68px; --sb-transition: 0.25s ease; }

        .sidebar {
            width: var(--sb-width);
            background: linear-gradient(180deg, #1a1f36 0%, #2d3561 100%);
            color: white; height: 100vh;
            position: fixed; left: 0; top: 0;
            z-index: 200; transition: width var(--sb-transition), left var(--sb-transition);
            /* PENTING: overflow-x hidden tapi Y boleh di child */
            overflow: hidden;
            display: flex; flex-direction: column;
        }
        .sidebar.collapsed { width: var(--sb-collapsed); }

        /* Overlay gelap saat mobile sidebar terbuka */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 199;
        }
        .sidebar-overlay.show { display: block; }

        .sidebar-brand {
            padding: 16px 18px; border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex; align-items: center; gap: 12px;
            min-height: 72px; flex-shrink: 0;
        }
        .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4299e1, #667eea);
            border-radius: 10px; display: flex; align-items: center;
            justify-content: center; font-size: 16px; flex-shrink: 0;
        }
        .brand-text { overflow: hidden; white-space: nowrap; }
        .brand-text h4 { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3; }
        .brand-text small { color: #a0aec0; font-size: 10px; display: block; }

        .sidebar-toggle {
            width: 34px; height: 34px; background: #f7fafc;
            border-radius: 8px; display: flex; align-items: center;
            justify-content: center; cursor: pointer; font-size: 14px;
            color: #4a5568; border: 1px solid #e2e8f0;
            transition: all 0.2s; flex-shrink: 0;
        }
        .sidebar-toggle:hover { background: #edf2f7; color: #2d3748; }

        /* FIX SCROLL: sidebar-menu harus bisa scroll vertikal */
        .sidebar-menu {
            padding: 12px 0;
            flex: 1;
            /* overflow-y scroll berfungsi karena sidebar tidak overflow:hidden lagi pada Y */
            overflow-y: auto;
            overflow-x: hidden;
            /* membatasi height agar scroll aktif */
            min-height: 0;
        }
        .sidebar-menu::-webkit-scrollbar { width: 4px; }
        .sidebar-menu::-webkit-scrollbar-track { background: transparent; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar-menu::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.35); }

        .menu-label {
            padding: 10px 18px 6px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
            color: rgba(255,255,255,0.42); white-space: nowrap; overflow: hidden;
            transition: opacity var(--sb-transition), padding var(--sb-transition), color 0.2s;
        }
        .sidebar.collapsed .menu-label { opacity: 0; padding: 4px 0; }

        /* Accordion section (dilipat/dibuka per grup menu) */
        .menu-section-toggle {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 18px 6px; cursor: pointer; user-select: none;
            border-radius: 8px; margin: 0 6px;
            transition: background 0.2s;
        }
        .menu-section-toggle:hover { background: rgba(255,255,255,0.05); }
        .menu-section-toggle .menu-label { padding: 0; margin: 0; flex: 1; }
        .menu-section-toggle:hover .menu-label { color: rgba(255,255,255,0.7); }
        .menu-section-toggle .chevron {
            font-size: 12px; color: rgba(255,255,255,0.42); transition: transform 0.2s, color 0.2s;
            flex-shrink: 0; margin-left: 8px;
        }
        .menu-section-toggle:hover .chevron { color: rgba(255,255,255,0.8); }
        .menu-section.open .menu-section-toggle .chevron { transform: rotate(90deg); color: #63b3ed; }
        .menu-section-body {
            display: grid; grid-template-rows: 0fr; overflow: hidden;
            transition: grid-template-rows 0.25s ease;
        }
        .menu-section-body > div { overflow: hidden; min-height: 0; }
        .menu-section.open .menu-section-body { grid-template-rows: 1fr; }
        .sidebar.collapsed .menu-section-toggle .chevron { display: none; }
        .sidebar.collapsed .menu-section-body { display: contents; }
        .sidebar.collapsed .menu-section-body > div { display: contents; }

        .menu-item a {
            display: flex; align-items: center; gap: 13px;
            padding: 11px 18px; color: rgba(255,255,255,0.78); text-decoration: none;
            transition: background 0.18s, color 0.18s, border-color 0.18s;
            font-size: 14.5px; font-weight: 500; white-space: nowrap; position: relative;
            margin: 1px 6px; border-radius: 8px;
            border-right: 3px solid transparent;
        }
        .menu-item a:hover {
            background: rgba(255,255,255,0.08); color: #fff;
        }
        .menu-item a.active {
            background: rgba(66,153,225,0.18); color: #fff;
            border-right-color: #4299e1;
            font-weight: 600;
        }
        .menu-item a i { width: 20px; text-align: center; font-size: 16px; flex-shrink: 0; }
        .menu-item a.active i { color: #63b3ed; }
        .menu-text {
            overflow: hidden; transition: opacity var(--sb-transition), max-width var(--sb-transition);
            max-width: 200px;
        }
        .sidebar.collapsed .menu-text { opacity: 0; max-width: 0; }

        .menu-item { position: relative; }
        .sidebar-tooltip {
            position: fixed; background: #1a1f36; color: #fff;
            padding: 5px 12px; border-radius: 6px; font-size: 12px;
            white-space: nowrap; pointer-events: none; opacity: 0;
            transition: opacity 0.15s; box-shadow: 0 4px 12px rgba(0,0,0,0.4); z-index: 9999;
        }
        .sidebar-tooltip.show { opacity: 1; }

        .menu-divider { border: none; border-top: 1px solid rgba(255,255,255,0.06); margin: 6px 16px; }

        /* ===== MAIN CONTENT ===== */
        .main-content { margin-left: var(--sb-width); flex: 1; transition: margin-left var(--sb-transition); min-width: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .main-content.expanded { margin-left: var(--sb-collapsed); }

        /* ===== NAVIGASI TANPA RELOAD (AJAX page swap) ===== */
        #pageLoadingBar {
            position: fixed; top: 0; left: var(--sb-width); right: 0; height: 3px;
            z-index: 300; background: transparent; pointer-events: none;
            transition: left var(--sb-transition);
        }
        .sidebar.collapsed ~ .main-content #pageLoadingBar { left: var(--sb-collapsed); }

        /* Footer fixed ikut menyesuaikan lebar sidebar — pola sama seperti pageLoadingBar,
           CSS murni (tidak perlu JS) supaya selalu sinkron dengan transisi collapse/expand. */
        #mainFooter { left: var(--sb-width); transition: left var(--sb-transition); }
        .sidebar.collapsed ~ #mainFooter { left: var(--sb-collapsed); }
        @media (max-width: 768px) {
            #mainFooter { left: 0 !important; }
        }
        #pageLoadingBar::before {
            content: ''; display: block; height: 100%; width: 0%;
            background: linear-gradient(90deg, #4299e1, #63b3ed);
            box-shadow: 0 0 8px rgba(66,153,225,0.6);
            transition: width 0.25s ease, opacity 0.2s ease;
            opacity: 0;
        }
        #pageLoadingBar.loading::before { width: 70%; opacity: 1; }
        #pageLoadingBar.done::before { width: 100%; opacity: 1; }
        #pageLoadingBar.hide::before { opacity: 0; }

        .content-area { transition: opacity 0.12s ease; }
        .content-area.nav-fading { opacity: 0.4; }

        @media (prefers-reduced-motion: reduce) {
            .content-area { transition: none; }
            #pageLoadingBar::before { transition: none; }
        }

        .topbar {
            background: var(--bg-topbar); padding: 13px 24px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08); position: sticky; top: 0; z-index: 90;
            gap: 12px;
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .topbar h5 { font-size: 17px; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .topbar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .topbar-clock {
            display: flex; flex-direction: column; align-items: flex-end;
            background: var(--notif-bg); border-radius: 8px; padding: 5px 12px; border: 1px solid var(--border);
        }
        .topbar-clock .clock-time { font-size: 16px; font-weight: 700; color: var(--text-main); letter-spacing: 1px; line-height: 1; }
        .topbar-clock .clock-date { font-size: 10px; color: var(--text-faint); margin-top: 2px; }

        .user-info {
            display: flex; align-items: center; gap: 8px;
            background: var(--notif-bg); border-radius: 8px; padding: 6px 12px;
            font-size: 12px; color: var(--text-sub); max-width: 180px;
            overflow: hidden;
        }
        .user-info .user-name {
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
            max-width: 110px;
        }
        .user-avatar {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #4299e1, #667eea);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 12px; font-weight: 700;
            flex-shrink: 0;
        }
        .btn-logout {
            background: #e53e3e; color: white; border: none;
            padding: 7px 14px; border-radius: 6px; cursor: pointer; font-size: 12px;
            text-decoration: none; display: flex; align-items: center; gap: 6px;
        }
        .btn-logout:hover { background: #c53030; }

        /* padding-bottom diisi ulang lewat JS (updateFooterSpacer) sesuai tinggi
           asli #mainFooter — nilai awal di sini hanya fallback sebelum JS jalan. */
        .content-area { padding: 22px; padding-bottom: 60px; flex: 1; }

        /* ===== RESPONSIVE BREAKPOINTS ===== */

        /* Large screen ≥ 1440px */
        @media (min-width: 1440px) {
            :root { --sb-width: 260px; }
            .content-area { padding: 28px; }
            .grid-4 { grid-template-columns: repeat(4,1fr); }
        }

        /* Desktop normal 1025–1439px: sidebar 240px */
        @media (min-width: 1025px) and (max-width: 1439px) {
            :root { --sb-width: 240px; }
            .grid-4 { grid-template-columns: repeat(4,1fr); }
            .content-area { padding: 20px; }
        }

        /* Laptop kecil 769–1024px: sidebar collapse otomatis ke 200px */
        @media (min-width: 769px) and (max-width: 1024px) {
            :root { --sb-width: 200px; }
            .topbar-clock .clock-date { display: none; }
            .content-area { padding: 16px; }
            .grid-4 { grid-template-columns: repeat(2,1fr); }
            .grid-3 { grid-template-columns: repeat(2,1fr); }
            .card-body { padding: 16px; }
            /* Tabel: font sedikit lebih kecil di laptop kecil */
            table { font-size: 13px; }
            thead th, tbody td { padding: 10px 10px; }
            /* User info: sembunyikan role label, hemat ruang */
            .user-role-label { display: none; }
        }

        /* Mobile ≤ 768px */
        @media (max-width: 768px) {
            .sidebar {
                left: calc(-1 * var(--sb-width));
                width: var(--sb-width) !important;
                box-shadow: none;
            }
            .sidebar.mobile-open {
                left: 0;
                box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            }
            .main-content { margin-left: 0 !important; }
            .topbar { padding: 10px 14px; }
            .topbar-clock { display: none; }
            .user-info span { display: none; }
            .btn-logout span { display: none; }
            .content-area { padding: 12px; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr !important; }
            .card-header { flex-wrap: wrap; gap: 8px; }
            .card-header h6 { font-size: 13px; }
            .hide-sm { display: none !important; }
        }

        /* HP kecil ≤ 480px */
        @media (max-width: 480px) {
            .content-area { padding: 8px; }
            .card-body { padding: 12px; }
            .card-header { padding: 10px 12px; }
            .btn { padding: 6px 10px; font-size: 12px; }
            .btn-sm { padding: 4px 8px; font-size: 11px; }
            table { font-size: 12px; }
            thead th, tbody td { padding: 7px 8px; }
            .hide-xs { display: none !important; }
            .grid-4 { grid-template-columns: 1fr 1fr !important; }
            .filter-bar .form-select,
            .filter-bar .form-control { min-width: 0 !important; width: 100%; }
            .pagination a, .pagination span { padding: 5px 7px; font-size: 11px; }
            .topbar h5 { font-size: 14px; }
        }

        /* Landscape HP 481–768px */
        @media (min-width: 481px) and (max-width: 768px) {
            .grid-4 { grid-template-columns: 1fr 1fr !important; }
            .grid-3 { grid-template-columns: 1fr 1fr !important; }
            .hide-sm { display: none !important; }
        }

        /* ===== CARDS ===== */
        .card { background: var(--bg-card); border-radius: 12px; box-shadow: 0 2px 8px var(--shadow); margin-bottom: 20px; overflow: visible; }
        .card > .card-header:first-child { border-radius: 12px 12px 0 0; overflow: hidden; }
        .card-header { padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; min-height: 52px; }
        .card-header h6 { font-weight: 600; color: var(--text-main); margin: 0; white-space: nowrap; }
        .card-body { padding: 20px; }

        .stat-card { border-radius: 12px; padding: 20px; color: white; }
        .stat-card.blue   { background: linear-gradient(135deg, #4299e1, #2b6cb0); }
        .stat-card.green  { background: linear-gradient(135deg, #48bb78, #276749); }
        .stat-card.yellow { background: linear-gradient(135deg, #ed8936, #c05621); }
        .stat-card.red    { background: linear-gradient(135deg, #fc8181, #c53030); }
        .stat-card .stat-number { font-size: 36px; font-weight: 700; }
        .stat-card .stat-label  { font-size: 13px; opacity: 0.9; }
        .stat-card .stat-icon   { font-size: 36px; opacity: 0.3; }

        /* ===== TABLE ===== */
        /* Semua tabel auto-scrollable — tidak ada overflow horizontal di parent */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; position: relative; }
        /* Pastikan div langsung pembungkus tabel selalu scrollable */
        div:has(> table) { overflow-x: auto; -webkit-overflow-scrolling: touch; position: relative; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 500px; }
        /* Isyarat visual "bisa digeser" saat tabel melebar dari layar — JS menambah
           class .has-overflow via IntersectionObserver-like check di scroll-hint.js
           (lihat script di akhir body). Gradient fade di tepi kanan + hint teks. */
        .table-responsive.has-overflow::after,
        div:has(> table).has-overflow::after {
            content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 28px;
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.06));
            pointer-events: none;
        }
        [data-theme="dark"] .table-responsive.has-overflow::after,
        [data-theme="dark"] div:has(> table).has-overflow::after {
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.35));
        }
        .scroll-hint-text {
            display: none; font-size: 11px; color: #a0aec0; text-align: right;
            padding: 4px 4px 8px; align-items: center; justify-content: flex-end; gap: 4px;
        }
        @media (max-width: 900px) {
            .scroll-hint-text.show { display: flex; }
        }
        thead th { background: var(--bg-thead); padding: 12px; text-align: left; font-weight: 600; color: var(--text-sub); border-bottom: 2px solid var(--border); white-space: nowrap; }
        tbody td { padding: 12px; border-bottom: 1px solid var(--border); color: var(--text-sub); }
        tbody tr:hover { background: var(--bg-hover); }

        /* ===== BUTTONS ===== */
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-primary   { background: #4299e1; color: white; } .btn-primary:hover   { background: #2b6cb0; }
        .btn-success   { background: #48bb78; color: white; } .btn-success:hover   { background: #276749; }
        .btn-warning   { background: #ed8936; color: white; } .btn-warning:hover   { background: #c05621; }
        .btn-danger    { background: #fc8181; color: white; } .btn-danger:hover    { background: #c53030; }
        .btn-secondary { background: #718096; color: white; } .btn-secondary:hover { background: #4a5568; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        /* ===== BADGES ===== */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-hadir     { background: #c6f6d5; color: #276749; }
        .badge-terlambat      { background: #fed7d7; color: #c53030; }  /* tanpa keterangan = merah */
        .badge-terlambat-izin { background: #feebc8; color: #c05621; }  /* ada keterangan = kuning */
        .badge-izin      { background: #bee3f8; color: #2c5282; }
        .badge-sakit     { background: #fed7e2; color: #97266d; }
        .badge-alpha     { background: #fed7e2; color: #c53030; }
        .badge-cuti      { background: #e9d8fd; color: #553c9a; }
        .badge-tetap     { background: #c6f6d5; color: #276749; }
        .badge-kontrak   { background: #bee3f8; color: #2c5282; }
        .badge-magang    { background: #fef3c7; color: #92400e; }
        .badge-training  { background: #e0f2fe; color: #0369a1; }
        .badge-nonaktif    { background: #fed7e2; color: #c53030; }
        .badge-outsourcing { background: #ede9fe; color: #6d28d9; }
        .badge-resign      { background: #f1f5f9; color: #475569; }
        .badge-phk         { background: #fee2e2; color: #991b1b; }
        .badge-harian      { background: #fef9c3; color: #854d0e; }
        /* backward compat */
        .badge-aktif     { background: #c6f6d5; color: #276749; }

        /* ===== FORMS ===== */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; color: var(--text-sub); }
        .form-control { width: 100%; padding: 9px 12px; border: 1px solid var(--border-input); border-radius: 6px; font-size: 14px; color: var(--text-main); background: var(--bg-input); }
        .form-control:focus { outline: none; border-color: #4299e1; box-shadow: 0 0 0 3px rgba(66,153,225,0.15); }
        .form-select { width: 100%; padding: 9px 12px; border: 1px solid var(--border-input); border-radius: 6px; font-size: 14px; color: var(--text-main); background: var(--bg-input); }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }

        /* ===== ALERTS ===== */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #c6f6d5; color: #276749; border: 1px solid #9ae6b4; }
        .alert-danger  { background: #fed7e2; color: #c53030; border: 1px solid #feb2b2; }

        /* ===== PAGINATION ===== */
        .pagination { display: flex; gap: 5px; justify-content: center; margin-top: 15px; }
        .pagination a, .pagination span { padding: 6px 12px; border-radius: 4px; font-size: 13px; text-decoration: none; border: 1px solid #e2e8f0; color: #4a5568; }
        .pagination .active span { background: #4299e1; color: white; border-color: #4299e1; }

        /* ===== UTILITY RESPONSIVE ===== */
        /* Filter bar global — semua form filter halaman */
        .filter-bar {
            display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 16px;
        }
        .filter-bar .form-control,
        .filter-bar .form-select {
            min-width: 120px; flex: 1 1 120px; max-width: 220px; height: 38px; font-size: 13px;
        }
        .filter-bar .btn { height: 38px; white-space: nowrap; flex-shrink: 0; }
        .filter-bar .search-wrap { flex: 3 1 280px; max-width: 420px; }
        /* Input pencarian TIDAK ikut dibatasi max-width:220px seperti dropdown filter lain —
           dia harus mengisi penuh lebar .search-wrap supaya placeholder yang panjang
           (mis. "Cari nama, NIK, jabatan, departemen...") tidak terpotong. */
        .filter-bar .search-wrap .form-control { max-width: none; width: 100%; }

        /* Action bar di atas tabel (tombol tambah + filter) */
        .action-bar {
            display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
            justify-content: space-between; margin-bottom: 16px;
        }
        .action-bar-left, .action-bar-right {
            display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
        }

        /* Grid responsif tambahan */
        .grid-auto { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .grid-stat  { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; }

        /* Teks truncate */
        .text-truncate { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 100%; display: block; }

        /* Pastikan image tidak meluap */
        img { max-width: 100%; height: auto; }

        /* Form di dalam modal/card full width di mobile */
        .form-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
        }
        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
            .filter-bar .form-control,
            .filter-bar .form-select { max-width: 100%; }
            .filter-bar .search-wrap { max-width: 100%; }
        }

        /* ===== NOTIFICATION BELL ===== */
        .notif-bell {
            position: relative; display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; background: var(--notif-bg); border-radius: 8px;
            cursor: pointer; border: 1px solid var(--border); text-decoration: none;
            color: var(--text-sub); transition: all 0.2s;
        }
        .notif-bell:hover { background: var(--notif-hover); color: var(--text-main); }
        .notif-bell i { font-size: 15px; }
        .notif-badge {
            position: absolute; top: -5px; right: -5px;
            background: #e53e3e; color: white; font-size: 9px; font-weight: 700;
            width: 18px; height: 18px; border-radius: 50%;
            display: none; align-items: center; justify-content: center; border: 2px solid white;
        }
        .notif-dropdown {
            position: absolute; top: calc(100% + 10px); right: 0;
            background: var(--notif-bg); border-radius: 12px; width: 320px; max-width: calc(100vw - 24px);
            box-shadow: 0 8px 24px var(--shadow-lg);
            border: 1px solid var(--notif-border); z-index: 200; display: none;
        }
        .notif-dropdown.show { display: block; }
        .notif-dropdown-header { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 13px; font-weight: 700; color: var(--text-main); }
        .notif-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-bottom: 1px solid var(--border);
            font-size: 13px; color: var(--text-sub); text-decoration: none;
        }
        .notif-item:hover { background: var(--notif-hover); }
        .notif-item:last-child { border-bottom: none; }
        .notif-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
        .notif-bell-wrapper { position: relative; }

        /* ===== DARK MODE OVERRIDES ===== */
        [data-theme="dark"] .content-area { background: var(--bg-page); }
        [data-theme="dark"] .topbar { border-bottom: 1px solid var(--border); box-shadow: 0 1px 8px var(--shadow); }
        [data-theme="dark"] .sidebar-toggle { background: rgba(255,255,255,0.08); color: #e2e8f0; border-color: rgba(255,255,255,0.15); }
        [data-theme="dark"] .sidebar-toggle:hover { background: rgba(255,255,255,0.15); }

        /* ── Inline text colors yang hardcoded di semua view ── */
        /* Override warna teks gelap (hitam/abu) agar tampil terang di dark mode */
        [data-theme="dark"] .content-area *:not([class*="badge"]):not([class*="btn"]):not(.sidebar *) {
            color: inherit;
        }
        [data-theme="dark"] .card         { color: var(--text-sub); }
        [data-theme="dark"] .card-body    { background: var(--bg-card); color: var(--text-sub); }
        [data-theme="dark"] .card-header  { background: var(--bg-card); }

        /* Semua elemen dengan inline style color yang gelap */
        [data-theme="dark"] td, [data-theme="dark"] th { color: var(--text-sub) !important; }
        [data-theme="dark"] td a, [data-theme="dark"] td strong, [data-theme="dark"] td div,
        [data-theme="dark"] td span:not([class*="badge"]):not([class*="btn"]) { color: var(--text-main) !important; }
        [data-theme="dark"] td small, [data-theme="dark"] td .text-muted { color: var(--text-muted) !important; }

        /* Label, heading, paragraph di content area */
        [data-theme="dark"] .content-area h1,[data-theme="dark"] .content-area h2,
        [data-theme="dark"] .content-area h3,[data-theme="dark"] .content-area h4,
        [data-theme="dark"] .content-area h5,[data-theme="dark"] .content-area h6 { color: var(--text-main) !important; }
        [data-theme="dark"] .content-area p { color: var(--text-sub) !important; }
        [data-theme="dark"] .content-area label { color: var(--text-sub) !important; }
        [data-theme="dark"] .content-area span:not([class*="badge"]) { color: inherit; }
        [data-theme="dark"] .content-area div { color: inherit; }

        /* Override inline style color spesifik — warna gelap jadi terang */
        [data-theme="dark"] [style*="color:#2d3748"] { color: var(--text-main) !important; }
        [data-theme="dark"] [style*="color: #2d3748"] { color: var(--text-main) !important; }
        [data-theme="dark"] [style*="color:#4a5568"] { color: var(--text-sub) !important; }
        [data-theme="dark"] [style*="color: #4a5568"] { color: var(--text-sub) !important; }
        [data-theme="dark"] [style*="color:#718096"] { color: var(--text-muted) !important; }
        [data-theme="dark"] [style*="color: #718096"] { color: var(--text-muted) !important; }
        [data-theme="dark"] [style*="color:#a0aec0"] { color: var(--text-faint) !important; }
        [data-theme="dark"] [style*="color: #a0aec0"] { color: var(--text-faint) !important; }
        [data-theme="dark"] [style*="color:#1a202c"] { color: var(--text-main) !important; }
        [data-theme="dark"] [style*="color:#374151"] { color: var(--text-sub) !important; }

        /* Background putih/abu yang hardcoded */
        [data-theme="dark"] [style*="background:white"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="background: white"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="background:#fff"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="background: #fff"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="background:#ffffff"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="background:#f7fafc"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background: #f7fafc"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background:#f0f2f5"] { background: var(--bg-page) !important; }
        [data-theme="dark"] [style*="background:#edf2f7"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background:#f9fafb"] { background: var(--bg-thead) !important; }

        /* Border warna terang di dark mode */
        [data-theme="dark"] [style*="border-bottom:1px solid #e2e8f0"] { border-bottom-color: var(--border) !important; }
        [data-theme="dark"] [style*="border-bottom: 1px solid #e2e8f0"] { border-bottom-color: var(--border) !important; }
        [data-theme="dark"] [style*="border:1px solid #e2e8f0"] { border-color: var(--border) !important; }
        [data-theme="dark"] [style*="border: 1px solid #e2e8f0"] { border-color: var(--border) !important; }
        [data-theme="dark"] [style*="border-bottom:1px solid #f0f0f0"] { border-bottom-color: var(--border) !important; }
        [data-theme="dark"] [style*="border:1px solid #f0f0f0"] { border-color: var(--border) !important; }

        /* Link teks di content area */
        [data-theme="dark"] .content-area a:not([class*="btn"]):not([class*="badge"]) { color: #60a5fa; }
        [data-theme="dark"] .content-area a:not([class*="btn"]):not([class*="badge"]):hover { color: #93c5fd; }

        /* Alert session */
        [data-theme="dark"] .alert-success { background: #1a3a2a; color: #68d391; border-color: #276749; }
        [data-theme="dark"] .alert-danger  { background: #3a1a1a; color: #fc8181; border-color: #c53030; }

        /* Form elements */
        [data-theme="dark"] input,
        [data-theme="dark"] textarea,
        [data-theme="dark"] select { background: var(--bg-input) !important; color: var(--text-main) !important; border-color: var(--border-input) !important; }
        [data-theme="dark"] input::placeholder,
        [data-theme="dark"] textarea::placeholder { color: var(--text-faint) !important; }

        /* Stat card v2 */
        [data-theme="dark"] .stat-card-v2 { background: var(--bg-card); box-shadow: 0 2px 8px var(--shadow); }
        [data-theme="dark"] .stat-val { color: var(--text-main) !important; }
        [data-theme="dark"] .stat-lbl { color: var(--text-muted) !important; }
        [data-theme="dark"] .stat-sub { color: var(--text-muted) !important; }
        [data-theme="dark"] .stat-icon-box.blue   { background: #1e3a5f; }
        [data-theme="dark"] .stat-icon-box.green  { background: #1a3a2a; }
        [data-theme="dark"] .stat-icon-box.yellow { background: #3a2a0f; }
        [data-theme="dark"] .stat-icon-box.red    { background: #3a1a1a; }
        [data-theme="dark"] .stat-icon-box.purple { background: #2a1a3a; }

        /* Notif dropdown */
        [data-theme="dark"] #notifDropdown { background: var(--bg-card) !important; border-color: var(--border) !important; box-shadow: 0 8px 30px var(--shadow-lg) !important; color: var(--text-sub) !important; }
        [data-theme="dark"] #notifDropdown * { color: var(--text-sub) !important; }
        [data-theme="dark"] #notifDropdown a { color: #60a5fa !important; }

        /* Progress bar */
        [data-theme="dark"] .attend-bar-wrap { background: var(--bg-thead); }
        [data-theme="dark"] .pagination a, [data-theme="dark"] .pagination span { background: var(--bg-card); color: var(--text-sub); border-color: var(--border); }
        [data-theme="dark"] .pagination .active span { background: #4299e1; color: white; }

        /* Modal / overlay */
        [data-theme="dark"] [style*="background:rgba(0,0,0,0.5)"] { background: rgba(0,0,0,0.7) !important; }
        [data-theme="dark"] [style*="background:rgba(0,0,0,.5)"] { background: rgba(0,0,0,0.7) !important; }

        /* Card di dalam card (border dan bg) */
        [data-theme="dark"] [style*="border-radius"][style*="background:#f"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="border-radius:10px"][style*="background:white"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="border-radius:12px"][style*="background:white"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="border-radius:14px"][style*="background:white"] { background: var(--bg-card) !important; }
        [data-theme="dark"] [style*="border-radius:8px"][style*="background:white"] { background: var(--bg-card) !important; }

        /* Dark mode toggle */
        #dmToggle:hover { background: var(--notif-hover) !important; color: var(--text-main) !important; }

        /* ── Page header gradient banners (orange, red, dark) ── */
        /* Orange banners: izin, lemburan, pengumuman — redup di dark */
        [data-theme="dark"] [style*="background:linear-gradient(135deg,#c05621"] { background: linear-gradient(135deg,#3a2a0f,#4a2e0a) !important; }
        [data-theme="dark"] [style*="background:linear-gradient(135deg,#744210"] { background: linear-gradient(135deg,#2a1a0a,#3a2a0f) !important; }
        [data-theme="dark"] [style*="background: linear-gradient(135deg, #c05621"] { background: linear-gradient(135deg,#3a2a0f,#4a2e0a) !important; }
        /* Red banners: alpha generator */
        [data-theme="dark"] [style*="background:linear-gradient(135deg,#742a2a"] { background: linear-gradient(135deg,#3a1010,#4a1515) !important; }
        /* Purple/gradient banners: portal header */
        [data-theme="dark"] [style*="background:linear-gradient(135deg,#667eea"] { background: linear-gradient(135deg,#1e2a4a,#2d1a4a) !important; }
        /* Light gradient backgrounds: jadwal, rekap — redup */
        [data-theme="dark"] [style*="background:linear-gradient(90deg, #faf5ff"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background:linear-gradient(90deg, #ebf8ff"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background:linear-gradient(135deg, #ebf8ff"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background:linear-gradient(90deg, #f3e8ff"] { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background:linear-gradient(135deg,#f0fff4"] { background: var(--bg-thead) !important; }
        /* Dark gradient banner (profil, jadwal) — sudah gelap, sedikit lebih gelap */
        [data-theme="dark"] [style*="background:linear-gradient(135deg,#1a202c"] { background: linear-gradient(135deg,#0f1117,#1a1d27) !important; }
        [data-theme="dark"] [style*="background:linear-gradient(135deg,#2d3748"] { background: linear-gradient(135deg,#1a1d27,#22263a) !important; }
        /* Lemburan & laporan class-based headers */
        [data-theme="dark"] .lmb-header { background: linear-gradient(135deg,#3a2a0f,#4a2e0a) !important; }
        [data-theme="dark"] .stat-box   { background: var(--bg-card) !important; box-shadow: 0 2px 8px var(--shadow) !important; }
        [data-theme="dark"] .stat-num   { color: var(--text-main) !important; }
        [data-theme="dark"] .stat-icon  { background: var(--bg-thead) !important; }
        [data-theme="dark"] .stat-label { color: var(--text-muted) !important; }

        /* ── Karyawan show — dokumen legal card putih ── */
        [data-theme="dark"] .legaldoc-card,
        [data-theme="dark"] [style*="background:white"][style*="border-radius"] { background: var(--bg-card) !important; }

        /* ── Pelanggaran summary card ── */
        [data-theme="dark"] .scard.semua  { background: #1e2235 !important; border-color: #3d4460 !important; }
        [data-theme="dark"] .scard.semua .sv { color: var(--text-main) !important; }
        [data-theme="dark"] .scard.semua .sl { color: var(--text-muted) !important; }
        [data-theme="dark"] .scard.alpha  { background: #3a1a1a !important; border-color: #c53030 !important; }
        [data-theme="dark"] .scard.tanpa  { background: #3a1a1a !important; border-color: #fc8181 !important; }
        [data-theme="dark"] .scard.izin   { background: #3a2a0f !important; border-color: #c05621 !important; }
        [data-theme="dark"] .pill:not(.active-semua):not(.active-alpha):not(.active-terlambat_tanpa):not(.active-terlambat_izin) {
            background: #1e2235 !important; color: var(--text-sub) !important; border-color: var(--border) !important;
        }

        /* ── Karyawan show tab section ── */
        [data-theme="dark"] .tab-content  { background: var(--bg-card) !important; color: var(--text-sub) !important; }
        [data-theme="dark"] .bio-section  { background: var(--bg-card) !important; }

        /* ── Cuti saldo progress bar track ── */
        [data-theme="dark"] [style*="background:#e2e8f0"][style*="border-radius:999px"] { background: #2d3448 !important; }
        [data-theme="dark"] [style*="background:#e2e8f0"][style*="border-radius:99px"]  { background: #2d3448 !important; }
        [data-theme="dark"] [style*="background:#e2e8f0"][style*="border-radius:4px"]   { background: #2d3448 !important; }

        /* ── Rank bar di pelanggaran ── */
        [data-theme="dark"] .rank-bar-wrap { background: #2d3448 !important; }

        /* ── Karyawan index classes ── */
        [data-theme="dark"] .table-modern thead th  { background: var(--bg-thead) !important; color: var(--text-muted) !important; border-bottom-color: var(--border) !important; }
        [data-theme="dark"] .table-modern tbody td  { color: var(--text-sub) !important; border-bottom-color: var(--border) !important; }
        [data-theme="dark"] .table-modern tbody tr:hover { background: var(--bg-hover) !important; }
        [data-theme="dark"] .nama-cell strong        { color: var(--text-main) !important; }
        [data-theme="dark"] .nama-cell small         { color: var(--text-muted) !important; }
        [data-theme="dark"] .nik-chip                { background: #2d3448 !important; color: #94a3b8 !important; }
        [data-theme="dark"] .shift-chip              { background: #1e3a5f !important; color: #60a5fa !important; }
        [data-theme="dark"] .dept-cell               { color: var(--text-sub) !important; }
        [data-theme="dark"] .dept-cell small         { color: var(--text-muted) !important; }
        [data-theme="dark"] .stat-mini               { background: var(--bg-card) !important; border-color: var(--border) !important; }
        [data-theme="dark"] .stat-mini-val           { color: var(--text-main) !important; }
        [data-theme="dark"] .stat-mini-lbl           { color: var(--text-muted) !important; }
        [data-theme="dark"] .action-btn.view         { background: #1e3a5f !important; color: #60a5fa !important; }
        [data-theme="dark"] .action-btn.edit         { background: #3a2a0f !important; color: #f6ad55 !important; }
        [data-theme="dark"] .action-btn.qr           { background: #2a1a3a !important; color: #b794f4 !important; }
        [data-theme="dark"] .action-btn.delete       { background: #3a1a1a !important; color: #fc8181 !important; }
        [data-theme="dark"] .karyawan-avatar-placeholder { background: linear-gradient(135deg, #2d3448, #3d4460) !important; color: #94a3b8 !important; }
        [data-theme="dark"] .karyawan-avatar         { border-color: var(--border) !important; }
        [data-theme="dark"] .search-wrap input       { background: var(--bg-input) !important; color: var(--text-main) !important; border-color: var(--border-input) !important; }
        [data-theme="dark"] .search-wrap i           { color: var(--text-faint) !important; }
        [data-theme="dark"] .empty-state             { color: var(--text-muted) !important; }

        /* ── Dashboard chart area ── */
        [data-theme="dark"] .alert-banner.warning    { background: #3a2a0f; border-color: #c05621; }
        [data-theme="dark"] .alert-banner.danger     { background: #3a1a1a; border-color: #c53030; }
        [data-theme="dark"] .alert-banner.info       { background: #1e3a5f; border-color: #2b6cb0; }
        [data-theme="dark"] .alert-banner-title      { color: var(--text-main) !important; }
        [data-theme="dark"] .chip                    { background: #2d3448 !important; border-color: var(--border) !important; color: var(--text-sub) !important; }

        /* ── Laporan & tabel umum ── */
        [data-theme="dark"] .rekap-table thead tr    { background: #1e2235 !important; }
        [data-theme="dark"] .rekap-table             { color: var(--text-sub) !important; }
        [data-theme="dark"] .dept-row                { background: #1e2235 !important; color: var(--text-muted) !important; }
        [data-theme="dark"] .val-zero                { color: #4a5568 !important; }
        [data-theme="dark"] .summary-grid            { background: var(--bg-card) !important; }
        [data-theme="dark"] .summary-item .sv        { color: var(--text-main) !important; }
        [data-theme="dark"] .summary-item .sl        { color: var(--text-muted) !important; }

        /* ── Portal & modal umum ── */
        [data-theme="dark"] .bio-label               { color: var(--text-muted) !important; }
        [data-theme="dark"] .bio-value               { color: var(--text-main) !important; }
        [data-theme="dark"] .tab-btn                 { color: var(--text-muted) !important; background: transparent !important; border-color: var(--border) !important; }
        [data-theme="dark"] .tab-btn.active          { color: #4299e1 !important; border-color: #4299e1 !important; }

        /* ── Warna khusus #1e293b / #334155 / #374151 / #64748b ── */
        [data-theme="dark"] [style*="color:#1e293b"]  { color: var(--text-main) !important; }
        [data-theme="dark"] [style*="color:#334155"]  { color: var(--text-sub) !important; }
        [data-theme="dark"] [style*="color:#374151"]  { color: var(--text-sub) !important; }
        [data-theme="dark"] [style*="color:#64748b"]  { color: var(--text-muted) !important; }
        [data-theme="dark"] [style*="color:#475569"]  { color: var(--text-muted) !important; }
        [data-theme="dark"] [style*="color:#94a3b8"]  { color: var(--text-faint) !important; }
        [data-theme="dark"] [style*="color:#6b7280"]  { color: var(--text-muted) !important; }

        /* Background chip/tag abu-abu terang */
        [data-theme="dark"] [style*="background:#f1f5f9"] { background: #2d3448 !important; }
        [data-theme="dark"] [style*="background:#eff6ff"]  { background: #1e3a5f !important; }
        [data-theme="dark"] [style*="background:#f0f4f8"]  { background: var(--bg-thead) !important; }
        [data-theme="dark"] [style*="background:#f0fdf4"]  { background: #1a3a2a !important; }
        [data-theme="dark"] [style*="background:#f5f3ff"]  { background: #2a1a3a !important; }
        [data-theme="dark"] [style*="background:#fff7ed"]  { background: #3a2a0f !important; }
        [data-theme="dark"] [style*="background:#fffbeb"]  { background: #3a2a0f !important; }
        [data-theme="dark"] [style*="background:#fef9c3"]  { background: #3a2a0f !important; }
        [data-theme="dark"] [style*="background:#e8f4fd"]  { background: #1e3a5f !important; }
        [data-theme="dark"] [style*="background:#ebf8ff"]  { background: #1e3a5f !important; }
        [data-theme="dark"] [style*="background:#faf5ff"]  { background: #2a1a3a !important; }
        [data-theme="dark"] [style*="background:#fff3e0"]  { background: #3a2a0f !important; }
        [data-theme="dark"] [style*="background:#f3e8ff"]  { background: #2a1a3a !important; }
        [data-theme="dark"] [style*="background:#fff0f0"]  { background: #3a1a1a !important; }
        [data-theme="dark"] [style*="background:#fff5f5"]  { background: #3a1a1a !important; }
        [data-theme="dark"] [style*="background:#fffaf0"]  { background: #3a2a0f !important; }
        [data-theme="dark"] [style*="background:#fffff0"]  { background: #2a2a0f !important; }
        [data-theme="dark"] [style*="background:#f0fff4"]  { background: #1a3a2a !important; }

        /* Border warna-warna pastel */
        [data-theme="dark"] [style*="border:1px solid #9ae6b4"] { border-color: #276749 !important; }
        [data-theme="dark"] [style*="border:1px solid #feb2b2"] { border-color: #c53030 !important; }
        [data-theme="dark"] [style*="border:1px solid #f6e05e"] { border-color: #b7791f !important; }
        [data-theme="dark"] [style*="border:1.5px solid #9ae6b4"] { border-color: #276749 !important; }
        [data-theme="dark"] [style*="border:1.5px solid #feb2b2"] { border-color: #c53030 !important; }
        [data-theme="dark"] [style*="border:2px solid #e2e8f0"]   { border-color: var(--border) !important; }
        [data-theme="dark"] [style*="border:1px solid #fde68a"]   { border-color: #c05621 !important; }
        [data-theme="dark"] [style*="border:1px solid #bee3f8"]   { border-color: #2b6cb0 !important; }
        [data-theme="dark"] [style*="border:1px solid #d6bcfa"]   { border-color: #6b46c1 !important; }
        [data-theme="dark"] [style*="border:1px solid #fbd38d"]   { border-color: #c05621 !important; }
        [data-theme="dark"] [style*="border:1px solid #fed7e2"]   { border-color: #97266d !important; }
        [data-theme="dark"] [style*="border:1px solid #fed7d7"]   { border-color: #c53030 !important; }
        [data-theme="dark"] [style*="border:1px solid #fef08a"]   { border-color: #b7791f !important; }

        /* Smooth transition */
        *, *::before, *::after { transition: background-color 0.2s ease, border-color 0.2s ease, color 0.15s ease; }
        .btn, img, svg, .sidebar *, canvas { transition: none !important; }
        .btn { transition: background-color 0.15s, opacity 0.15s !important; }
    </style>
    <div id="pageStyles">@stack('styles')</div>
</head>
<body>

    @include('layouts.partials.sidebar')

    <div class="main-content" id="mainContent">
        @include('layouts.partials.topbar')

        <div id="pageLoadingBar"></div>

        <div class="content-area" id="contentArea">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning" style="background:#fffaf0;border:1px solid #f6ad55;color:#c05621;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info" style="background:#ebf8ff;border:1px solid #63b3ed;color:#2b6cb0;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
                    <i class="fas fa-info-circle"></i> {{ session('info') }}
                </div>
            @endif
            @if(session('new_password'))
                <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:14px 18px;margin-bottom:16px;">
                    <div style="font-weight:700;color:#276749;margin-bottom:6px;"><i class="fas fa-key"></i> Password {{ session('new_password_user') }} berhasil direset</div>
                    <div style="font-size:12px;color:#4a5568;margin-bottom:6px;">Password baru:</div>
                    <code style="background:#2d3748;color:#68d391;padding:8px 16px;border-radius:8px;font-size:15px;display:inline-block;letter-spacing:2px;">{{ session('new_password') }}</code>
                    <div style="font-size:11px;color:#718096;margin-top:6px;"><i class="fas fa-copy"></i> Salin dan berikan ke karyawan. Password tidak akan ditampilkan lagi.</div>
                </div>
            @endif
            @yield('content')
        </div>

    </div>

    {{-- Footer fixed — selalu terlihat di bawah layar. Jarak kosong pengganti di
         .content-area (padding-bottom) DIHITUNG OTOMATIS dari tinggi asli footer
         via JS (lihat updateFooterSpacer di script bawah), bukan angka tetap —
         supaya tidak pernah menimpa konten walau footer wrap jadi 2-3 baris di
         layar sempit. --}}
    <footer id="mainFooter" style="position:fixed;bottom:0;right:0;background:linear-gradient(to right,#f8fafc,#f1f5f9);border-top:1px solid #e2e8f0;padding:10px 24px;z-index:85;box-shadow:0 -2px 8px rgba(0,0,0,0.06);">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                {{-- Kiri: copyright --}}
                <div style="font-size:12px;color:#4a5568;font-weight:500;">
                    &copy; {{ date('Y') }} <strong style="color:#2d3748;">{{ $namaPerusahaan }}</strong> &mdash; HR System
                </div>
                {{-- Tengah: developer --}}
                <div style="font-size:12px;color:#718096;">
                    Developed by
                    <a href="https://www.instagram.com/ramawidiy/" target="_blank" rel="noopener noreferrer"
                       style="font-weight:700;color:#3182ce;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                        Rama Widiyadhana
                    </a>
                </div>
                {{-- Kanan: versi + status --}}
                <div style="font-size:12px;color:#718096;display:flex;align-items:center;gap:8px;">
                    <span style="font-weight:500;">v1.0.0</span>
                    <span style="color:#cbd5e0;">&bull;</span>
                    <span>Status: <strong style="color:#38a169;"><i class="fas fa-circle" style="font-size:8px;animation:pulse 2s infinite;"></i> Online</strong></span>
                </div>
            </div>
        </footer>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>
    <div class="sidebar-tooltip" id="sidebarTooltip"></div>

    <script>
    // ── Clock ────────────────────────────────────────────────────────────────
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

    // ── Sidebar: desktop vs mobile ───────────────────────────────────────────
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const overlay     = document.getElementById('sidebarOverlay');
    const tooltip     = document.getElementById('sidebarTooltip');
    const isMobile    = () => window.innerWidth <= 768;

    // Restore state desktop
    if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }

    function toggleSidebar() {
        if (isMobile()) {
            // Mobile: slide in/out
            const isOpen = sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show', isOpen);
        } else {
            // Desktop: collapse/expand
            const isCollapsed = sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded', isCollapsed);
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            if (isCollapsed) tooltip.classList.remove('show');
        }
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('show');
    }

    // ── Accordion section sidebar ────────────────────────────────────────────
    function toggleMenuSection(header) {
        const section = header.closest('.menu-section');
        if (!section) return;
        const isOpen = section.classList.toggle('open');
        const openSections = JSON.parse(localStorage.getItem('sidebarOpenSections') || '{}');
        openSections[section.dataset.section] = isOpen;
        localStorage.setItem('sidebarOpenSections', JSON.stringify(openSections));
    }

    (function initMenuSections() {
        const openSections = JSON.parse(localStorage.getItem('sidebarOpenSections') || '{}');
        document.querySelectorAll('.menu-section').forEach(function (section) {
            const key = section.dataset.section;
            const hasActive = section.querySelector('.menu-item a.active') !== null;
            const remembered = openSections[key];
            const shouldOpen = hasActive || (remembered === undefined ? section.dataset.defaultOpen === '1' : remembered);
            section.classList.toggle('open', shouldOpen);
        });
    })();

    // Tutup sidebar mobile saat resize ke desktop
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
            // Restore desktop state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        }
    });

    // Tutup sidebar mobile saat klik menu item di mobile
    document.querySelectorAll('.menu-item a').forEach(a => {
        a.addEventListener('click', () => {
            if (isMobile()) closeMobileSidebar();
        });
    });

    // ── Isyarat visual "geser untuk lihat lebih" pada tabel yang overflow ──────
    // Dipanggil ulang tiap kali konten halaman berganti (full load & AJAX nav)
    // supaya tabel baru yang overflow tetap dapat gradient fade + hint teks.
    window.initScrollHints = function () {
        var wrappers = document.querySelectorAll('#contentArea .table-responsive, #contentArea div:has(> table)');
        wrappers.forEach(function (el) {
            var isOverflowing = el.scrollWidth > el.clientWidth + 2;
            el.classList.toggle('has-overflow', isOverflowing);
            var hint = el.previousElementSibling;
            if (!hint || !hint.classList || !hint.classList.contains('scroll-hint-text')) {
                hint = document.createElement('div');
                hint.className = 'scroll-hint-text';
                hint.innerHTML = '<i class="fas fa-arrows-alt-h"></i> Geser untuk lihat lebih';
                el.parentNode.insertBefore(hint, el);
            }
            hint.classList.toggle('show', isOverflowing);
        });
    };
    window.initScrollHints();

    // ── Footer fixed: padding-bottom .content-area dihitung dari tinggi ASLI
    // footer (bukan angka tebakan) — supaya tidak pernah menimpa konten walau
    // footer wrap jadi 2-3 baris di layar sempit atau nama perusahaan panjang.
    window.updateFooterSpacer = function () {
        var footerEl = document.getElementById('mainFooter');
        var contentEl = document.getElementById('contentArea');
        if (!footerEl || !contentEl) return;
        contentEl.style.paddingBottom = (footerEl.offsetHeight + 20) + 'px';
    };
    window.updateFooterSpacer();
    if (window.ResizeObserver) {
        new ResizeObserver(window.updateFooterSpacer).observe(document.getElementById('mainFooter'));
    }

    window.addEventListener('resize', function () {
        clearTimeout(window.__scrollHintResizeTimer);
        window.__scrollHintResizeTimer = setTimeout(window.initScrollHints, 200);
        clearTimeout(window.__footerResizeTimer);
        window.__footerResizeTimer = setTimeout(window.updateFooterSpacer, 200);
    });

    // ── Notifikasi In-App ────────────────────────────────────────────────────
    @if(auth()->user()?->role === 'karyawan')
    const notifFetchUrl    = '{{ route("portal.notifikasi.fetch") }}';
    const notifMarkReadUrl = '{{ route("portal.notifikasi.mark-read") }}';
    @else
    const notifFetchUrl    = '{{ route("admin.notifikasi.fetch") }}';
    const notifMarkReadUrl = '{{ route("admin.notifikasi.mark-read") }}';
    @endif
    const csrfToken        = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let notifOpen = false;

    function toggleNotifDropdown() {
        const dd = document.getElementById('notifDropdown');
        notifOpen = !notifOpen;
        dd.style.display = notifOpen ? 'block' : 'none';
        if (notifOpen) fetchNotifItems();
    }

    document.addEventListener('click', function(e) {
        const btn = document.getElementById('notifBellBtn');
        const dd  = document.getElementById('notifDropdown');
        if (dd && btn && !btn.contains(e.target) && !dd.contains(e.target)) {
            dd.style.display = 'none';
            notifOpen = false;
        }
    });

    function fetchNotifItems() {
        fetch(notifFetchUrl)
            .then(r => r.json())
            .then(data => {
                updateBadge(data.unread);
                renderNotifList(data.items);
                document.getElementById('markAllBtn').style.display = data.unread > 0 ? 'inline' : 'none';
            })
            .catch(() => {});
    }

    function fetchNotifCount() {
        fetch(notifFetchUrl)
            .then(r => r.json())
            .then(data => updateBadge(data.unread))
            .catch(() => {});
    }

    function updateBadge(count) {
        const badge = document.getElementById('notifBadge');
        if (count > 0) {
            badge.textContent   = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderNotifList(items) {
        const el = document.getElementById('notifList');
        if (!items || items.length === 0) {
            el.innerHTML = '<div style="text-align:center;padding:30px;color:#a0aec0;font-size:13px;"><i class="fas fa-bell-slash" style="display:block;font-size:28px;margin-bottom:10px;"></i>Tidak ada notifikasi</div>';
            return;
        }
        const iconMap = { 'fas fa-calendar-alt': '#fef9c3/#854d0e', 'fas fa-exclamation-triangle': '#fed7e2/#c53030', 'fas fa-business-time': '#e6f9f0/#276749', 'fas fa-bell': '#ebf8ff/#2b6cb0' };
        let html = '';
        items.forEach(n => {
            const d    = n.data;
            const icon = d.icon  || 'fas fa-bell';
            const bg   = d.bg    || '#ebf8ff';
            const clr  = d.color || '#2b6cb0';
            const url  = d.url   || '#';
            const title = d.title || 'Notifikasi';
            const body  = d.body  || '';

            html += `<div style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;
                          background:${n.unread ? '#f0f9ff' : 'white'};cursor:pointer;transition:background .15s;"
                         onclick="handleNotifClick('${n.id}', '${url}')"
                         onmouseover="this.style.background='#f7fafc'" onmouseout="this.style.background='${n.unread ? '#f0f9ff' : 'white'}'">
                <div style="width:36px;height:36px;border-radius:9px;background:${bg};color:${clr};
                            display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">
                    <i class="${icon}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:${n.unread ? '700' : '600'};color:#2d3748;margin-bottom:2px;">
                        ${title}
                        ${n.unread ? '<span style="background:#4299e1;color:white;font-size:8px;padding:1px 5px;border-radius:3px;margin-left:4px;">BARU</span>' : ''}
                    </div>
                    <div style="font-size:11px;color:#4a5568;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${body}</div>
                    <div style="font-size:10px;color:#a0aec0;margin-top:3px;"><i class="fas fa-clock"></i> ${n.time}</div>
                </div>
            </div>`;
        });
        el.innerHTML = html;
    }

    function handleNotifClick(id, url) {
        fetch(notifMarkReadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ id })
        }).catch(() => {});
        if (url && url !== '#') window.location = url;
    }

    function markAllRead() {
        fetch(notifMarkReadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({})
        }).then(() => {
            updateBadge(0);
            document.getElementById('markAllBtn').style.display = 'none';
            fetchNotifItems();
        }).catch(() => {});
    }

    fetchNotifCount();
    setInterval(fetchNotifCount, 60000);

    // ── Tooltip saat sidebar collapsed (desktop) ─────────────────────────────
    document.querySelectorAll('.menu-item[data-tooltip]').forEach(item => {
        item.addEventListener('mouseenter', () => {
            if (isMobile() || !sidebar.classList.contains('collapsed')) return;
            tooltip.textContent = item.dataset.tooltip;
            tooltip.classList.add('show');
        });
        item.addEventListener('mousemove', (e) => {
            if (isMobile() || !sidebar.classList.contains('collapsed')) return;
            tooltip.style.left = (e.clientX + 14) + 'px';
            tooltip.style.top  = (e.clientY - 10) + 'px';
        });
        item.addEventListener('mouseleave', () => tooltip.classList.remove('show'));
    });

    // Timer layout inti (jam topbar, dsb) sudah terdaftar di atas — "freeze" di
    // sini supaya navigasi tanpa-reload TIDAK PERNAH ikut menghentikannya.
    // Timer yang didaftarkan SETELAH titik ini (dari script per-halaman di
    // bawah) yang akan dianggap "milik halaman" dan boleh dibersihkan saat pindah.
    window.__pageTimers.intervals = [];
    window.__pageTimers.timeouts = [];
    </script>
    <div id="pageScripts" style="display:none">@stack('scripts')</div>

<script>
// ── DARK MODE ────────────────────────────────────────────────────────────────
(function(){
    // Apply immediately before render to avoid flash
    var saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    // Update icon immediately
    var icon = document.getElementById('dmIcon');
    if (icon) icon.className = saved === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
})();

function toggleDarkMode(){
    var html    = document.documentElement;
    var current = html.getAttribute('data-theme') || 'light';
    var next    = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    var icon = document.getElementById('dmIcon');
    if (icon) {
        icon.style.transform = 'rotate(20deg)';
        setTimeout(function(){
            icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            icon.style.transform = '';
        }, 150);
    }
}
</script>

<script>
// ── NAVIGASI TANPA RELOAD (AJAX page swap) ────────────────────────────────
(function () {
    var contentArea  = document.getElementById('contentArea');
    var loadingBar    = document.getElementById('pageLoadingBar');
    var pageTitleText = document.getElementById('pageTitleText');
    if (!contentArea) return;

    var currentController = null;

    function clearPageTimers() {
        if (!window.__pageTimers) return;
        window.__pageTimers.intervals.forEach(function (id) { window.clearInterval(id); });
        window.__pageTimers.timeouts.forEach(function (id) { window.clearTimeout(id); });
        window.__pageTimers.intervals = [];
        window.__pageTimers.timeouts = [];
    }

    function isSameOrigin(url) {
        try { return new URL(url, window.location.href).origin === window.location.origin; }
        catch (e) { return false; }
    }

    function shouldIntercept(anchor) {
        if (!anchor || !anchor.href) return false;
        if (anchor.target && anchor.target !== '' && anchor.target !== '_self') return false;
        if (anchor.hasAttribute('download')) return false;
        if (anchor.dataset.noAjax !== undefined) return false;
        if (anchor.getAttribute('href').startsWith('#')) return false;
        if (!isSameOrigin(anchor.href)) return false;
        // Hindari link ke file non-halaman (pdf/csv/excel/zip) — biarkan browser handle download/tab baru.
        if (/\.(pdf|csv|xlsx?|zip|docx?)(\?.*)?$/i.test(anchor.pathname)) return false;
        return true;
    }

    function extractTopLevelFunctionNames(code) {
        // Cari `function nama(...)` di awal baris (top-level, bukan di dalam blok lain).
        // Cukup akurat untuk pola penulisan yang dipakai di halaman-halaman ini.
        var names = [];
        var re = /^\s*function\s+([a-zA-Z_$][\w$]*)\s*\(/gm;
        var m;
        while ((m = re.exec(code)) !== null) names.push(m[1]);
        return names;
    }

    // Cache script eksternal (src=...) yang sudah pernah dimuat sekali di halaman
    // manapun sejak AJAX-nav aktif — supaya tidak didownload/dieksekusi ulang tiap
    // pindah halaman yang sama-sama memakainya (mis. chart.umd.min.js dipakai di
    // banyak halaman dashboard/laporan).
    window.__loadedExternalScripts = window.__loadedExternalScripts || {};

    function runInlineScripts(container) {
        var scripts = Array.prototype.slice.call(container.querySelectorAll('script'));

        // Dieksekusi BERURUTAN (bukan paralel) supaya script eksternal (mis.
        // Chart.js) dipastikan selesai di-load SEBELUM script inline berikutnya
        // yang memakainya (mis. `new Chart(...)`) sempat jalan — sebelumnya
        // forEach menaruh semua <script> ke DOM sekaligus, dan browser memuat
        // src="..." secara asinkron sehingga script inline berikutnya bisa
        // duluan jalan sebelum library eksternalnya siap ("Chart is not defined").
        function next(i) {
            if (i >= scripts.length) return;
            var old = scripts[i];

            if (old.src) {
                if (window.__loadedExternalScripts[old.src]) {
                    old.remove();
                    next(i + 1);
                    return;
                }
                var s = document.createElement('script');
                for (var a = 0; a < old.attributes.length; a++) {
                    s.setAttribute(old.attributes[a].name, old.attributes[a].value);
                }
                s.onload = s.onerror = function () {
                    window.__loadedExternalScripts[old.src] = true;
                    next(i + 1);
                };
                old.parentNode.replaceChild(s, old);
                return; // lanjut lewat onload, bukan langsung
            }

            // Script inline dibungkus IIFE supaya `let`/`const` top-level tidak
            // bentrok dengan deklarasi nama sama dari halaman lain yang pernah
            // dijalankan sebelumnya di konteks global yang sama (tanpa reload).
            // Function top-level tetap di-expose ke window supaya onclick="..."
            // inline di HTML (mis. switchTab(...)) tetap bisa memanggilnya.
            var s2 = document.createElement('script');
            for (var b = 0; b < old.attributes.length; b++) {
                s2.setAttribute(old.attributes[b].name, old.attributes[b].value);
            }
            var code = old.textContent;
            var fnNames = extractTopLevelFunctionNames(code);
            var exposeLines = fnNames.map(function (n) { return 'window.' + n + ' = ' + n + ';'; }).join('\n');
            s2.textContent = '(function(){\n' + code + '\n' + exposeLines + '\n})();';
            old.parentNode.replaceChild(s2, old);
            next(i + 1);
        }

        next(0);
    }

    function startLoading() {
        loadingBar.classList.remove('done', 'hide');
        loadingBar.classList.add('loading');
    }

    function finishLoading(ok) {
        loadingBar.classList.remove('loading');
        loadingBar.classList.add(ok ? 'done' : 'hide');
        setTimeout(function () { loadingBar.classList.add('hide'); }, 250);
        setTimeout(function () { loadingBar.classList.remove('done', 'hide'); }, 500);
    }

    function extractAndSwap(html, url) {
        var doc = new DOMParser().parseFromString(html, 'text/html');

        var newContent = doc.getElementById('contentArea');
        if (!newContent) {
            // Bukan halaman admin ber-layout sama (mis. redirect ke login) — fallback full reload.
            window.location.href = url;
            return false;
        }

        // Judul tab & judul halaman di topbar
        if (doc.title) document.title = doc.title;
        var newPageTitle = doc.getElementById('pageTitleText');
        if (pageTitleText && newPageTitle) pageTitleText.textContent = newPageTitle.textContent;

        // Hentikan timer (setInterval/setTimeout) yang didaftarkan halaman sebelumnya
        // sebelum kontennya diganti, supaya tidak ada polling "hantu" yang nyasar
        // mengubah elemen yang sudah tidak ada.
        clearPageTimers();

        contentArea.innerHTML = newContent.innerHTML;

        // CSS khusus per-halaman dari dokumen baru — tanpa ini, halaman yang
        // di-load lewat AJAX kehilangan seluruh styling khususnya (cuma dapat
        // CSS global layout), karena stack CSS halaman aslinya cuma dirender
        // sekali di head saat full page load, tidak ikut saat innerHTML diganti.
        var newPageStyles = doc.getElementById('pageStyles');
        var oldPageStyles = document.getElementById('pageStyles');
        if (newPageStyles && oldPageStyles) {
            oldPageStyles.innerHTML = newPageStyles.innerHTML;
        }

        // Script khusus per-halaman dari dokumen baru
        var newPageScripts = doc.getElementById('pageScripts');
        var oldPageScripts = document.getElementById('pageScripts');
        if (newPageScripts && oldPageScripts) {
            oldPageScripts.innerHTML = newPageScripts.innerHTML;
            runInlineScripts(oldPageScripts);
        }

        runInlineScripts(contentArea);

        window.scrollTo({ top: 0, behavior: 'instant' });
        updateActiveMenu(url);
        closeMobileSidebarIfOpen();
        initScrollHints();
        return true;
    }

    function updateActiveMenu(url) {
        var path = new URL(url, window.location.href).pathname;
        document.querySelectorAll('.menu-item a').forEach(function (a) {
            var linkPath = a.pathname;
            var active = linkPath && path === linkPath;
            a.classList.toggle('active', active);
            if (active) {
                var section = a.closest('.menu-section');
                if (section) section.classList.add('open');
            }
        });
    }

    function closeMobileSidebarIfOpen() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.querySelector('.sidebar-overlay');
        if (sidebar && sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('show');
        }
    }

    function navigate(url, pushState) {
        if (currentController) currentController.abort();
        currentController = new AbortController();

        startLoading();
        contentArea.classList.add('nav-fading');

        fetch(url, { signal: currentController.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(function (html) {
                var swapped = extractAndSwap(html, url);
                if (swapped && pushState) {
                    history.pushState({ ajaxNav: true }, '', url);
                }
                finishLoading(true);
            })
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                // Gagal (network error dsb) — fallback aman: reload penuh ke URL tujuan.
                window.location.href = url;
            })
            .finally(function () {
                contentArea.classList.remove('nav-fading');
            });
    }

    document.addEventListener('click', function (e) {
        var anchor = e.target.closest('a');
        if (!shouldIntercept(anchor)) return;
        e.preventDefault();
        if (anchor.href === window.location.href) return;
        navigate(anchor.href, true);
    });

    window.addEventListener('popstate', function () {
        navigate(window.location.href, false);
    });
})();
</script>
</body>
</html>
