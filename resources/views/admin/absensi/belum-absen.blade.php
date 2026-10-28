@extends('layouts.admin')
@section('title', 'Karyawan Belum Absen')
@section('page-title', 'Karyawan Belum Absen')

@section('content')
@push('styles')
<style>
@media (max-width: 768px) { .hide-mobile { display: none !important; } }

.filter-bar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:18px; }
.filter-bar .form-control, .filter-bar .form-select { height:38px; font-size:13px; }
@media (max-width:576px) {
    .filter-bar { flex-direction:column; align-items:stretch; }
    .filter-bar .form-control, .filter-bar .form-select { width:100%; }
}

.search-wrap { position:relative; flex:1; min-width:180px; max-width:260px; }
.search-wrap .search-icon { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#a0aec0; font-size:13px; }
.search-wrap input { padding-left:34px; }
@media (max-width:576px) { .search-wrap { max-width:100%; width:100%; } }

.table-modern { width:100%; border-collapse:separate; border-spacing:0; font-size:13.5px; }
.table-modern thead th {
    background:var(--bg-thead,#f8fafc);
    padding:10px 14px; text-align:left;
    font-size:11px; font-weight:700; color:#64748b;
    text-transform:uppercase; letter-spacing:0.6px;
    border-bottom:1px solid var(--border,#e8edf2); white-space:nowrap;
}
.table-modern tbody tr { transition:background .15s; }
.table-modern tbody tr:hover { background:var(--bg-hover,#f0f7ff); }
.table-modern tbody td {
    padding:10px 14px; border-bottom:1px solid var(--border,#f0f4f8);
    color:var(--text-main,#374151); vertical-align:middle;
}
.table-modern tbody tr:last-child td { border-bottom:none; }

.karyawan-avatar {
    width:38px; height:38px; border-radius:10px;
    object-fit:cover; border:2px solid var(--border,#e2e8f0);
}
.avatar-placeholder {
    width:38px; height:38px; border-radius:10px;
    background:linear-gradient(135deg,#e2e8f0,#cbd5e0);
    display:flex; align-items:center; justify-content:center;
    color:#94a3b8; font-size:15px; font-weight:700;
}
.dept-badge {
    display:inline-block; padding:2px 8px; border-radius:20px;
    font-size:11px; font-weight:600;
    background:#eff6ff; color:#3b82f6;
}

.cb-row { cursor:pointer; }
.cb-row input[type=checkbox] { width:16px; height:16px; cursor:pointer; accent-color:#4299e1; }

.bulk-bar {
    position:sticky; bottom:0; left:0; right:0;
    background:var(--bg-card,#fff);
    border-top:2px solid #4299e1;
    padding:14px 20px;
    display:flex; align-items:center; gap:12px; flex-wrap:wrap;
    box-shadow:0 -4px 16px rgba(0,0,0,0.08);
    z-index:100;
    transition:opacity .2s;
}
.bulk-bar.hidden { display:none; }

.select-all-bar {
    background:var(--bg-thead,#f0f7ff);
    border-radius:8px; padding:8px 14px;
    font-size:13px; margin-bottom:12px;
    display:flex; align-items:center; gap:10px;
    flex-wrap:wrap;
}
</style>
@endpush

{{-- Summary Banner --}}
<div style="background:linear-gradient(135deg,#fffbeb,#fef9c3);border:1px solid #fde68a;border-radius:12px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <div style="width:48px;height:48px;border-radius:12px;background:#f59e0b;display:flex;align-items:center;justify-content:center;color:white;font-size:22px;flex-shrink:0;">
        <i class="fas fa-user-clock"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:22px;font-weight:800;color:#854d0e;line-height:1;">{{ $karyawan->total() }}</div>
        <div style="font-size:13px;color:#92400e;margin-top:2px;">
            Karyawan belum absen —
            <strong>{{ $tanggal->translatedFormat('l, d F Y') }}</strong>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary" style="font-size:13px;padding:7px 14px;">
            <i class="fas fa-list"></i> Semua Absensi
        </a>
        <a href="{{ route('admin.absensi.create') }}" class="btn btn-primary" style="font-size:13px;padding:7px 14px;">
            <i class="fas fa-plus"></i> Absen Manual
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="padding:14px 20px;">
        <h6 style="margin:0;font-size:14px;"><i class="fas fa-clock" style="color:#f59e0b;"></i> Daftar Karyawan Belum Absen</h6>
        {{-- Filter tanggal di header --}}
        <form method="GET" style="display:flex;align-items:center;gap:8px;">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="department_id" value="{{ request('department_id') }}">
            <label style="font-size:12px;color:#64748b;white-space:nowrap;">Tanggal:</label>
            <input type="date" name="tanggal" class="form-control"
                   value="{{ $tanggal->format('Y-m-d') }}"
                   max="{{ today()->format('Y-m-d') }}"
                   style="width:150px;height:34px;font-size:13px;"
                   onchange="this.form.submit()">
        </form>
    </div>

    <div class="card-body" style="padding:16px 20px;">

        {{-- Filter Bar --}}
        <form method="GET" id="filterForm">
            <input type="hidden" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}">
            <div class="filter-bar">
                <div class="search-wrap">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Cari nama atau NIK..."
                           value="{{ request('search') }}"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('filterForm').submit();}">
                </div>
                <select name="department_id" class="form-select" style="max-width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Departemen</option>
                    @foreach($departemen as $dep)
                        <option value="{{ $dep->id }}" {{ request('department_id')==$dep->id?'selected':'' }}>{{ $dep->name }}</option>
                    @endforeach
                </select>
                @if(request()->hasAny(['search','department_id']))
                <a href="{{ route('admin.absensi.belum-absen', ['tanggal' => $tanggal->format('Y-m-d')]) }}"
                   class="btn btn-secondary" style="font-size:13px;padding:7px 12px;">
                    <i class="fas fa-times"></i> Reset
                </a>
                @endif
            </div>
        </form>

        {{-- Select-All bar --}}
        <div class="select-all-bar" id="selectAllBar">
            <input type="checkbox" id="checkAll" onchange="toggleAll(this)">
            <label for="checkAll" style="cursor:pointer;font-weight:600;color:var(--text-main,#374151);margin:0;">
                Pilih semua yang tampil
            </label>
            <span id="selectedCount" style="margin-left:auto;font-size:12px;color:#64748b;">0 dipilih</span>
        </div>

        @if($karyawan->count())
        <form id="massalForm" action="{{ route('admin.absensi.absen-massal') }}" method="POST">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}">

            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th>Karyawan</th>
                            <th class="hide-mobile">NIK</th>
                            <th>Departemen / Jabatan</th>
                            <th class="hide-mobile">Shift</th>
                            <th class="hide-mobile">Masa Kerja</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($karyawan as $k)
                        <tr class="cb-row" onclick="toggleRow(event, {{ $k->id }})">
                            <td onclick="event.stopPropagation()">
                                <input type="checkbox" name="ids[]" value="{{ $k->id }}"
                                       class="row-cb" id="cb_{{ $k->id }}"
                                       onchange="updateCount()">
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    @if($k->foto_wajah)
                                        <img src="{{ asset('storage/'.$k->foto_wajah) }}" class="karyawan-avatar"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                        <div class="avatar-placeholder" style="display:none;">{{ strtoupper(substr($k->nama,0,1)) }}</div>
                                    @else
                                        <div class="avatar-placeholder">{{ strtoupper(substr($k->nama,0,1)) }}</div>
                                    @endif
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px;">{{ $k->nama }}</div>
                                        <div style="font-size:11px;color:#94a3b8;">{{ $k->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="hide-mobile">
                                <span style="font-family:monospace;font-size:12px;background:var(--bg-thead,#f1f5f9);padding:2px 8px;border-radius:5px;color:#475569;">
                                    {{ $k->nik }}
                                </span>
                            </td>
                            <td>
                                <span class="dept-badge">{{ $k->department?->name ?? $k->departemen }}</span><br>
                                <small style="color:#94a3b8;font-size:11.5px;">{{ $k->jabatan }}</small>
                            </td>
                            <td class="hide-mobile">
                                @if($k->shift)
                                    <span style="font-size:12px;color:#3b82f6;font-weight:600;">
                                        <i class="fas fa-clock" style="font-size:10px;"></i>
                                        {{ $k->shift->nama_shift }}
                                    </span><br>
                                    <small style="color:#94a3b8;font-size:11px;">
                                        {{ substr($k->shift->jam_masuk,0,5) }}–{{ substr($k->shift->jam_keluar,0,5) }}
                                    </small>
                                @else
                                    <span style="color:#cbd5e0;font-size:12px;">—</span>
                                @endif
                            </td>
                            <td class="hide-mobile">
                                @if($k->tanggal_masuk)
                                    @php
                                        $bulan = (int) $k->tanggal_masuk->startOfDay()->diffInMonths(today()->startOfDay());
                                        $thn   = intdiv($bulan, 12);
                                        $bln   = $bulan % 12;
                                    @endphp
                                    <span style="font-size:12px;color:var(--text-main,#374151);">
                                        @if($thn) {{ $thn }} thn @endif
                                        @if($bln) {{ $bln }} bln @endif
                                        @if(!$thn && !$bln) < 1 bln @endif
                                    </span>
                                @else
                                    <span style="color:#cbd5e0;font-size:12px;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Bulk action bar (sticky bottom) --}}
            <div class="bulk-bar hidden" id="bulkBar">
                <div style="font-size:14px;font-weight:700;color:var(--text-main,#1e293b);">
                    <i class="fas fa-check-square" style="color:#4299e1;"></i>
                    <span id="bulkCount">0</span> karyawan dipilih
                </div>
                <div style="margin-left:auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <label style="font-size:13px;color:#64748b;white-space:nowrap;">Status:</label>
                    <select name="status" class="form-select" style="width:140px;height:36px;font-size:13px;">
                        <option value="hadir">✅ Hadir</option>
                        <option value="izin">📋 Izin</option>
                        <option value="sakit">🏥 Sakit</option>
                        <option value="alpha">❌ Alpha</option>
                    </select>
                    <button type="submit" class="btn btn-primary" style="font-size:13px;padding:7px 18px;"
                            onclick="return confirmMassal()">
                        <i class="fas fa-check"></i> Absenkan Sekarang
                    </button>
                    <button type="button" class="btn btn-secondary" style="font-size:13px;padding:7px 12px;"
                            onclick="clearSelection()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </div>
        </form>
        @else
        <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
            <i class="fas fa-check-circle" style="font-size:52px;color:#34d399;margin-bottom:16px;display:block;"></i>
            <div style="font-size:16px;font-weight:700;color:#16a34a;margin-bottom:6px;">Semua Karyawan Sudah Absen!</div>
            <div style="font-size:13px;">Tidak ada karyawan yang belum absen pada {{ $tanggal->translatedFormat('d F Y') }}.</div>
            <a href="{{ route('admin.absensi.index') }}" class="btn btn-primary" style="margin-top:20px;font-size:13px;">
                <i class="fas fa-list"></i> Lihat Semua Absensi
            </a>
        </div>
        @endif

        {{-- Pagination --}}
        @if($karyawan->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;gap:8px;">
            <div style="font-size:12px;color:#94a3b8;">
                Menampilkan {{ $karyawan->firstItem() }}–{{ $karyawan->lastItem() }}
                dari {{ $karyawan->total() }} karyawan
            </div>
            <div class="pagination">
                {{ $karyawan->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@push('scripts')
<script>
function updateCount(){
    const checked = document.querySelectorAll('.row-cb:checked');
    const n = checked.length;
    document.getElementById('selectedCount').textContent = n + ' dipilih';
    document.getElementById('bulkCount').textContent = n;
    const bar = document.getElementById('bulkBar');
    bar.classList.toggle('hidden', n === 0);
    const checkAll = document.getElementById('checkAll');
    const all = document.querySelectorAll('.row-cb');
    checkAll.indeterminate = n > 0 && n < all.length;
    checkAll.checked = n > 0 && n === all.length;
}

function toggleAll(cb){
    document.querySelectorAll('.row-cb').forEach(c => c.checked = cb.checked);
    updateCount();
}

function toggleRow(event, id){
    if(event.target.type === 'checkbox') return;
    const cb = document.getElementById('cb_' + id);
    if(cb){ cb.checked = !cb.checked; updateCount(); }
}

function clearSelection(){
    document.querySelectorAll('.row-cb').forEach(c => c.checked = false);
    document.getElementById('checkAll').checked = false;
    updateCount();
}

function confirmMassal(){
    const n = document.querySelectorAll('.row-cb:checked').length;
    const status = document.querySelector('[name=status]').value;
    if(n === 0){ alert('Pilih minimal 1 karyawan!'); return false; }
    const statusLabel = {hadir:'Hadir',izin:'Izin',sakit:'Sakit',alpha:'Alpha'};
    return confirm(`Absenkan ${n} karyawan dengan status "${statusLabel[status]}"?\n\nTindakan ini akan mencatat absensi hari ini.`);
}
</script>
@endpush
@endsection

