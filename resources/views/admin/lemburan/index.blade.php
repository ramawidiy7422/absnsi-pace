@extends('layouts.admin')
@section('title', 'Lemburan')
@section('page-title', 'Manajemen Lemburan')

@push('styles')
<style>
.lmb-header {
    background: linear-gradient(135deg, #c05621, #ed8936);
    color: white; border-radius: 14px; padding: 20px 24px;
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
}
.lmb-header h4 { margin: 0; font-size: 18px; font-weight: 700; }
.lmb-header p  { margin: 4px 0 0; opacity: 0.85; font-size: 13px; }

.stat-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 20px; }
.stat-box {
    background: white; border-radius: 12px; padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); display: flex; align-items: center; gap: 14px;
}
.stat-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
}
.stat-num   { font-size: 22px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-label { font-size: 11px; color: #718096; margin-top: 3px; }

.filter-bar {
    background: white; border-radius: 12px; padding: 14px 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 18px;
    display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;
}
.filter-bar .fg { display: flex; flex-direction: column; gap: 4px; }
.filter-bar label { font-size: 11px; font-weight: 700; color: #4a5568; text-transform: uppercase; }
.filter-bar select, .filter-bar input {
    padding: 7px 10px; border: 1.5px solid #e2e8f0; border-radius: 8px;
    font-size: 13px; color: #2d3748; min-width: 120px;
}

.badge-pending  { background:#fef9c3; color:#854d0e; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-approved { background:#c6f6d5; color:#276749; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-rejected { background:#fed7e2; color:#c53030; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

@media (max-width: 768px) {
    .stat-row { grid-template-columns: repeat(2,1fr); }
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="lmb-header">
    <div>
        <h4><i class="fas fa-business-time"></i> Manajemen Lemburan</h4>
        <p>Pengajuan & persetujuan lembur karyawan</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        @if($totalPending > 0)
        <div style="background:rgba(255,255,255,0.2);border-radius:10px;padding:10px 16px;font-size:13px;">
            <i class="fas fa-clock"></i> <strong>{{ $totalPending }}</strong> menunggu persetujuan
        </div>
        @endif
        <a href="{{ route('admin.lemburan.rekap') }}" style="background:rgba(255,255,255,0.2);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-chart-bar"></i> Rekap Lembur
        </a>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stat-row">
    <div class="stat-box">
        <div class="stat-icon" style="background:#fff7ed;color:#c05621;"><i class="fas fa-hourglass-half"></i></div>
        <div>
            <div class="stat-num" style="color:#c05621;">{{ $totalPending }}</div>
            <div class="stat-label">Menunggu Approval</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="background:#f0fff4;color:#276749;"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-num" style="color:#276749;">{{ $totalApproved }}</div>
            <div class="stat-label">Disetujui Bulan Ini</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="background:#ebf8ff;color:#2b6cb0;"><i class="fas fa-clock"></i></div>
        <div>
            <div class="stat-num" style="color:#2b6cb0;">{{ number_format($totalJamBulanIni, 1) }}</div>
            <div class="stat-label">Total Jam Lembur Bulan Ini</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="background:#f3e8ff;color:#6b46c1;"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-num" style="color:#6b46c1;">{{ $lemburan->total() }}</div>
            <div class="stat-label">Total Pengajuan</div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.lemburan.index') }}"
          style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;width:100%;">
        <div class="fg" style="flex:1;min-width:160px;">
            <label>Cari Karyawan</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIK..." style="min-width:180px;">
        </div>
        <div class="fg">
            <label>Status</label>
            <select name="status">
                <option value="">Semua</option>
                <option value="pending"  {{ request('status')=='pending'  ?'selected':'' }}>Pending</option>
                <option value="approved" {{ request('status')=='approved' ?'selected':'' }}>Disetujui</option>
                <option value="rejected" {{ request('status')=='rejected' ?'selected':'' }}>Ditolak</option>
            </select>
        </div>
        <div class="fg">
            <label>Bulan</label>
            <select name="bulan">
                <option value="">Semua</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('bulan')==$m ?'selected':'' }}>
                        {{ \Carbon\Carbon::createFromDate(null,$m,1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>Tahun</label>
            <select name="tahun">
                @foreach(range(now()->year, now()->year-2) as $y)
                    <option value="{{ $y }}" {{ request('tahun')==$y ?'selected':'' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        @if(request()->hasAny(['search','status','bulan','tahun']))
        <a href="{{ route('admin.lemburan.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
        @endif
    </form>
</div>

{{-- Bulk Action Bar --}}
@if(auth()->user()->hasPermission('lembur.approve'))
<div id="bulkBar" style="display:none;background:#2d3748;color:white;border-radius:10px;padding:10px 18px;margin-bottom:12px;align-items:center;gap:12px;flex-wrap:wrap;">
    <span id="bulkCount" style="font-size:13px;font-weight:600;"></span>
    <form id="formBulkApprove" method="POST" action="{{ route('admin.lemburan.bulk-approve') }}" style="display:inline;" onsubmit="return confirm('Setujui semua lembur yang dipilih?')">
        @csrf
        <div id="bulkApproveIds"></div>
        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui Semua</button>
    </form>
    <form id="formBulkReject" method="POST" action="{{ route('admin.lemburan.bulk-reject') }}" style="display:inline;" onsubmit="return confirm('Tolak semua lembur yang dipilih?')">
        @csrf
        <div id="bulkRejectIds"></div>
        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak Semua</button>
    </form>
    <button onclick="clearSelection()" class="btn btn-secondary btn-sm"><i class="fas fa-times-circle"></i> Batal Pilih</button>
</div>
@endif

{{-- Tabel --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        @if(auth()->user()->hasPermission('lembur.approve'))
                        <th style="width:36px;"><input type="checkbox" id="checkAll" title="Pilih semua pending" onchange="toggleAll(this)"></th>
                        @endif
                        <th class="hide-mobile">#</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th class="hide-mobile">Jam Mulai</th>
                        <th class="hide-mobile">Jam Selesai</th>
                        <th>Total Jam</th>
                        <th class="hide-mobile">Alasan</th>
                        <th>Status</th>
                        <th class="hide-mobile">Disetujui Oleh</th>
                        @if(auth()->user()->hasPermission('lembur.approve') || auth()->user()->hasPermission('lembur.edit') || auth()->user()->hasPermission('lembur.hapus'))
                        <th style="text-align:center;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($lemburan as $i => $lb)
                    <tr>
                        @if(auth()->user()->hasPermission('lembur.approve'))
                        <td style="text-align:center;">
                            @if($lb->status === 'pending')
                            <input type="checkbox" class="bulk-check" value="{{ $lb->id }}" onchange="updateBulkBar()">
                            @endif
                        </td>
                        @endif
                        <td class="hide-mobile" style="color:#a0aec0;font-size:12px;">{{ $lemburan->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('admin.karyawan.show', $lb->karyawan_id) }}#tab-lemburan"
                               style="font-weight:600;font-size:13px;color:#2d3748;text-decoration:none;">
                                {{ $lb->karyawan?->nama ?? '-' }}
                            </a>
                            <div style="font-size:11px;color:#a0aec0;">{{ $lb->karyawan?->department?->name ?? $lb->karyawan?->departemen }}</div>
                        </td>
                        <td style="font-size:13px;font-weight:600;">
                            {{ $lb->tanggal->translatedFormat('d M Y') }}<br>
                            <span style="font-size:11px;color:#a0aec0;">{{ $lb->tanggal->translatedFormat('l') }}</span>
                        </td>
                        <td class="hide-mobile" style="font-size:13px;font-weight:600;">{{ substr($lb->jam_mulai,0,5) }}</td>
                        <td class="hide-mobile" style="font-size:13px;font-weight:600;">{{ substr($lb->jam_selesai,0,5) }}</td>
                        <td>
                            <span style="background:#ebf8ff;color:#2b6cb0;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                                {{ number_format($lb->total_jam, 1) }} jam
                            </span>
                        </td>
                        <td class="hide-mobile" style="max-width:160px;font-size:12px;color:#4a5568;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                            title="{{ $lb->alasan }}">
                            {{ $lb->alasan }}
                        </td>
                        <td>
                            @if($lb->status === 'pending')
                                <span class="badge-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                            @elseif($lb->status === 'approved')
                                <span class="badge-approved"><i class="fas fa-check"></i> Disetujui</span>
                            @else
                                <span class="badge-rejected"><i class="fas fa-times"></i> Ditolak</span>
                            @endif
                        </td>
                        <td class="hide-mobile" style="font-size:12px;color:#718096;">
                            @if($lb->approver)
                                {{ $lb->approver->name }}<br>
                                <small>{{ $lb->approved_at?->format('d/m/Y H:i') }}</small>
                            @else —
                            @endif
                        </td>
                        @if(auth()->user()->hasPermission('lembur.approve') || auth()->user()->hasPermission('lembur.edit') || auth()->user()->hasPermission('lembur.hapus'))
                        <td>
                            <div style="display:flex;gap:4px;justify-content:center;align-items:center;">
                                <a href="{{ route('admin.karyawan.show', $lb->karyawan_id) }}#tab-lemburan"
                                   class="btn btn-secondary btn-sm" title="Lihat Detail Karyawan">
                                    <i class="fas fa-external-link-alt"></i> Detail
                                </a>
                                @if($lb->status === 'pending')
                                @if(auth()->user()->hasPermission('lembur.approve'))
                                <form method="POST" action="{{ route('admin.lemburan.approve', $lb) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Setujui"
                                            onclick="return confirm('Setujui lembur ini?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button class="btn btn-danger btn-sm" title="Tolak"
                                        onclick="openRejectModal({{ $lb->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                                @if(auth()->user()->hasPermission('lembur.edit'))
                                <button class="btn btn-warning btn-sm" title="Edit"
                                        onclick="openEditModal({{ $lb->id }}, '{{ $lb->tanggal->format('Y-m-d') }}', '{{ $lb->jam_mulai ? substr($lb->jam_mulai,0,5) : '' }}', '{{ $lb->jam_selesai ? substr($lb->jam_selesai,0,5) : '' }}', '{{ addslashes($lb->alasan) }}', '{{ addslashes($lb->keterangan ?? '') }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @endif
                                @if(auth()->user()->hasPermission('lembur.hapus'))
                                <form method="POST" action="{{ route('admin.lemburan.destroy', $lb) }}"
                                      onsubmit="return confirm('Hapus data lembur ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-business-time" style="font-size:36px;opacity:0.3;display:block;margin-bottom:10px;"></i>
                            Belum ada data lembur
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($lemburan->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #f0f0f0;">
            {{ $lemburan->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEdit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:500px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 style="font-weight:700;color:#2d3748;margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Lembur</h6>
            <button onclick="closeModal('modalEdit')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal *</label>
                <input type="date" name="tanggal" id="editTanggal" class="form-control" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div class="form-group">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jam Mulai *</label>
                    <input type="time" name="jam_mulai" id="editMulai" class="form-control" required>
                </div>
                <div class="form-group">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jam Selesai *</label>
                    <input type="time" name="jam_selesai" id="editSelesai" class="form-control" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Alasan *</label>
                <input type="text" name="alasan" id="editAlasan" class="form-control" required maxlength="255">
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Keterangan</label>
                <textarea name="keterangan" id="editKet" class="form-control" rows="2" maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tolak --}}
<div id="modalReject" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:400px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h6 style="font-weight:700;color:#c53030;margin:0;"><i class="fas fa-times-circle"></i> Tolak Lembur</h6>
            <button onclick="closeModal('modalReject')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form id="formReject" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Alasan Penolakan</label>
                <textarea name="alasan_tolak" class="form-control" rows="3" placeholder="Tulis alasan penolakan..." maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('modalReject')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openEditModal(id, tgl, mulai, selesai, alasan, ket) {
    document.getElementById('editTanggal').value = tgl;
    document.getElementById('editMulai').value   = mulai;
    document.getElementById('editSelesai').value = selesai;
    document.getElementById('editAlasan').value  = alasan;
    document.getElementById('editKet').value     = ket;
    document.getElementById('formEdit').action   = '/admin/lemburan/' + id;
    openModal('modalEdit');
}

function openRejectModal(id) {
    document.getElementById('formReject').action = '{{ route("admin.lemburan.reject", ":id") }}'.replace(':id', id);
    openModal('modalReject');
}

['modalEdit','modalReject'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target===this) closeModal(id); });
});

// ── Bulk Selection ─────────────────────────────────────────────────
function getChecked() {
    return [...document.querySelectorAll('.bulk-check:checked')].map(c => c.value);
}

function updateBulkBar() {
    const ids = getChecked();
    const bar = document.getElementById('bulkBar');
    if (!bar) return;
    if (ids.length > 0) {
        bar.style.display = 'flex';
        document.getElementById('bulkCount').textContent = ids.length + ' pengajuan dipilih';
        const approveIds = document.getElementById('bulkApproveIds');
        const rejectIds  = document.getElementById('bulkRejectIds');
        approveIds.innerHTML = '';
        rejectIds.innerHTML  = '';
        ids.forEach(id => {
            approveIds.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`;
            rejectIds.innerHTML  += `<input type="hidden" name="ids[]" value="${id}">`;
        });
    } else {
        bar.style.display = 'none';
    }
}

function toggleAll(cb) {
    document.querySelectorAll('.bulk-check').forEach(c => c.checked = cb.checked);
    updateBulkBar();
}

function clearSelection() {
    document.querySelectorAll('.bulk-check').forEach(c => c.checked = false);
    const ca = document.getElementById('checkAll');
    if (ca) ca.checked = false;
    updateBulkBar();
}
</script>
@endpush
@endsection

