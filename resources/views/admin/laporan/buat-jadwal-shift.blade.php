@extends('layouts.admin')
@section('title', 'Buat Jadwal Shift')
@section('page-title', 'Buat Jadwal Shift')

@push('styles')
<style>
/* ── Page header ─────────────────────────────────────── */
.bjs-header {
    background: linear-gradient(135deg, #553c9a 0%, #805ad5 100%);
    color: white; border-radius: 16px; padding: 22px 28px;
    margin-bottom: 24px; display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 12px;
}
.bjs-header h4 { margin: 0; font-size: 20px; font-weight: 800; }
.bjs-header p  { margin: 4px 0 0; opacity: .85; font-size: 13px; }

/* ── 2-column grid ───────────────────────────────────── */
.bjs-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) {
    .bjs-grid { grid-template-columns: 1fr; }
}

/* ── Card ────────────────────────────────────────────── */
.bjs-card {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    overflow: hidden;
}
.bjs-card-head {
    padding: 14px 20px; border-bottom: 1px solid #f0f0f0;
    display: flex; align-items: center; gap: 10px;
}
.bjs-card-head .icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
}
.bjs-card-head h6 {
    margin: 0; font-size: 14px; font-weight: 700; color: #2d3748;
}
.bjs-card-body { padding: 20px; }

/* ── Filter bar ───────────────────────────────────────── */
.filter-row {
    display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;
}
.filter-row select, .filter-row input {
    flex: 1; min-width: 140px;
    padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 9px;
    font-size: 13px; color: #2d3748; outline: none;
    transition: border-color .2s;
}
.filter-row select:focus, .filter-row input:focus {
    border-color: #805ad5;
}

