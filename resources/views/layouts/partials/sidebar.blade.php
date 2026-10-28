@php $u = auth()->user(); $isKaryawan = $u->role === 'karyawan'; @endphp
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon" style="background:white;border-radius:8px;padding:4px;display:flex;align-items:center;justify-content:center;width:44px;height:44px;flex-shrink:0;">
            <img src="{{ $logoPerusahaan ?? '/images/logokecil.png' }}" alt="Logo" style="width:36px;height:36px;object-fit:contain;">
        </div>
        <div class="brand-text">
            <h4 style="font-size:13px;line-height:1.3;">{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}</h4>
            <small>Sistem Absensi Digital</small>
        </div>
    </div>

    <div class="sidebar-menu">

        @if($isKaryawan)
        {{-- ===== MENU PORTAL KARYAWAN ===== --}}
        <div class="menu-label">Beranda</div>
        <div class="menu-item" data-tooltip="Dashboard">
            <a href="{{ route('portal.index') }}" class="{{ request()->routeIs('portal.index') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </div>

        <hr class="menu-divider">
        <div class="menu-label">Kehadiran</div>
        <div class="menu-item" data-tooltip="Absensi Saya">
            <a href="{{ route('portal.absensi') }}" class="{{ request()->routeIs('portal.absensi') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i>
                <span class="menu-text">Absensi Saya</span>
            </a>
        </div>
        <div class="menu-item" data-tooltip="Profil Lengkap Saya">
            <a href="{{ route('portal.profil-lengkap') }}" class="{{ request()->routeIs('portal.profil-lengkap') ? 'active' : '' }}">
                <i class="fas fa-address-card"></i>
                <span class="menu-text">Profil Lengkap Saya</span>
            </a>
        </div>

        <hr class="menu-divider">
        <div class="menu-label">Informasi</div>
        <div class="menu-item" data-tooltip="Kalender">
            <a href="{{ route('portal.kalender-cuti') }}" class="{{ request()->routeIs('portal.kalender-cuti*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Kalender</span>
            </a>
        </div>

        <hr class="menu-divider">
        <div class="menu-label">Akun</div>
        @if($u->karyawan_id && !$isKaryawan)
        <div class="menu-item" data-tooltip="Profil Saya">
            <a href="{{ route('admin.karyawan.show', $u->karyawan_id) }}" class="{{ request()->routeIs('admin.karyawan.show*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i>
                <span class="menu-text">Profil Saya</span>
            </a>
        </div>
        @endif
        <div class="menu-item" data-tooltip="Ganti Password">
            <a href="{{ route('portal.ganti-password') }}" class="{{ request()->routeIs('portal.ganti-password') ? 'active' : '' }}">
                <i class="fas fa-key"></i>
                <span class="menu-text">Ganti Password</span>
            </a>
        </div>
        @if($u->hasPermission('kiosk.akses'))
        <div class="menu-item" data-tooltip="Absensi Digital">
            <a href="{{ route('kiosk.index') }}" target="_blank">
                <i class="fas fa-fingerprint"></i>
                <span class="menu-text">Absensi Digital</span>
            </a>
        </div>
        @endif

        @php
            // Karyawan yang diberi akses admin lewat Role & Permission (Per Role
            // untuk role "karyawan", atau Per User/override individual) melihat
            // section tambahan di bawah ini berisi menu admin yang relevan —
            // dicek sama persis (hasPermission per item) seperti sidebar admin,
            // supaya menu yang muncul selalu sesuai izin yang benar-benar aktif.
            $adminPermsUntukKaryawan = [
                'dashboard.lihat','karyawan.lihat','shift.lihat','absensi.lihat','absensi.edit',
                'laporan.lihat','laporan.rekap-bulanan','laporan.rekap-dept','laporan.rekap-cuti',
                'laporan.rekap-karyawan','laporan.jadwal-shift','cuti.lihat','izin.lihat',
                'izin-terlambat.lihat','lembur.lihat','departemen.kelola','divisi.kelola',
                'jabatan.kelola','hari-libur.lihat','pengumuman.lihat','users.lihat',
                'pengaturan.lihat','audit-log.lihat','login-history.lihat','role.kelola',
                'reset-password.lihat','cuti.approve','fmk.approve','izin-terlambat.approve',
                'lembur.approve','izin.approve',
            ];
            $punyaAksesAdminTambahan = collect($adminPermsUntukKaryawan)->contains(fn($p) => $u->hasPermission($p));
        @endphp

        @if($punyaAksesAdminTambahan)
        <hr class="menu-divider">
        <div class="menu-label">Menu Admin</div>
        @endif
        @endif
        {{-- Blok berikut dirender untuk role admin/staff SEPENUHNYA, dan untuk
             karyawan HANYA ditampilkan sebagai section tambahan kalau ada
             permission admin yang aktif (dicek di atas). --}}
        @if(!$isKaryawan || $punyaAksesAdminTambahan)
        {{-- ===== MENU ADMIN / STAFF ===== --}}
        @unless($isKaryawan)
        <div class="menu-label">Menu Utama</div>

        @if($u->hasPermission('dashboard.lihat'))
        <div class="menu-item" data-tooltip="Dashboard">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </div>
        @endif
        @endunless

        @if($u->hasPermission('cuti.approve') || $u->hasPermission('fmk.approve') || $u->hasPermission('izin-terlambat.approve') || $u->hasPermission('lembur.approve') || $u->hasPermission('izin.approve'))
        @php $totalApprovalSaya = \App\Http\Controllers\Admin\ApprovalSayaController::hitungPending($u); @endphp
        <div class="menu-item" data-tooltip="Approval Saya">
            <a href="{{ route('admin.approval-saya.index') }}" class="{{ request()->routeIs('admin.approval-saya*') ? 'active' : '' }}" style="display:flex;align-items:center;justify-content:space-between;">
                <span style="display:flex;align-items:center;gap:10px;">
                    <i class="fas fa-inbox"></i>
                    <span class="menu-text">Approval Saya</span>
                </span>
                @if($totalApprovalSaya > 0)
                <span class="menu-text" style="background:#e53e3e;color:white;font-size:11px;font-weight:700;padding:1px 7px;border-radius:10px;">{{ $totalApprovalSaya }}</span>
                @endif
            </a>
        </div>
        @endif

        @unless($isKaryawan)
        <div class="menu-item" data-tooltip="Profil Saya">
            <a href="{{ route('admin.profil') }}" class="{{ request()->routeIs('admin.profil*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                <span class="menu-text">Profil Saya</span>
            </a>
        </div>

        @if($u->karyawan_id)
        <div class="menu-item" data-tooltip="Portal Saya">
            <a href="{{ route('portal.index') }}" class="{{ request()->routeIs('portal*') ? 'active' : '' }}" style="background:linear-gradient(135deg,rgba(102,126,234,0.1),rgba(118,75,162,0.1));">
                <i class="fas fa-user-circle"></i>
                <span class="menu-text">Portal Saya</span>
            </a>
        </div>
        @endif

        <div class="menu-item" data-tooltip="Absensi Digital">
            <a href="{{ route('kiosk.index') }}" target="_blank">
                <i class="fas fa-fingerprint"></i>
                <span class="menu-text">Absensi Digital</span>
            </a>
        </div>
        @endunless

        @if($u->hasPermission('pengumuman.lihat'))
        <div class="menu-item" data-tooltip="Pengumuman">
            <a href="{{ route('admin.pengumuman.index') }}" class="{{ request()->routeIs('admin.pengumuman*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i>
                <span class="menu-text">Pengumuman</span>
            </a>
        </div>
        @endif


        {{-- DATA MASTER --}}
        @if($u->hasPermission('karyawan.lihat') || $u->hasPermission('shift.lihat'))
        <hr class="menu-divider">
        <div class="menu-section" data-section="data-master" data-default-open="1">
        <div class="menu-section-toggle" onclick="toggleMenuSection(this)">
            <div class="menu-label">Data Master</div>
            <i class="fas fa-chevron-right chevron"></i>
        </div>
        <div class="menu-section-body"><div>

        @if($u->hasPermission('karyawan.lihat'))
        <div class="menu-item" data-tooltip="Karyawan">
            <a href="{{ route('admin.karyawan.index') }}" class="{{ request()->routeIs('admin.karyawan*') && !request()->routeIs('admin.pelatihan*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span class="menu-text">Karyawan</span>
            </a>
        </div>
        <div class="menu-item" data-tooltip="Monitoring Kontrak">
            <a href="{{ route('admin.kontrak.index') }}" class="{{ request()->routeIs('admin.kontrak*') ? 'active' : '' }}">
                <i class="fas fa-file-contract"></i>
                <span class="menu-text">Monitoring Kontrak</span>
            </a>
        </div>
        <div class="menu-item" data-tooltip="Pelatihan">
            <a href="{{ route('admin.pelatihan.index') }}" class="{{ request()->routeIs('admin.pelatihan*') ? 'active' : '' }}">
                <i class="fas fa-graduation-cap"></i>
                <span class="menu-text">Pelatihan</span>
            </a>
        </div>
        <div class="menu-item" data-tooltip="Surat Peringatan">
            <a href="{{ route('admin.warninglettter.index') }}" class="{{ request()->routeIs('admin.warninglettter.index') ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="menu-text">Surat Peringatan</span>
            </a>
        </div>
        @endif

        <div class="menu-item" data-tooltip="Surat & Formulir">
            <a href="{{ route('admin.formulir.index') }}" class="{{ request()->routeIs('admin.formulir*') || request()->routeIs('admin.surat-dinas*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Surat & Formulir</span>
            </a>
        </div>

        @if($u->hasPermission('shift.lihat'))
        <div class="menu-item" data-tooltip="Shift Kerja">
            <a href="{{ route('admin.shift.index') }}" class="{{ request()->routeIs('admin.shift*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span class="menu-text">Shift Kerja</span>
            </a>
        </div>
        @endif

        </div></div>
        </div>
        @endif

        {{-- ORGANISASI --}}
        @if($u->hasPermission('departemen.kelola') || $u->hasPermission('divisi.kelola') || $u->hasPermission('jabatan.kelola'))
        <hr class="menu-divider">
        <div class="menu-label">Organisasi</div>
        <div class="menu-item" data-tooltip="Organisasi">
            <a href="{{ route('admin.organisasi.index') }}" class="{{ request()->routeIs('admin.organisasi*') || request()->routeIs('admin.department*') || request()->routeIs('admin.devision*') || request()->routeIs('admin.position*') ? 'active' : '' }}">
                <i class="fas fa-sitemap"></i>
                <span class="menu-text">Organisasi</span>
            </a>
        </div>
        @endif

        {{-- CUTI & IZIN --}}
        @if($u->hasPermission('cuti.lihat') || $u->hasPermission('izin.lihat') || $u->hasPermission('izin-terlambat.lihat') || $u->hasPermission('lembur.lihat'))
        <hr class="menu-divider">
        <div class="menu-section" data-section="cuti-izin">
        <div class="menu-section-toggle" onclick="toggleMenuSection(this)">
            <div class="menu-label">Cuti & Izin</div>
            <i class="fas fa-chevron-right chevron"></i>
        </div>
        <div class="menu-section-body"><div>

        @if($u->hasPermission('cuti.lihat'))
        <div class="menu-item" data-tooltip="Pengajuan Cuti">
            <a href="{{ route('admin.leaverequest.index') }}" class="{{ request()->routeIs('admin.leaverequest*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Pengajuan Cuti</span>
            </a>
        </div>
        <div class="menu-item" data-tooltip="Saldo Cuti">
            <a href="{{ route('admin.cuti-saldo.index') }}" class="{{ request()->routeIs('admin.cuti-saldo*') ? 'active' : '' }}">
                <i class="fas fa-umbrella-beach"></i>
                <span class="menu-text">Saldo Cuti</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('izin-terlambat.lihat'))
        <div class="menu-item" data-tooltip="Izin Terlambat">
            <a href="{{ route('admin.izin-terlambat.index') }}" class="{{ request()->routeIs('admin.izin-terlambat*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span class="menu-text">Izin Terlambat</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('izin-pulang-cepat.lihat'))
        <div class="menu-item" data-tooltip="Izin Pulang Cepat">
            <a href="{{ route('admin.izin-pulang-cepat.index') }}" class="{{ request()->routeIs('admin.izin-pulang-cepat*') ? 'active' : '' }}">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Izin Pulang Cepat</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('lembur.lihat'))
        <div class="menu-item" data-tooltip="SPK Lembur">
            <a href="{{ route('admin.spk-lembur.index') }}" class="{{ request()->routeIs('admin.spk-lembur*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">SPK Lembur</span>
            </a>
        </div>
        <div class="menu-item" data-tooltip="Lembur">
            <a href="{{ route('admin.lemburan.index') }}" class="{{ request()->routeIs('admin.lemburan.index') || request()->routeIs('admin.lemburan.show') ? 'active' : '' }}">
                <i class="fas fa-business-time"></i>
                <span class="menu-text">Lembur</span>
            </a>
        </div>
        @endif

        </div></div>
        </div>
        @endif

        {{-- ABSENSI --}}
        @if($u->hasPermission('absensi.lihat') || $u->hasPermission('laporan.lihat'))
        <hr class="menu-divider">
        <div class="menu-section" data-section="absensi">
        <div class="menu-section-toggle" onclick="toggleMenuSection(this)">
            <div class="menu-label">Absensi</div>
            <i class="fas fa-chevron-right chevron"></i>
        </div>
        <div class="menu-section-body"><div>

        @if($u->hasPermission('absensi.lihat') || $u->hasPermission('absensi.edit'))
        <div class="menu-item" data-tooltip="Operasional Absensi">
            <a href="{{ route('admin.absensi-operasional.index') }}" class="{{ request()->routeIs('admin.absensi-operasional*') || request()->routeIs('admin.absensi.index') || request()->routeIs('admin.absensi.belum-absen*') || request()->routeIs('admin.absensi.alpha-generator*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span class="menu-text">Operasional Absensi</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('laporan.lihat') || $u->hasPermission('laporan.rekap-bulanan') || $u->hasPermission('laporan.rekap-dept') || $u->hasPermission('laporan.rekap-cuti') || $u->hasPermission('laporan.rekap-karyawan'))
        <div class="menu-item" data-tooltip="Laporan Absensi">
            <a href="{{ route('admin.laporan.index') }}" class="{{ request()->routeIs('admin.laporan.index') || request()->routeIs('admin.laporan.rekap-bulanan') || request()->routeIs('admin.laporan.rekap-departemen') || request()->routeIs('admin.laporan.rekap-cuti-izin') || request()->routeIs('admin.laporan.rekap-karyawan') || request()->routeIs('admin.laporan.keterlambatan*') || request()->routeIs('admin.laporan.pelanggaran*') || request()->routeIs('admin.laporan.trend-kehadiran*') || request()->routeIs('admin.laporan.rekap-lembur*') || request()->routeIs('admin.laporan.rekap-potongan-tunjangan*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Laporan Absensi</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('laporan.export') && $u->karyawan_id)
        <div class="menu-item" data-tooltip="Slip Absensi Saya">
            <a href="{{ route('portal.slip-pdf', ['bulan' => now()->month, 'tahun' => now()->year]) }}" target="_blank">
                <i class="fas fa-file-invoice"></i>
                <span class="menu-text">Slip Absensi</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('absensi.lihat'))
        <div class="menu-item" data-tooltip="Koreksi Absensi">
            <a href="{{ route('admin.koreksi-absensi.index') }}" class="{{ request()->routeIs('admin.koreksi-absensi*') ? 'active' : '' }}">
                <i class="fas fa-edit"></i>
                <span class="menu-text">Koreksi Absensi</span>
                @php $pendingKoreksi = \App\Models\KoreksiAbsensi::where('status','pending')->count(); @endphp
                @if($pendingKoreksi > 0)
                <span style="background:#e53e3e;color:white;font-size:10px;padding:1px 6px;border-radius:10px;font-weight:700;margin-left:auto;">{{ $pendingKoreksi }}</span>
                @endif
            </a>
        </div>
        @endif

        @if($u->hasPermission('laporan.jadwal-shift'))
        <div class="menu-item" data-tooltip="Jadwal Shift">
            <a href="{{ route('admin.laporan.jadwal-shift') }}" class="{{ request()->routeIs('admin.laporan.jadwal-shift') || request()->routeIs('admin.laporan.jadwal-karyawan') ? 'active' : '' }}">
                <i class="fas fa-calendar-week"></i>
                <span class="menu-text">Jadwal Shift</span>
            </a>
        </div>
        @endif

        </div></div>
        </div>
        @endif

        {{-- KALENDER --}}
        @if($u->hasPermission('hari-libur.lihat'))
        <hr class="menu-divider">
        <div class="menu-label">Kalender</div>

        <div class="menu-item" data-tooltip="Hari Libur">
            <a href="{{ route('admin.hari-libur.index') }}" class="{{ request()->routeIs('admin.hari-libur*') ? 'active' : '' }}">
                <i class="fas fa-calendar-times"></i>
                <span class="menu-text">Hari Libur</span>
            </a>
        </div>
        @endif

        {{-- PENGATURAN --}}
        @if($u->hasPermission('users.lihat') || $u->hasPermission('pengaturan.lihat') || $u->hasPermission('audit-log.lihat') || $u->hasPermission('login-history.lihat') || $u->hasPermission('role.kelola') || $u->hasPermission('reset-password.lihat'))
        <hr class="menu-divider">
        <div class="menu-section" data-section="pengaturan">
        <div class="menu-section-toggle" onclick="toggleMenuSection(this)">
            <div class="menu-label">Pengaturan</div>
            <i class="fas fa-chevron-right chevron"></i>
        </div>
        <div class="menu-section-body"><div>

        @if($u->hasPermission('users.lihat'))
        <div class="menu-item" data-tooltip="Manajemen User">
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i>
                <span class="menu-text">Manajemen User</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('reset-password.lihat'))
        <div class="menu-item" data-tooltip="Permintaan Reset Password">
            <a href="{{ route('admin.reset-password-requests.index') }}" class="{{ request()->routeIs('admin.reset-password-requests*') ? 'active' : '' }}">
                <i class="fas fa-key"></i>
                <span class="menu-text">Permintaan Reset Password</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('pengaturan.lihat'))
        <div class="menu-item" data-tooltip="Pengaturan Sistem">
            <a href="{{ route('admin.pengaturan.index') }}" class="{{ request()->routeIs('admin.pengaturan*') ? 'active' : '' }}">
                <i class="fas fa-sliders-h"></i>
                <span class="menu-text">Pengaturan Sistem</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('audit-log.lihat') || $u->hasPermission('login-history.lihat'))
        <div class="menu-item" data-tooltip="Log & Riwayat Sistem">
            <a href="{{ route('admin.log-sistem.index') }}" class="{{ request()->routeIs('admin.log-sistem*') || request()->routeIs('admin.audit-log*') || request()->routeIs('admin.login-history*') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span class="menu-text">Log & Riwayat Sistem</span>
            </a>
        </div>
        @endif

        @if($u->hasPermission('role.kelola'))
        <div class="menu-item" data-tooltip="Role & Permission">
            <a href="{{ route('admin.role-permission.index') }}" class="{{ request()->routeIs('admin.role-permission*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span class="menu-text">Role & Permission</span>
            </a>
        </div>
        @endif

        </div></div>
        </div>
        @endif

        @endif {{-- end @if($isKaryawan) @else --}}

    </div>

</div>
