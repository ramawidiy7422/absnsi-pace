@extends('layouts.admin')
@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')

@push('styles')
<style>
/* ===== RESPONSIVE: hide-mobile ===== */
@media (max-width: 768px) {
    .hide-mobile { display: none !important; }
}

/* ===== FILTER BAR ===== */
.filter-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.filter-bar .form-control,
.filter-bar .form-select {
    height: 38px;
    font-size: 13px;
}
@media (max-width: 576px) {
    .filter-bar .form-control,
    .filter-bar .form-select { width: 100%; min-width: 0 !important; max-width: 100% !important; }
    .search-wrap { max-width: 100% !important; width: 100%; }
}
.filter-bar .search-wrap {
    position: relative;
    flex: 3 1 320px;
    min-width: 240px;
    max-width: 480px;
}
.search-wrap .search-icon {
    position: absolute;
    left: 11px; top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    font-size: 13px;
}
.search-wrap input { padding-left: 34px; padding-right: 40px; }
.search-wrap .search-submit {
    position: absolute;
    right: 4px; top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #64748b;
    padding: 6px 8px;
    line-height: 1;
    cursor: pointer;
}
.search-wrap .search-submit:hover,
.search-wrap .search-submit:active {
    color: #2563eb;
}

/* ===== TABLE MODERN ===== */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13.5px; min-width: 480px; }
.table-modern thead th {
    background: #f8fafc;
    padding: 11px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-bottom: 1px solid #e8edf2;
    white-space: nowrap;
}
.table-modern thead th:first-child { border-radius: 8px 0 0 0; }
.table-modern thead th:last-child  { border-radius: 0 8px 0 0; }

.table-modern tbody tr {
    transition: background 0.15s, box-shadow 0.15s;
    cursor: pointer;
}
.table-modern tbody tr:hover {
    background: #f0f7ff;
    box-shadow: inset 3px 0 0 #4299e1;
}
.table-modern tbody td {
    padding: 11px 14px;
    border-bottom: 1px solid #f0f4f8;
    color: #374151;
    vertical-align: middle;
}
.table-modern tbody tr:last-child td { border-bottom: none; }

/* Avatar */
.karyawan-avatar {
    width: 44px; height: 44px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    background: #f0f4f8;
    display: block;
}
.karyawan-avatar-placeholder {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #e2e8f0, #cbd5e0);
    display: flex; align-items: center; justify-content: center;
    color: #94a3b8;
    font-size: 18px;
    font-weight: 700;
}