/* ── Select-all bar ───────────────────────────────────── */
.sel-bar {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; background: #f7f3ff;
    border-radius: 9px; margin-bottom: 10px;
}
.sel-bar label { font-size: 13px; font-weight: 600; color: #553c9a; margin: 0; cursor: pointer; flex: 1; }
.sel-bar .count { font-size: 12px; color: #805ad5; font-weight: 700; }

/* ── Karyawan list ────────────────────────────────────── */
#karyawanList {
    max-height: calc(100vh - 380px);
    min-height: 300px;
    overflow-y: auto;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    background: #fafafa;
}
.dept-header {
    position: sticky; top: 0; z-index: 1;
    padding: 6px 14px;
    background: linear-gradient(90deg, #f3e8ff, #faf5ff);
    font-size: 11px; font-weight: 800; color: #553c9a;
    text-transform: uppercase; letter-spacing: .5px;
    border-bottom: 1px solid #e9d8fd;
    display: flex; align-items: center; gap: 6px;
}
.dept-header .dept-count {
    background: #805ad5; color: white;
    border-radius: 20px; padding: 1px 7px;
    font-size: 10px; font-weight: 700;
}
.kary-item {
    display: flex; align-items: center; gap: 12px;
    padding: 9px 14px; cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background .12s;
}
.kary-item:hover { background: #f7f3ff; }
.kary-item:last-child { border-bottom: none; }
.kary-item input[type=checkbox] {
    width: 16px; height: 16px; accent-color: #805ad5;
    flex-shrink: 0; cursor: pointer;
}
.kary-item .kary-info { flex: 1; min-width: 0; }
.kary-item .kary-nama {
    font-size: 13px; font-weight: 600; color: #2d3748;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.kary-item .kary-sub { font-size: 11px; color: #718096; }
.kary-item .shift-badge {
    font-size: 10px; font-weight: 700; padding: 2px 7px;
    border-radius: 6px; background: #bee3f8; color: #2c5282;
    white-space: nowrap; flex-shrink: 0;
}

/* ── Right panel form ─────────────────────────────────── */
.form-label { font-size: 12px; font-weight: 700; color: #4a5568; display: block; margin-bottom: 5px; }
.form-control-bjs {
    width: 100%; padding: 10px 13px;
    border: 1.5px solid #e2e8f0; border-radius: 9px;
    font-size: 13px; color: #2d3748; outline: none;
    transition: border-color .2s; box-sizing: border-box;
}
.form-control-bjs:focus { border-color: #805ad5; }

/* ── Hari checkbox pills ──────────────────────────────── */
.hari-grid { display: flex; gap: 6px; flex-wrap: wrap; }
.hari-pill {
    display: flex; align-items: center; gap: 5px;
    padding: 6px 11px; border: 1.5px solid #e2e8f0;
    border-radius: 8px; cursor: pointer; font-size: 12px;
    font-weight: 700; color: #4a5568; background: #f7fafc;
    transition: all .15s; user-select: none;
}
.hari-pill input { display: none; }
.hari-pill.active { background: #553c9a; border-color: #553c9a; color: white; }

/* ── Submit button ────────────────────────────────────── */
.btn-apply {
    width: 100%; padding: 13px;
    background: linear-gradient(135deg, #553c9a, #805ad5);
    color: white; border: none; border-radius: 10px;
    font-size: 15px; font-weight: 700; cursor: pointer;
    transition: opacity .2s; margin-top: 4px;
}
.btn-apply:hover { opacity: .9; }
.btn-apply:disabled { opacity: .5; cursor: not-allowed; }

/* ── Quick month shortcuts ────────────────────────────── */
.month-shortcuts { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.month-btn {
    padding: 4px 10px; border: 1.5px solid #e2e8f0; border-radius: 7px;
    font-size: 11px; font-weight: 600; color: #553c9a; background: #f7f3ff;
    cursor: pointer; transition: all .15s;
}
.month-btn:hover, .month-btn.active { background: #553c9a; color: white; border-color: #553c9a; }

/* ── Empty & loading states ───────────────────────────── */
.list-placeholder {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 50px 20px; color: #a0aec0;
    gap: 10px; font-size: 13px;
}
.list-placeholder i { font-size: 32px; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="bjs-header">
    <div>
        <h4><i class="fas fa-calendar-plus" style="margin-right:8px;"></i> Buat Jadwal Shift</h4>
        <p>Pilih karyawan, rentang tanggal, dan shift yang akan diterapkan</p>
    </div>
    <a href="{{ route('admin.laporan.jadwal-shift') }}"
       style="padding:9px 18px;background:rgba(255,255,255,.2);color:white;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Kembali ke Jadwal
    </a>
</div>

{{-- Success flash --}}
@if(session('success'))
<div style="background:#c6f6d5;border:1px solid #86efac;border-radius:10px;padding:12px 16px;color:#166534;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
    <i class="fas fa-check-circle" style="font-size:16px;"></i>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.employeshift.storeBulk') }}" id="mainForm">
@csrf
<input type="hidden" name="_redirect" value="{{ route('admin.laporan.buat-jadwal-shift') }}">

<div class="bjs-grid">

    {{-- ══ KOLOM KIRI: Pilih Karyawan ══════════════════════════════ --}}
    <div class="bjs-card">
        <div class="bjs-card-head">
            <div class="icon" style="background:#f3e8ff;color:#805ad5;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h6>Pilih Karyawan</h6>
                <div style="font-size:11px;color:#a0aec0;margin-top:1px;">Centang karyawan yang akan dijadwalkan</div>
            </div>
        </div>
        <div class="bjs-card-body">

            {{-- Filter --}}
            <div class="filter-row">
                <select id="filterDept" onchange="onDeptChange()">
                    <option value="">— Semua Departemen —</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                <select id="filterShiftKary">
                    <option value="">— Semua Shift —</option>
                    @foreach($shifts as $s)
                    <option value="{{ $s->id }}">{{ $s->nama_shift }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-row" style="margin-bottom:14px;">
                <input type="text" id="searchKary" placeholder="&#xf002;  Cari nama atau NIK…" oninput="onSearch(this.value)">
            </div>

            {{-- Select-all bar --}}
            <div class="sel-bar">
                <input type="checkbox" id="chkAll" onchange="toggleAll(this)" style="width:16px;height:16px;accent-color:#805ad5;cursor:pointer;flex-shrink:0;">
                <label for="chkAll">Centang semua yang tampil</label>
                <span class="count" id="selCount">0 dipilih</span>
            </div>

            {{-- Karyawan list --}}
            <div id="karyawanList">
                <div class="list-placeholder">
                    <i class="fas fa-building"></i>
                    <span>Pilih departemen atau ketik nama untuk menampilkan karyawan</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ KOLOM KANAN: Pengaturan Jadwal ══════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Card Pengaturan --}}
        <div class="bjs-card">
            <div class="bjs-card-head">
                <div class="icon" style="background:#ebf8ff;color:#2b6cb0;">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h6>Pengaturan Jadwal</h6>
                    <div style="font-size:11px;color:#a0aec0;margin-top:1px;">Shift, periode, dan hari kerja</div>
                </div>
            </div>
            <div class="bjs-card-body" style="display:flex;flex-direction:column;gap:14px;">

                {{-- Shift --}}
                <div>
                    <label class="form-label">Shift <span style="color:#e53e3e;">*</span></label>
                    <select name="shift_id" class="form-control-bjs" required>
                        <option value="">— Pilih Shift —</option>
                        @foreach($shifts as $s)
                        <option value="{{ $s->id }}">
                            {{ $s->nama_shift }}
                            ({{ substr($s->jam_masuk,0,5) }}–{{ substr($s->jam_keluar,0,5) }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Shortcut bulan --}}
                <div>
                    <label class="form-label">Bulan Cepat</label>
                    <div class="month-shortcuts" id="monthShortcuts">
                        @php
                            $now = now();
                            $months = [
                                ['c' => $now->copy()->subMonth()->startOfMonth(), 'label' => $now->copy()->subMonth()->translatedFormat('M Y')],
                                ['c' => $now->copy()->startOfMonth(),             'label' => $now->translatedFormat('M Y')],
                                ['c' => $now->copy()->addMonth()->startOfMonth(), 'label' => $now->copy()->addMonth()->translatedFormat('M Y')],
                            ];
                        @endphp
                        @foreach($months as $m)
                        <button type="button" class="month-btn {{ $loop->index === 1 ? 'active' : '' }}"
                                data-from="{{ $m['c']->format('Y-m-d') }}"
                                data-to="{{ $m['c']->copy()->endOfMonth()->format('Y-m-d') }}"
                                onclick="setMonth(this)">
                            {{ $m['label'] }}
                        </button>
                        @endforeach
                        <button type="button" class="month-btn" data-custom="1" onclick="toggleCustom()">
                            <i class="fas fa-pen" style="font-size:10px;"></i> Custom
                        </button>
                    </div>
                </div>

                {{-- Tanggal --}}
                <div id="dateFields" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Dari Tanggal <span style="color:#e53e3e;">*</span></label>
                        <input type="date" name="date_from" id="dateFrom" class="form-control-bjs" required
                               value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="form-label">Sampai Tanggal <span style="color:#e53e3e;">*</span></label>
                        <input type="date" name="date_to" id="dateTo" class="form-control-bjs" required
                               value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                    </div>
                </div>

                {{-- Hari --}}
                <div>
                    <label class="form-label" style="margin-bottom:8px;">Hari yang Disertakan</label>
                    <div class="hari-grid">
                        @foreach(['Min'=>0,'Sen'=>1,'Sel'=>2,'Rab'=>3,'Kam'=>4,'Jum'=>5,'Sab'=>6] as $hr => $idx)
                        <label class="hari-pill {{ $idx >= 1 && $idx <= 5 ? 'active' : '' }}" id="pill_{{ $idx }}">
                            <input type="checkbox" name="hari[]" value="{{ $idx }}"
                                   {{ $idx >= 1 && $idx <= 5 ? 'checked' : '' }}
                                   onchange="syncPill({{ $idx }})">
                            {{ $hr }}
                        </label>
                        @endforeach
                    </div>
                    <div style="margin-top:8px;display:flex;gap:6px;">
                        <button type="button" onclick="setHari('weekday')"
                                style="font-size:11px;padding:4px 9px;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;cursor:pointer;color:#4a5568;font-weight:600;">
                            Sen–Jum
                        </button>
                        <button type="button" onclick="setHari('all')"
                                style="font-size:11px;padding:4px 9px;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;cursor:pointer;color:#4a5568;font-weight:600;">
                            Semua Hari
                        </button>
                        <button type="button" onclick="setHari('none')"
                                style="font-size:11px;padding:4px 9px;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;cursor:pointer;color:#4a5568;font-weight:600;">
                            Reset
                        </button>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="form-label">Keterangan <span style="color:#a0aec0;">(opsional)</span></label>
                    <input type="text" name="keterangan" class="form-control-bjs"
                           placeholder="mis: Jadwal reguler Juni 2026" maxlength="255">
                </div>

                {{-- Ubah shift default --}}
                <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:9px;padding:12px 14px;">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;margin:0;">
                        <input type="checkbox" name="ubah_shift_default" value="1"
                               style="width:16px;height:16px;accent-color:#276749;flex-shrink:0;margin-top:2px;">
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#276749;">Sekaligus ubah shift default</div>
                            <div style="font-size:11px;color:#2f855a;margin-top:2px;">Shift default karyawan yang dipilih akan diubah ke shift ini secara permanen</div>
                        </div>
                    </label>
                </div>

            </div>
        </div>

        {{-- Card Summary + Submit --}}
        <div class="bjs-card">
            <div class="bjs-card-head">
                <div class="icon" style="background:#f0fff4;color:#276749;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h6>Ringkasan</h6>
            </div>
            <div class="bjs-card-body">
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;font-size:13px;color:#4a5568;">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Karyawan dipilih</span>
                        <strong id="summaryKary" style="color:#553c9a;">0 orang</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Periode</span>
                        <strong id="summaryPeriode" style="color:#2b6cb0;">—</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Hari per minggu</span>
                        <strong id="summaryHari" style="color:#276749;">5 hari</strong>
                    </div>
                </div>

                <button type="submit" class="btn-apply" id="btnApply" disabled
                        onclick="return confirmApply()">
                    <i class="fas fa-calendar-check"></i>
                    <span id="btnLabel">Terapkan Jadwal</span>
                </button>

                <div style="margin-top:10px;font-size:11px;color:#a0aec0;text-align:center;">
                    <i class="fas fa-info-circle"></i>
                    Jadwal yang sudah ada pada tanggal yang sama akan ditimpa otomatis.
                </div>
            </div>
        </div>

    </div>{{-- /kolom kanan --}}

</div>{{-- /bjs-grid --}}
</form>
@push('scripts')
<script>
/* ── Data dari server ──────────────────────────────────────── */
const AJAX_URL   = '{{ route('admin.karyawan-by-dept') }}';
let allData      = [];   // full list dari server
let filteredData = [];   // setelah filter search/shift

/* ── Init ─────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    updateSummaryPeriode();
    updateSummaryHari();

    document.getElementById('dateFrom').addEventListener('change', updateSummaryPeriode);
    document.getElementById('dateTo').addEventListener('change',   updateSummaryPeriode);
    document.getElementById('filterShiftKary').addEventListener('change', applyFilters);

    loadKaryawan('');
});

/* ── Load karyawan via AJAX ───────────────────────────────── */
function onDeptChange() {
    const dept = document.getElementById('filterDept').value;
    loadKaryawan(dept);
}

function loadKaryawan(dept) {
    const el  = document.getElementById('karyawanList');
    el.innerHTML = '<div class="list-placeholder"><i class="fas fa-spinner fa-spin"></i><span>Memuat karyawan…</span></div>';

    const url = dept ? AJAX_URL + '?department_id=' + encodeURIComponent(dept) : AJAX_URL;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            allData      = data;
            filteredData = data;
            document.getElementById('searchKary').value    = '';
            document.getElementById('filterShiftKary').value = '';
            renderList(data, true);
        })
        .catch(() => {
            el.innerHTML = '<div class="list-placeholder"><i class="fas fa-exclamation-circle" style="color:#e53e3e;"></i><span>Gagal memuat data.</span></div>';
        });
}

/* ── Filter search & shift ────────────────────────────────── */
function onSearch() { applyFilters(); }

function applyFilters() {
    const q     = document.getElementById('searchKary').value.toLowerCase().trim();
    const shift = document.getElementById('filterShiftKary').value;

    filteredData = allData.filter(k => {
        const matchQ     = !q     || k.nama.toLowerCase().includes(q) || (k.nik ?? '').toLowerCase().includes(q);
        const matchShift = !shift || String(k.shift_id) === String(shift);
        return matchQ && matchShift;
    });

    renderList(filteredData, false);
}

/* ── Render list ──────────────────────────────────────────── */
function renderList(data, checkAll) {
    const el = document.getElementById('karyawanList');
    const dept = document.getElementById('filterDept').value;

    if (!data.length) {
        el.innerHTML = '<div class="list-placeholder"><i class="fas fa-search"></i><span>Tidak ada karyawan ditemukan.</span></div>';
        updateSelCount();
        return;
    }

    // Pertahankan status checked karyawan yang sudah dicentang sebelumnya
    const prevChecked = {};
    el.querySelectorAll('input[type=checkbox]').forEach(cb => {
        prevChecked[cb.value] = cb.checked;
    });

    let html = '';
    let prevDept = null;

    data.forEach(k => {
        const deptName = k.dept_name ?? 'Tanpa Departemen';

        // Header departemen hanya jika tampil lintas-dept
        if (!dept && deptName !== prevDept) {
            const countInDept = data.filter(x => (x.dept_name ?? 'Tanpa Departemen') === deptName).length;
            html += `
            <div class="dept-header">
                <i class="fas fa-building"></i>
                ${deptName}
                <span class="dept-count">${countInDept}</span>
            </div>`;
            prevDept = deptName;
        }

        const checked = checkAll ? true : (String(k.id) in prevChecked ? prevChecked[String(k.id)] : false);
        const shiftLabel = k.shift_nama ? k.shift_nama : '';

        html += `
        <label class="kary-item">
            <input type="checkbox" name="karyawan_ids[]" value="${k.id}"
                   ${checked ? 'checked' : ''} onchange="updateSelCount()">
            <div class="kary-info">
                <div class="kary-nama">${k.nama}</div>
                <div class="kary-sub">${k.nik ? k.nik + ' · ' : ''}${k.jabatan ?? ''}</div>
            </div>
            ${shiftLabel ? `<span class="shift-badge">${shiftLabel}</span>` : ''}
        </label>`;
    });

    el.innerHTML = html;
    updateSelCount();
}

/* ── Toggle all ───────────────────────────────────────────── */
function toggleAll(cb) {
    document.querySelectorAll('#karyawanList input[type=checkbox]').forEach(c => c.checked = cb.checked);
    updateSelCount();
}

/* ── Count selected ───────────────────────────────────────── */
function updateSelCount() {
    const n   = document.querySelectorAll('#karyawanList input[type=checkbox]:checked').length;
    const tot = document.querySelectorAll('#karyawanList input[type=checkbox]').length;

    document.getElementById('selCount').textContent   = n + ' dipilih';
    document.getElementById('summaryKary').textContent = n + ' orang';

    const btn = document.getElementById('btnApply');
    btn.disabled = (n === 0);

    // Sync chkAll state
    const chkAll = document.getElementById('chkAll');
    chkAll.checked       = (n > 0 && n === tot);
    chkAll.indeterminate = (n > 0 && n < tot);

    updateBtnLabel(n);
}

function updateBtnLabel(n) {
    document.getElementById('btnLabel').textContent =
        n > 0 ? `Terapkan ke ${n} Karyawan` : 'Terapkan Jadwal';
}

/* ── Month shortcuts ──────────────────────────────────────── */
function setMonth(btn) {
    document.querySelectorAll('.month-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('dateFrom').value = btn.dataset.from;
    document.getElementById('dateTo').value   = btn.dataset.to;
    updateSummaryPeriode();
}

let customMode = false;
function toggleCustom() {
    customMode = !customMode;
    // Custom mode: just let user pick date manually — highlight button
    document.querySelectorAll('.month-btn').forEach(b => {
        if (b.dataset.custom) b.classList.toggle('active', customMode);
        else b.classList.remove('active');
    });
}

function updateSummaryPeriode() {
    const f = document.getElementById('dateFrom').value;
    const t = document.getElementById('dateTo').value;
    if (f && t) {
        const df = new Date(f), dt = new Date(t);
        const opt = { day: 'numeric', month: 'short', year: 'numeric' };
        document.getElementById('summaryPeriode').textContent =
            df.toLocaleDateString('id-ID', opt) + ' – ' + dt.toLocaleDateString('id-ID', opt);
    } else {
        document.getElementById('summaryPeriode').textContent = '—';
    }
}

/* ── Hari pills ───────────────────────────────────────────── */
function syncPill(idx) {
    const cb   = document.querySelector(`#pill_${idx} input`);
    const pill = document.getElementById('pill_' + idx);
    pill.classList.toggle('active', cb.checked);
    updateSummaryHari();
}

function setHari(mode) {
    for (let i = 0; i <= 6; i++) {
        const cb   = document.querySelector(`#pill_${i} input`);
        const pill = document.getElementById('pill_' + i);
        let checked;
        if (mode === 'weekday') checked = (i >= 1 && i <= 5);
        else if (mode === 'all') checked = true;
        else checked = false;
        cb.checked = checked;
        pill.classList.toggle('active', checked);
    }
    updateSummaryHari();
}

function updateSummaryHari() {
    const n = document.querySelectorAll('.hari-pill input:checked').length;
    document.getElementById('summaryHari').textContent = n + ' hari';
}

/* ── Confirm submit ───────────────────────────────────────── */
function confirmApply() {
    const n = document.querySelectorAll('#karyawanList input[type=checkbox]:checked').length;
    if (!n) { alert('Pilih minimal 1 karyawan.'); return false; }
    const f = document.getElementById('dateFrom').value;
    const t = document.getElementById('dateTo').value;
    if (!f || !t) { alert('Lengkapi rentang tanggal.'); return false; }
    const hariN = document.querySelectorAll('.hari-pill input:checked').length;
    if (!hariN) { alert('Pilih minimal 1 hari.'); return false; }
    return confirm(`Terapkan jadwal ke ${n} karyawan?\nPeriode: ${f} s/d ${t}`);
}
</script>
@endpush
@endsection

