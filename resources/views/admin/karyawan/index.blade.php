@extends('layouts.admin')
@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')

@push('styles')
<style>
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
.search-wrap {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 280px;
}
.search-wrap .search-icon {
    position: absolute;
    left: 11px; top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    font-size: 13px;
}
.search-wrap input { padding-left: 34px; }

/* ===== TABLE MODERN ===== */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13.5px; }
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
.stat-mini {
    background: #fff;
    border-radius: 10px;
    padding: 14px 16px;
    border: 1px solid #e8edf2;
    display: flex;
    align-items: center;
    gap: 12px;
}
.stat-mini-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.stat-mini-val { font-size: 22px; font-weight: 700; color: #1e293b; line-height: 1; }
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
            <div class="stat-mini-val">{{ $karyawan->getCollection()->where('status','aktif')->count() }}</div>
            <div class="stat-mini-lbl">Aktif (halaman ini)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#f5f3ff; color:#7c3aed;"><i class="fas fa-building"></i></div>
        <div>
            <div class="stat-mini-val">{{ $departemen->count() }}</div>
            <div class="stat-mini-lbl">Departemen</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fff7ed; color:#ea580c;"><i class="fas fa-qrcode"></i></div>
        <div style="flex:1;">
            <a href="{{ route('admin.karyawan.print-qr-semua') }}" target="_blank"
               style="font-size:12px; color:#7c3aed; font-weight:600; text-decoration:none;">
                <i class="fas fa-print"></i> Print Semua QR
            </a>
            <div class="stat-mini-lbl">Kartu Absensi</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="padding:14px 20px;">
        <h6 style="margin:0; font-size:14px;"><i class="fas fa-users" style="color:#4299e1;"></i> Daftar Karyawan</h6>
        @if(in_array(auth()->user()->role, ['admin','hrd']))
        <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary" style="font-size:13px; padding:7px 14px;">
            <i class="fas fa-plus"></i> Tambah Karyawan
        </a>
        @endif
    </div>
    <div class="card-body" style="padding:16px 20px;">

        {{-- Filter --}}
        <form method="GET" id="filterForm">
            <div class="filter-bar">
                <div class="search-wrap">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Cari NIK atau nama..."
                           value="{{ request('search') }}"
                           oninput="debounceSubmit()">
                </div>
                <select name="departemen" class="form-select" style="max-width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Departemen</option>
                    @foreach($departemen as $dep)
                        <option value="{{ $dep }}" {{ request('departemen')==$dep ? 'selected' : '' }}>{{ $dep }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select" style="max-width:140px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="aktif"    {{ request('status')=='aktif'    ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status')=='nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @if(request()->hasAny(['search','departemen','status']))
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary" style="font-size:13px; padding:7px 12px;">
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
                        <th>NIK</th>
                        <th>Departemen / Jabatan</th>
                        <th>Shift</th>
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

                        <td><span class="nik-chip">{{ $k->nik }}</span></td>

                        <td class="dept-cell">
                            {{ $k->departemen }}<br>
                            <small>{{ $k->jabatan }}</small>
                        </td>

                        <td>
                            @if($k->shift)
                                <span class="shift-chip">
                                    <i class="fas fa-clock" style="font-size:10px;"></i>
                                    {{ $k->shift->nama_shift }}
                                </span>
                            @else
                                <span style="color:#cbd5e0; font-size:12px;">—</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-{{ $k->status }}">{{ ucfirst($k->status) }}</span>
                        </td>

                        {{-- Aksi — stop propagation agar tidak trigger row click --}}
                        <td onclick="event.stopPropagation()">
                            <div class="action-group">
                                <a href="{{ route('admin.karyawan.show', $k) }}"
                                   class="action-btn view" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array(auth()->user()->role, ['admin','hrd']))
                                <a href="{{ route('admin.karyawan.edit', $k) }}"
                                   class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                <a href="{{ route('admin.karyawan.print-qr', $k) }}" target="_blank"
                                   class="action-btn qr" title="Print QR">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                                @if(auth()->user()->role === 'admin')
                                <form method="POST" action="{{ route('admin.karyawan.destroy', $k) }}"
                                      style="display:inline;"
                                      onsubmit="return confirm('Hapus karyawan {{ addslashes($k->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <p>Tidak ada data karyawan</p>
                                @if(request()->hasAny(['search','departemen','status']))
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
@endsection

@push('scripts')
<script>
let debounceTimer;
function debounceSubmit() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
}
</script>
@endpush