/* Nama cell */
.nama-cell strong { font-weight: 600; color: #1e293b; font-size: 13.5px; }
.nama-cell small  { color: #94a3b8; font-size: 11px; }

/* NIK chip */
.nik-chip {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    font-family: monospace;
    letter-spacing: 0.3px;
}

/* Departemen */
.dept-cell { color: #334155; font-size: 13px; }
.dept-cell small { color: #94a3b8; font-size: 11.5px; }
.area-badge {
    display: inline-block;
    font-size: 10px; font-weight: 600;
    padding: 1px 6px; border-radius: 4px;
    margin-left: 5px; vertical-align: middle;
    background: #edf2ff; color: #3b5bdb;
    white-space: nowrap;
}
.area-badge:has(~ ) { }
/* warna per area */
.area-badge[data-area="white"] { background:#f0fdf4; color:#16a34a; }
.area-badge[data-area="grey"]  { background:#f1f5f9; color:#475569; }
.area-badge[data-area="black"] { background:#1e293b; color:#e2e8f0; }

/* Shift badge */
.shift-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #eff6ff;
    color: #3b82f6;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 600;
}

/* Action buttons */
.action-group { display: flex; gap: 5px; align-items: center; }
.action-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.action-btn:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,0.15); }
.action-btn.view   { background: #e8f4fd; color: #2b6cb0; }
.action-btn.edit   { background: #fff3e0; color: #c05621; }
.action-btn.qr     { background: #f3e8ff; color: #7c3aed; }
.action-btn.delete { background: #fff0f0; color: #c53030; }

/* Empty state */
.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #94a3b8;
}
.empty-state i { font-size: 48px; margin-bottom: 12px; opacity: 0.4; display: block; }
.empty-state p { font-size: 14px; }

/* Stat bar atas */
.stat-mini-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 18px;
}
@media (max-width: 900px) {
    .stat-mini-bar { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .stat-mini-bar { grid-template-columns: repeat(2, 1fr); gap: 8px; }
}
.stat-mini {
    background: var(--bg-card, #fff);
    border-radius: 10px;
    padding: 14px 16px;
    border: 1px solid var(--border, #e8edf2);
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.stat-mini-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.stat-mini-val { font-size: 22px; font-weight: 700; color: var(--text-main, #1e293b); line-height: 1; }
.stat-mini-lbl { font-size: 11px; color: #94a3b8; margin-top: 2px; }
</style>
@endpush

{{-- Stat mini --}}
<div class="stat-mini-bar">
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#eff6ff; color:#3b82f6;"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-mini-val">{{ $karyawan->total() }}</div>
            <div class="stat-mini-lbl">Total Karyawan</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#f0fdf4; color:#16a34a;"><i class="fas fa-user-check"></i></div>
        <div>
            <div class="stat-mini-val">{{ $karyawan->getCollection()->whereIn('status',['kontrak','tetap','magang','training'])->count() }}</div>
            <div class="stat-mini-lbl">Aktif (halaman ini)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#f5f3ff; color:#7c3aed;"><i class="fas fa-building"></i></div>
        <div>
            <div class="stat-mini-val">{{ $departments->count() }}</div>
            <div class="stat-mini-lbl">Departemen</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fff7ed; color:#ea580c;"><i class="fas fa-qrcode"></i></div>
        <div style="flex:1;">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <a href="{{ route('admin.karyawan.print-qr-semua') }}" target="_blank"
                   style="font-size:11px; color:#7c3aed; font-weight:600; text-decoration:none;">
                    <i class="fas fa-qrcode"></i> QR Semua
                </a>
                <a href="{{ route('admin.karyawan.id-card-semua') }}" target="_blank"
                   style="font-size:11px; color:#2b6cb0; font-weight:600; text-decoration:none;">
                    <i class="fas fa-id-card"></i> ID Card Semua
                </a>
            </div>
            <div class="stat-mini-lbl">Cetak Kartu</div>
        </div>
    </div>
    @if(auth()->user()->hasPermission('laporan.export'))
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fff5f5; color:#c53030;"><i class="fas fa-file-pdf"></i></div>
        <div style="flex:1;">
            <a href="{{ route('admin.karyawan.export-pdf', request()->query()) }}"
               style="font-size:12px; color:#c53030; font-weight:600; text-decoration:none;">
                <i class="fas fa-download"></i> Export PDF
            </a>
            <div class="stat-mini-lbl">Data Karyawan</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#f0fff4; color:#276749;"><i class="fas fa-file-excel"></i></div>
        <div style="flex:1;">
            <a href="{{ route('admin.karyawan.export-csv', request()->query()) }}"
               style="font-size:12px; color:#276749; font-weight:600; text-decoration:none;">
                <i class="fas fa-download"></i> Export Excel
            </a>
            <div class="stat-mini-lbl">Data Karyawan</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#ebf8ff; color:#2b6cb0;"><i class="fas fa-face-smile"></i></div>
        <div style="flex:1;">
            <form method="POST" action="{{ route('admin.karyawan.sync-wajah') }}" style="display:inline;"
                  onsubmit="return confirm('Sync semua foto wajah ke sistem Face Recognition? Proses ini bisa memakan waktu beberapa menit.')">
                @csrf
                <button type="submit" style="background:none;border:none;padding:0;font-size:12px;color:#2b6cb0;font-weight:600;cursor:pointer;">
                    <i class="fas fa-sync-alt"></i> Sync Face ID
                </button>
            </form>
            <div class="stat-mini-lbl">Daftarkan Wajah</div>
        </div>
    </div>
    @endif
</div>

{{-- Alert kontrak hampir habis --}}
@if($kontrakHampirHabis->count())
<div style="background:#fffbeb;border:1px solid #fef08a;border-radius:12px;padding:14px 18px;margin-bottom:16px;">
    <div style="font-weight:700;color:#854d0e;margin-bottom:10px;font-size:13px;">
        <i class="fas fa-exclamation-triangle"></i> {{ $kontrakHampirHabis->count() }} karyawan kontrak akan berakhir dalam 30 hari
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @foreach($kontrakHampirHabis as $k)
        @php $sisa = (int) now()->startOfDay()->diffInDays($k->tanggal_kontrak_berakhir->startOfDay()); @endphp
        <a href="{{ route('admin.karyawan.show', $k) }}" style="background:white;border:1px solid #fde68a;border-radius:8px;padding:6px 12px;font-size:12px;color:#92400e;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-user-clock" style="color:#d97706;"></i>
            <span><strong>{{ $k->nama }}</strong> — {{ $sisa }} hari lagi ({{ $k->tanggal_kontrak_berakhir->format('d/m/Y') }})</span>
        </a>
        @endforeach
    </div>
</div>
@endif

@if($belumAbsenHariIni > 0)
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
        <div style="font-weight:700;color:#854d0e;font-size:13px;">
            <i class="fas fa-clock"></i> {{ $belumAbsenHariIni }} karyawan belum absen hari ini
            <span style="font-weight:400;color:#92400e;font-size:12px;">({{ today()->translatedFormat('l, d F Y') }})</span>
        </div>
        <div style="font-size:12px;color:#92400e;margin-top:2px;">Gunakan fitur absensi manual untuk mencatat kehadiran mereka.</div>
    </div>
    <a href="{{ route('admin.absensi.belum-absen') }}"
       style="background:#f59e0b;color:white;border:none;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-list-check"></i> Lihat & Absenkan
    </a>
</div>
@endif

<div class="card">
    <div class="card-header" style="padding:14px 20px;">
        <h6 style="margin:0; font-size:14px;"><i class="fas fa-users" style="color:#4299e1;"></i> Daftar Karyawan</h6>
        @if(auth()->user()->hasPermission('karyawan.tambah'))
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admin.karyawan.import') }}" class="btn btn-secondary" style="font-size:13px;padding:7px 14px;">
                <i class="fas fa-file-csv"></i> Import CSV
            </a>
            <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary" style="font-size:13px;padding:7px 14px;">
                <i class="fas fa-plus"></i> Tambah Karyawan
            </a>
        </div>
        @endif
    </div>
    <div class="card-body" style="padding:16px 20px;">

        {{-- Tab Filter Status (klik cepat) --}}
        @php
            $activeStatus = request('status', '');
            $tabs = [
                'aktif'       => ['label'=>'Aktif',        'count'=>($statusCount->tetap + $statusCount->kontrak + $statusCount->magang + $statusCount->training + $statusCount->outsourcing + $statusCount->harian), 'color'=>'#16a34a', 'bg'=>'#dcfce7'],
                'tetap'       => ['label'=>'Tetap',        'count'=>$statusCount->tetap,       'color'=>'#16a34a', 'bg'=>'#f0fdf4'],
                'kontrak'     => ['label'=>'Kontrak',      'count'=>$statusCount->kontrak,     'color'=>'#2563eb', 'bg'=>'#dbeafe'],
                'magang'      => ['label'=>'Magang',       'count'=>$statusCount->magang,      'color'=>'#d97706', 'bg'=>'#fef3c7'],
                'training'    => ['label'=>'Training',     'count'=>$statusCount->training,    'color'=>'#0891b2', 'bg'=>'#e0f2fe'],
                'outsourcing' => ['label'=>'Outsourcing',  'count'=>$statusCount->outsourcing, 'color'=>'#7c3aed', 'bg'=>'#ede9fe'],
                'harian'      => ['label'=>'Harian Lepas', 'count'=>$statusCount->harian,      'color'=>'#854d0e', 'bg'=>'#fef9c3'],
                'nonaktif'    => ['label'=>'Tdk Aktif',    'count'=>($statusCount->nonaktif),  'color'=>'#dc2626', 'bg'=>'#fee2e2'],
                'all'         => ['label'=>'Semua',        'count'=>$statusCount->total,       'color'=>'#64748b', 'bg'=>'#f1f5f9'],
            ];
            // Default tampilan: jika tidak ada filter sama sekali = tab "aktif"
            $activeTab = $activeStatus ?: (!request()->hasAny(['search','department_id','position_id','jenis_kelamin']) ? 'aktif' : 'all');
        @endphp
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;">
            @foreach($tabs as $val => $tab)
            @php
                $href = route('admin.karyawan.index', array_merge(request()->except('status','page'), ['status' => $val]));
                $isActive = $activeTab === $val;
            @endphp
            <a href="{{ $href }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12.5px;font-weight:600;text-decoration:none;transition:all .15s;
                      border:2px solid {{ $isActive ? $tab['color'] : 'transparent' }};
                      background:{{ $isActive ? $tab['color'] : $tab['bg'] }};
                      color:{{ $isActive ? 'white' : $tab['color'] }};">
                {{ $tab['label'] }}
                <span style="background:{{ $isActive ? 'rgba(255,255,255,0.25)' : 'rgba(0,0,0,0.1)' }};border-radius:10px;padding:1px 7px;font-size:11px;">
                    {{ $tab['count'] ?? 0 }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Filter Bar --}}
        <form method="GET" id="filterForm">
            <input type="hidden" name="status" value="{{ request('status', 'aktif') }}">
            <div class="filter-bar">
                {{-- Search --}}
                <div class="search-wrap">
                    <i class="fas fa-search search-icon"></i>
                    <input type="search" name="search" class="form-control"
                           placeholder="Cari nama, NIK, jabatan, departemen..."
                           value="{{ request('search') }}"
                           enterkeyhint="search"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('filterForm').submit();}">
                    <button type="submit" class="search-submit" aria-label="Cari" title="Cari">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                {{-- Filter Departemen --}}
                <select name="department_id" class="form-select" style="flex:1 1 130px;min-width:110px;max-width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Dept.</option>
                    @foreach($departments as $dep)
                        <option value="{{ $dep->id }}" {{ request('department_id')==$dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                    @endforeach
                </select>

                {{-- Filter Jabatan --}}
                <select name="position_id" class="form-select" style="flex:1 1 130px;min-width:110px;max-width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Jabatan</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}" {{ request('position_id')==$pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                    @endforeach
                </select>

                {{-- Filter Jenis Kelamin --}}
                <select name="jenis_kelamin" class="form-select" style="flex:0 1 100px;min-width:90px;max-width:130px;" onchange="this.form.submit()">
                    <option value="">L & P</option>
                    <option value="L" {{ request('jenis_kelamin')==='L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ request('jenis_kelamin')==='P' ? 'selected' : '' }}>Perempuan</option>
                </select>

                @if(request()->hasAny(['search','department_id','position_id','jenis_kelamin']) || (request('status') && !in_array(request('status'), ['aktif','all'])))
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary" style="font-size:13px;padding:7px 12px;white-space:nowrap;">
                    <i class="fas fa-times"></i> Reset
                </a>
                @endif
            </div>
        </form>

        {{-- Tabel --}}
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th class="hide-mobile">ID Karyawan</th>
                        <th class="hide-mobile">Inisial</th>
                        <th>Departemen / Jabatan</th>
                        @if(in_array($activeTab, ['nonaktif','all']))
                        <th class="hide-mobile">Keluar</th>
                        @else
                        <th class="hide-mobile">Shift</th>
                        @endif
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawan as $i => $k)
                    <tr onclick="window.location='{{ route('admin.karyawan.show', $k) }}'" title="Klik untuk detail">
                        <td style="color:#94a3b8; font-size:12px; width:36px;">
                            {{ $karyawan->firstItem() + $i }}
                        </td>

                        {{-- Avatar + Nama --}}
                        <td>
                            <div style="display:flex; align-items:center; gap:11px;">
                                @if($k->foto_wajah)
                                    <img src="{{ asset('storage/' . $k->foto_wajah) }}"
                                         class="karyawan-avatar"
                                         alt="{{ $k->nama }}"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="karyawan-avatar-placeholder" style="display:none;">
                                        {{ strtoupper(substr($k->nama,0,1)) }}
                                    </div>
                                @else
                                    <div class="karyawan-avatar-placeholder">
                                        {{ strtoupper(substr($k->nama,0,1)) }}
                                    </div>
                                @endif
                                <div class="nama-cell">
                                    <strong>{{ $k->nama }}</strong><br>
                                    <small>{{ $k->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                                </div>
                            </div>
                        </td>

                        <td class="hide-mobile">
                            @if(auth()->user()->hasPermission('karyawan.lihat'))
                                <span class="nik-chip">{{ $k->nik }}</span>
                            @else
                                <span class="nik-chip" title="ID hanya tampil untuk admin">
                                    {{ substr($k->nik, 0, 3) }}••••••
                                </span>
                            @endif
                        </td>

                        <td class="hide-mobile">
                            @if($k->inisial)
                                <span style="background:#f0fff4;color:#276749;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:700;border:1px solid #9ae6b4;">
                                    {{ $k->inisial }}
                                </span>
                            @else
                                <span style="color:#cbd5e0;font-size:12px;">—</span>
                            @endif
                        </td>

                        <td class="dept-cell">
                            {{ $k->department?->name ?? $k->departemen }}
                            @if($k->devision)
                                @php
                                    $areaName = strtolower($k->devision->name);
                                    $areaKey = str_contains($areaName,'white') ? 'white' : (str_contains($areaName,'grey') ? 'grey' : (str_contains($areaName,'black') ? 'black' : ''));
                                @endphp
                                <span class="area-badge" @if($areaKey) data-area="{{ $areaKey }}" @endif>{{ $k->devision->name }}</span>
                            @endif
                            <br><small>{{ $k->position?->name ?? $k->jabatan }}</small>
                        </td>

                        @if(in_array($activeTab, ['nonaktif','all']) && in_array($k->status, ['resign','phk','nonaktif']))
                        <td class="hide-mobile">
                            @if($k->tanggal_keluar)
                                <span style="font-size:12px;color:#475569;font-weight:600;">
                                    <i class="fas fa-calendar-times" style="font-size:10px;color:#dc2626;"></i>
                                    {{ $k->tanggal_keluar->format('d/m/Y') }}
                                </span><br>
                            @endif
                            @if($k->alasan_keluar)
                                <small style="color:#94a3b8;" title="{{ $k->alasan_keluar }}">
                                    {{ \Illuminate\Support\Str::limit($k->alasan_keluar, 28) }}
                                </small>
                            @elseif(!$k->tanggal_keluar)
                                <span style="color:#cbd5e0; font-size:12px;">—</span>
                            @endif
                        </td>
                        @elseif(in_array($activeTab, ['nonaktif','all']))
                        <td class="hide-mobile">
                            <span style="color:#cbd5e0; font-size:12px;">—</span>
                        </td>
                        @else
                        <td class="hide-mobile">
                            @php
                                $jadwalHariIni = $k->employeshifts->first();
                                $shiftTampil   = $jadwalHariIni?->shift ?? $k->shift;
                            @endphp
                            @if($shiftTampil)
                                <span class="shift-chip" title="{{ $jadwalHariIni ? 'Jadwal khusus hari ini' : 'Shift default' }}">
                                    <i class="fas fa-clock" style="font-size:10px;"></i>
                                    {{ $shiftTampil->nama_shift }}
                                    @if($jadwalHariIni)
                                        <span style="font-size:9px;opacity:0.7;">★</span>
                                    @endif
                                </span>
                            @else
                                <span style="color:#cbd5e0; font-size:12px;">—</span>
                            @endif
                        </td>
                        @endif

                        <td>
                            <span class="badge badge-{{ $k->status }}">{{ ucfirst($k->status) }}</span>
                            @if($k->bebas_absen)
                            <span style="display:block;margin-top:3px;background:#fef3c7;color:#92400e;border-radius:20px;padding:1px 8px;font-size:10px;font-weight:700;width:fit-content;">
                                <i class="fas fa-star"></i> Bebas Absen
                            </span>
                            @endif
                        </td>

                        {{-- Aksi — stop propagation agar tidak trigger row click --}}
                        <td onclick="event.stopPropagation()">
                            <div class="action-group">
                                <a href="{{ route('admin.karyawan.show', $k) }}"
                                   class="action-btn view" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->hasPermission('karyawan.edit'))
                                <a href="{{ route('admin.karyawan.edit', $k) }}"
                                   class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                <a href="{{ route('admin.karyawan.print-qr', $k) }}" target="_blank"
                                   class="action-btn qr" title="Print QR">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                                @if(auth()->user()->hasPermission('karyawan.hapus'))
                                <button type="button" class="action-btn delete" title="Nonaktifkan (resign)"
                                        onclick="bukaModalNonaktifkan({{ $k->id }}, '{{ addslashes($k->nama) }}', '{{ route('admin.karyawan.destroy', $k) }}')">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                                @endif
                                @if(auth()->user()->role === 'superadmin')
                                <form method="POST" action="{{ route('admin.karyawan.destroy-permanent', $k) }}"
                                      style="display:inline;"
                                      onsubmit="return confirm('HAPUS PERMANEN {{ addslashes($k->nama) }}? SELURUH riwayat absensi/cuti/izin/SP akan ikut terhapus dan TIDAK BISA dikembalikan. Lanjutkan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn delete" title="Hapus Permanen (Superadmin)" style="color:#c53030;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <p>Tidak ada data karyawan</p>
                                @if(request()->hasAny(['search','department_id','position_id','jenis_kelamin']) || (request('status') && !in_array(request('status'),['aktif','all'])))
                                    <a href="{{ route('admin.karyawan.index') }}" style="color:#4299e1; font-size:13px;">Reset filter</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; flex-wrap:wrap; gap:8px;">
            <div style="font-size:12px; color:#94a3b8;">
                Menampilkan {{ $karyawan->firstItem() }}–{{ $karyawan->lastItem() }}
                dari {{ $karyawan->total() }} karyawan
            </div>
            <div class="pagination">
                {{ $karyawan->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Bulk Buat Akun Portal --}}
@if(auth()->user()->hasPermission('karyawan.buat-akun'))
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-users-cog" style="color:#0ea5e9;"></i> Bulk Buat Akun Portal Karyawan</h6>
    </div>
    <div class="card-body">
        @php
            $linkedIds = \DB::table('users')->whereNotNull('karyawan_id')->pluck('karyawan_id');
            $belumAkun = \App\Models\Karyawan::whereNotIn('status',['resign','phk','nonaktif'])->whereNotIn('id',$linkedIds)->count();
        @endphp
        <p style="font-size:13px;color:#64748b;margin-bottom:16px;">
            <i class="fas fa-info-circle" style="color:#0ea5e9;"></i>
            <strong>{{ $belumAkun }}</strong> karyawan aktif belum memiliki akun portal.
            Akun akan dibuat otomatis dengan format email <em>nama.karyawan@domain</em> dan role <strong>karyawan</strong>.
        </p>
        <form method="POST" action="{{ route('admin.karyawan.bulk-buat-akun') }}"
              onsubmit="return confirm('Buat akun untuk {{ $belumAkun }} karyawan yang belum punya akun?')">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Filter Departemen</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Semua Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Domain Email</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;">@</span>
                        <input type="text" name="domain_email" class="form-control" value="erpgmp.com" placeholder="erpgmp.com" style="padding-left:26px;" required>
                    </div>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Password Default</label>
                    <input type="text" name="password" class="form-control" value="karyawan123" placeholder="min 6 karakter" required minlength="6">
                </div>
                <div style="padding-bottom:1px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Buat Akun
                    </button>
                </div>
            </div>
        </form>
        @if(session('bulk_akun_result'))
        <div style="margin-top:12px;padding:10px 14px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:13px;color:#166534;">
            <i class="fas fa-check-circle"></i> {{ session('bulk_akun_result') }}
        </div>
        @endif
    </div>
</div>
@endif
@endsection

{{-- Modal Nonaktifkan Karyawan (resign) — isi alasan & tanggal keluar sebelum submit --}}
<div id="modalNonaktifkan" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);overflow:hidden;">

        <div style="background:linear-gradient(135deg,#1a202c,#2d3748);padding:20px 24px;display:flex;justify-content:space-between;align-items:center;">
            <div style="color:#fff;">
                <div style="font-size:15px;font-weight:700;"><i class="fas fa-user-slash"></i> Nonaktifkan Karyawan</div>
                <div style="font-size:12px;color:#a0aec0;margin-top:2px;" id="nonaktif-nama-sub"></div>
            </div>
            <button type="button" onclick="tutupModalNonaktifkan()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;width:32px;height:32px;border-radius:50%;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>

        <form id="formNonaktifkan" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="padding:20px 24px;">
                <div style="margin-bottom:8px;padding:10px 12px;background:#fffaf0;border:1px solid #fbd38d;border-radius:8px;font-size:12px;color:#744210;">
                    <i class="fas fa-info-circle"></i> Karyawan akan dipindah ke status <strong>Resign</strong>. Data &amp; riwayat absensi/cuti/izin tetap tersimpan — ini bukan hapus permanen.
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Tanggal Keluar <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="tanggal_keluar" id="nonaktif-tanggal" class="form-control" required>
                </div>

                <div class="form-group" style="margin-top:14px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Alasan Keluar <span style="color:#e53e3e;">*</span></label>
                    <select name="alasan_keluar_kategori" id="nonaktif-kategori" class="form-select" required onchange="toggleAlasanLainnya()">
                        <option value="">-- Pilih Alasan --</option>
                        <option value="Mengundurkan diri">Mengundurkan diri</option>
                        <option value="Kontrak berakhir (tidak diperpanjang)">Kontrak berakhir (tidak diperpanjang)</option>
                        <option value="PHK (Pemutusan Hubungan Kerja)">PHK (Pemutusan Hubungan Kerja)</option>
                        <option value="Pensiun">Pensiun</option>
                        <option value="Meninggal dunia">Meninggal dunia</option>
                        <option value="Pelanggaran disiplin">Pelanggaran disiplin</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group" id="nonaktif-catatan-wrap" style="margin-top:14px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Catatan Tambahan <span id="nonaktif-catatan-required" style="display:none;color:#e53e3e;">*</span></label>
                    <textarea name="alasan_keluar_catatan" id="nonaktif-catatan" class="form-control" rows="2" placeholder="Opsional, detail tambahan (mis. pindah kerja, alasan pribadi, dll)"></textarea>
                </div>
            </div>

            <div style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="tutupModalNonaktifkan()" class="btn" style="background:#e2e8f0;color:#334155;">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#e53e3e;border-color:#e53e3e;">
                    <i class="fas fa-user-slash"></i> Nonaktifkan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function bukaModalNonaktifkan(id, nama, actionUrl){
    document.getElementById('nonaktif-nama-sub').textContent = nama;
    document.getElementById('formNonaktifkan').action = actionUrl;
    document.getElementById('nonaktif-tanggal').value = new Date().toISOString().slice(0,10);
    document.getElementById('nonaktif-kategori').value = '';
    document.getElementById('nonaktif-catatan').value = '';
    toggleAlasanLainnya();
    document.getElementById('modalNonaktifkan').style.display = 'flex';
}
function tutupModalNonaktifkan(){
    document.getElementById('modalNonaktifkan').style.display = 'none';
}
function toggleAlasanLainnya(){
    var kategori = document.getElementById('nonaktif-kategori').value;
    var wajibCatatan = kategori === 'Lainnya';
    document.getElementById('nonaktif-catatan-required').style.display = wajibCatatan ? 'inline' : 'none';
    document.getElementById('nonaktif-catatan').required = wajibCatatan;
}
document.getElementById('formNonaktifkan').addEventListener('submit', function(e){
    var kategori = document.getElementById('nonaktif-kategori').value;
    var catatan  = document.getElementById('nonaktif-catatan').value.trim();
    if(kategori === 'Lainnya' && !catatan){
        e.preventDefault();
        alert('Mohon isi catatan tambahan untuk alasan "Lainnya".');
        return;
    }
    if(!confirm('Yakin nonaktifkan karyawan ini? Data & riwayat tetap tersimpan.')){
        e.preventDefault();
    }
});
</script>
@endpush

