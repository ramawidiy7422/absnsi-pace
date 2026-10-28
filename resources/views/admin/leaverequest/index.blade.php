@extends('layouts.admin')
@section('title', 'Pengajuan Cuti & Izin')
@section('page-title', 'Pengajuan Cuti & Izin')

@section('content')

@push('styles')
<style>
.badge-pending_spv, .badge-pending_manajer, .badge-pending_hrd { background:#fef9c3; color:#854d0e; }
.badge-disetujui { background:#c6f6d5; color:#276749; }
.badge-ditolak   { background:#fed7e2; color:#c53030; }
.filter-bar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:18px; }
</style>
@endpush

<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-calendar-alt" style="color:#4299e1;"></i> Semua Pengajuan Cuti & Izin</h6>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.leaverequest.export-pdf', request()->query()) }}"
           style="padding:6px 14px;background:#c53030;color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('admin.leaverequest.export-csv', request()->query()) }}"
           style="padding:6px 14px;background:#1a5c36;color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        @endif
        @if(auth()->user()->hasPermission('cuti.approve'))
        <div id="bulkBar" style="display:none;gap:8px;align-items:center;">
            <span id="bulkCount" style="font-size:12px;color:#718096;"></span>
            <form method="POST" action="{{ route('admin.leaverequest.bulk-approve') }}" id="formBulkApprove">
                @csrf
                <div id="hiddenApproveIds"></div>
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Setujui semua yang dipilih?')">
                    <i class="fas fa-check"></i> Setujui Terpilih
                </button>
            </form>
            <form method="POST" action="{{ route('admin.leaverequest.bulk-reject') }}" id="formBulkReject">
                @csrf
                <div id="hiddenRejectIds"></div>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tolak semua yang dipilih?')">
                    <i class="fas fa-times"></i> Tolak Terpilih
                </button>
            </form>
        </div>
        @endif
        </div>
    </div>
    <div class="card-body">
        <form method="GET" id="filterForm">
            <div class="filter-bar">
                <div style="position:relative; flex:1; min-width:200px; max-width:280px;">
                    <i class="fas fa-search" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#a0aec0; font-size:13px;"></i>
                    <input type="text" name="search" class="form-control" style="padding-left:34px;"
                           placeholder="Cari nama karyawan..." value="{{ request('search') }}"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('filterForm').submit();}">
                </div>
                <select name="status" class="form-select" style="max-width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending_spv"     {{ request('status')=='pending_spv'     ? 'selected':'' }}>Menunggu SPV</option>
                    <option value="pending_manajer" {{ request('status')=='pending_manajer' ? 'selected':'' }}>Menunggu Manajer</option>
                    <option value="pending_hrd"     {{ request('status')=='pending_hrd'     ? 'selected':'' }}>Menunggu HRD</option>
                    <option value="disetujui" {{ request('status')=='disetujui' ? 'selected':'' }}>Disetujui</option>
                    <option value="ditolak"   {{ request('status')=='ditolak'   ? 'selected':'' }}>Ditolak</option>
                </select>
                <select name="type" class="form-select" style="max-width:140px;" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="cuti"  {{ request('type')=='cuti'  ? 'selected':'' }}>Cuti</option>
                    <option value="izin"  {{ request('type')=='izin'  ? 'selected':'' }}>Izin</option>
                    <option value="sakit" {{ request('type')=='sakit' ? 'selected':'' }}>Sakit</option>
                </select>
                <select name="bulan" class="form-select" style="max-width:150px;" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                @if(request()->hasAny(['search','status','type','bulan']))
                <a href="{{ route('admin.leaverequest.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Reset
                </a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="checkAll" title="Pilih semua pending" style="cursor:pointer;"></th>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Tipe</th>
                        <th>Tanggal Mulai</th>
                        <th class="hide-mobile">Tanggal Selesai</th>
                        <th class="hide-mobile">Durasi</th>
                        <th class="hide-mobile">Keterangan</th>
                        <th>Status</th>
                        <th class="hide-mobile">Disetujui Oleh</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaverequests as $i => $lr)
                    <tr>
                        <td>
                            @if(!$lr->isFinal() && auth()->user()->can('approve-leaverequest', $lr))
                            <input type="checkbox" class="row-check" value="{{ $lr->id }}" style="cursor:pointer;">
                            @endif
                        </td>
                        <td style="color:#94a3b8; font-size:12px;">{{ $leaverequests->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('admin.karyawan.show', $lr->karyawan_id) }}#tab-ketidakhadiran"
                               style="color:#2d3748;text-decoration:none;">
                                <strong>{{ $lr->karyawan?->nama ?? '-' }}</strong>
                            </a><br>
                            <small style="color:#94a3b8;">{{ $lr->karyawan?->nik ?? '' }}</small>
                        </td>
                        <td>
                            @php
                                $typeColor = ['cuti'=>'#3b82f6','izin'=>'#f59e0b','sakit'=>'#ef4444'];
                                $typeBg    = ['cuti'=>'#eff6ff','izin'=>'#fffbeb','sakit'=>'#fef2f2'];
                            @endphp
                            <span style="background:{{ $typeBg[$lr->type] ?? '#f3f4f6' }}; color:{{ $typeColor[$lr->type] ?? '#6b7280' }}; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; text-transform:capitalize;">
                                {{ ucfirst($lr->type) }}
                            </span>
                        </td>
                        <td style="font-size:13px;">{{ $lr->start_date->format('d/m/Y') }}</td>
                        <td class="hide-mobile" style="font-size:13px;">{{ $lr->end_date->format('d/m/Y') }}</td>
                        <td class="hide-mobile">
                            @php $durasi = $lr->start_date->diffInDays($lr->end_date) + 1; @endphp
                            <span style="font-size:12px; font-weight:600; color:#4a5568;">{{ $durasi }} hari</span>
                        </td>
                        <td class="hide-mobile" style="max-width:150px; font-size:12px; color:#718096; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $lr->keterangan ?? '-' }}
                        </td>
                        <td>
                            @php
                                $statusLabel = match($lr->status) {
                                    'pending_spv'     => 'Menunggu SPV',
                                    'pending_manajer' => 'Menunggu Manajer',
                                    'pending_hrd'     => 'Menunggu HRD',
                                    default           => ucfirst($lr->status),
                                };
                            @endphp
                            <span class="badge badge-{{ $lr->status }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="hide-mobile" style="font-size:12px; color:#718096;">
                            @if($lr->approver)
                                {{ $lr->approver->name }}<br>
                                <small>{{ $lr->approved_at?->format('d/m/Y H:i') }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:4px; justify-content:center; align-items:center;">
                                <button onclick="showDetail({{ $lr->id }})"
                                        class="btn btn-secondary btn-sm" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                @if(!$lr->isFinal() && auth()->user()->can('approve-leaverequest', $lr))
                                <form method="POST" action="{{ route('admin.leaverequest.approve', $lr) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Setujui"
                                            onclick="return confirm('Setujui pengajuan ini?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.leaverequest.reject', $lr) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" title="Tolak"
                                            onclick="return confirm('Tolak pengajuan ini?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align:center; padding:40px; color:#94a3b8;">
                            <i class="fas fa-calendar-times" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
                            Belum ada pengajuan cuti/izin
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; flex-wrap:wrap; gap:8px;">
            <div style="font-size:12px; color:#94a3b8;">
                Menampilkan {{ $leaverequests->firstItem() }}–{{ $leaverequests->lastItem() }}
                dari {{ $leaverequests->total() }} pengajuan
            </div>
            <div class="pagination">{{ $leaverequests->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>
@push('scripts')
<script>
const DETAIL_URL  = '{{ url("admin/leaverequest") }}';
const APPROVE_URL = '{{ url("admin/leaverequest") }}';
const CSRF        = '{{ csrf_token() }}';
const canApprove = {{ auth()->user()->hasPermission('cuti.approve') ? 'true' : 'false' }};

// Bulk select logic
function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const bar = document.getElementById('bulkBar');
    const count = document.getElementById('bulkCount');
    if (!bar) return;

    if (checked.length > 0) {
        bar.style.display = 'flex';
        count.textContent = checked.length + ' terpilih';
        // Sync hidden IDs to both forms
        ['hiddenApproveIds','hiddenRejectIds'].forEach(divId => {
            const div = document.getElementById(divId);
            div.innerHTML = '';
            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = cb.value;
                div.appendChild(inp);
            });
        });
    } else {
        bar.style.display = 'none';
    }
}

document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

// ── Modal Detail ──────────────────────────────────────────────────────────────
const typeLabel = { cuti:'Cuti', izin:'Izin', sakit:'Sakit' };
const typeColor = { cuti:['#eff6ff','#3b82f6'], izin:['#fffbeb','#f59e0b'], sakit:['#fef2f2','#ef4444'] };
const statColor = {
    pending_spv:['#fef9c3','#854d0e'], pending_manajer:['#fef9c3','#854d0e'], pending_hrd:['#fef9c3','#854d0e'],
    disetujui:['#c6f6d5','#276749'], ditolak:['#fed7e2','#c53030'],
};
const statLabel = {
    pending_spv:'Menunggu SPV', pending_manajer:'Menunggu Manajer', pending_hrd:'Menunggu HRD',
    disetujui:'Disetujui', ditolak:'Ditolak',
};

function showDetail(id) {
    const modal = document.getElementById('modalDetail');
    modal.style.display = 'flex';
    document.getElementById('md-loading').style.display = 'block';
    document.getElementById('md-content').style.display = 'none';

    fetch(`${DETAIL_URL}/${id}/detail`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        const k = d.karyawan;
        const [tBg, tCol] = typeColor[d.type] ?? ['#f3f4f6','#374151'];
        const [sBg, sCol] = statColor[d.status] ?? ['#f3f4f6','#374151'];

        // Header
        document.getElementById('md-title').textContent = `Pengajuan ${typeLabel[d.type] ?? d.type} — ${k.nama}`;
        document.getElementById('md-subtitle').textContent = `${k.nik} · ${k.departemen}`;

        // Karyawan card
        document.getElementById('md-karyawan').innerHTML = `
            <div style="width:48px;height:48px;border-radius:50%;overflow:hidden;flex-shrink:0;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
                ${k.foto ? `<img src="${k.foto}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fas fa-user" style="color:#a0aec0;font-size:20px;"></i>`}
            </div>
            <div>
                <div style="font-weight:700;font-size:14px;color:#2d3748;">${k.nama}</div>
                <div style="font-size:12px;color:#718096;">${k.jabatan ?? '-'} · ${k.departemen ?? '-'}</div>
                <span style="background:#ebf8ff;color:#2b6cb0;padding:1px 8px;border-radius:10px;font-size:11px;font-weight:700;">${k.nik}</span>
            </div>`;

        // Detail boxes
        document.getElementById('md-detail').innerHTML = `
            <div style="background:#f8fafc;border-radius:8px;padding:12px;">
                <div style="font-size:10px;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Tipe</div>
                <span style="background:${tBg};color:${tCol};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">${typeLabel[d.type] ?? d.type}</span>
            </div>
            <div style="background:#f8fafc;border-radius:8px;padding:12px;">
                <div style="font-size:10px;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Status</div>
                <span style="background:${sBg};color:${sCol};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">${statLabel[d.status] ?? d.status}</span>
            </div>
            <div style="background:#f8fafc;border-radius:8px;padding:12px;">
                <div style="font-size:10px;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Tanggal Mulai</div>
                <div style="font-weight:700;font-size:13px;color:#2d3748;">${d.start_date}</div>
            </div>
            <div style="background:#f8fafc;border-radius:8px;padding:12px;">
                <div style="font-size:10px;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Tanggal Selesai</div>
                <div style="font-weight:700;font-size:13px;color:#2d3748;">${d.end_date}</div>
            </div>
            <div style="background:#ebf8ff;border-radius:8px;padding:12px;grid-column:span 2;text-align:center;">
                <div style="font-size:10px;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Durasi</div>
                <div style="font-size:22px;font-weight:800;color:#2b6cb0;">${d.durasi} <span style="font-size:13px;font-weight:500;">hari</span></div>
            </div>`;

        // Saldo (hanya untuk tipe cuti)
        const saldoWrap = document.getElementById('md-saldo');
        if (d.type === 'cuti') {
            saldoWrap.style.display = 'block';
            document.getElementById('md-saldo-boxes').innerHTML = `
                <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:8px;padding:10px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#276749;">${d.saldo.jatah}</div>
                    <div style="font-size:10px;color:#718096;">Jatah</div>
                </div>
                <div style="background:#fff5f5;border:1px solid #feb2b2;border-radius:8px;padding:10px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#c53030;">${d.saldo.terpakai}</div>
                    <div style="font-size:10px;color:#718096;">Terpakai</div>
                </div>
                <div style="background:#ebf8ff;border:1px solid #90cdf4;border-radius:8px;padding:10px;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#2b6cb0;">${d.saldo.sisa}</div>
                    <div style="font-size:10px;color:#718096;">Sisa</div>
                </div>`;
        } else {
            saldoWrap.style.display = 'none';
        }

        // Stepper progress tahap approval (hanya untuk tipe cuti yang pakai alur berjenjang)
        const stageWrap = document.getElementById('md-stage-wrap');
        if (d.type === 'cuti') {
            stageWrap.style.display = 'block';
            const stageKeys = ['spv', 'manajer', 'hrd'];
            document.getElementById('md-stage-boxes').innerHTML = d.stage_progress.map((s, idx) => {
                const isRejectedHere = d.status === 'ditolak' && d.rejected_stage === stageKeys[idx];
                const done = s.by !== null;
                const bg = isRejectedHere ? '#fed7e2' : (done ? '#c6f6d5' : '#f7fafc');
                const col = isRejectedHere ? '#c53030' : (done ? '#276749' : '#a0aec0');
                const icon = isRejectedHere ? 'fa-times-circle' : (done ? 'fa-check-circle' : 'fa-clock');
                return `<div style="background:${bg};color:${col};border-radius:8px;padding:8px;text-align:center;">
                    <i class="fas ${icon}" style="font-size:14px;"></i>
                    <div style="font-size:11px;font-weight:700;margin-top:2px;">${s.label}</div>
                    <div style="font-size:10px;">${s.by ?? 'Menunggu'}</div>
                </div>`;
            }).join('');
        } else {
            stageWrap.style.display = 'none';
        }

        // Keterangan
        const ketWrap = document.getElementById('md-ket-wrap');
        if (d.keterangan) {
            ketWrap.style.display = 'block';
            document.getElementById('md-ket').textContent = d.keterangan;
        } else { ketWrap.style.display = 'none'; }

        // Approver
        const apvWrap = document.getElementById('md-approver-wrap');
        if (d.approved_by) {
            apvWrap.style.display = 'block';
            document.getElementById('md-approver').innerHTML =
                `<i class="fas fa-user-check" style="color:#48bb78;"></i> <strong>${d.approved_by}</strong> <span style="color:#a0aec0;font-size:11px;">· ${d.approved_at}</span>`;
        } else { apvWrap.style.display = 'none'; }

        // Tombol aksi
        let actions = `<a href="{{ url('admin/karyawan') }}/${k.id}#tab-ketidakhadiran"
            style="padding:8px 16px;background:#f7fafc;color:#4a5568;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <i class="fas fa-user"></i> Buka Profil Karyawan
        </a>`;

        if (d.boleh_approve) {
            actions += `
            <form method="POST" action="${APPROVE_URL}/${d.id}/approve" style="margin:0;">
                <input type="hidden" name="_token" value="${CSRF}">
                <button type="submit" onclick="return confirm('Setujui pengajuan ini?')"
                    style="padding:8px 16px;background:#48bb78;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-check"></i> Setujui
                </button>
            </form>
            <form method="POST" action="${APPROVE_URL}/${d.id}/reject" style="margin:0;">
                <input type="hidden" name="_token" value="${CSRF}">
                <button type="submit" onclick="return confirm('Tolak pengajuan ini?')"
                    style="padding:8px 16px;background:#f56565;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-times"></i> Tolak
                </button>
            </form>`;
        }

        document.getElementById('md-actions').innerHTML = actions;
        document.getElementById('md-loading').style.display = 'none';
        document.getElementById('md-content').style.display = 'block';
    })
    .catch(() => {
        document.getElementById('md-loading').innerHTML =
            '<p style="text-align:center;color:#c53030;padding:20px;">Gagal memuat data.</p>';
    });
}

function closeDetail() {
    document.getElementById('modalDetail').style.display = 'none';
}
document.getElementById('modalDetail').addEventListener('click', function(e) {
    if (e.target === this) closeDetail();
});
</script>
@endpush
@endsection

{{-- Modal Detail Pengajuan --}}
<div id="modalDetail" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:520px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);overflow:hidden;">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#1a202c,#2d3748);padding:20px 24px;display:flex;justify-content:space-between;align-items:center;">
            <div style="color:#fff;">
                <div style="font-size:15px;font-weight:700;" id="md-title">Detail Pengajuan</div>
                <div style="font-size:12px;color:#a0aec0;margin-top:2px;" id="md-subtitle"></div>
            </div>
            <button onclick="closeDetail()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;width:32px;height:32px;border-radius:50%;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>

        <div style="padding:20px 24px;" id="md-loading">
            <div style="text-align:center;padding:30px;color:#94a3b8;">
                <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i>
                <p style="margin-top:8px;font-size:13px;">Memuat data...</p>
            </div>
        </div>

        <div style="padding:0 24px 20px;" id="md-content" style="display:none;">

            {{-- Info Karyawan --}}
            <div style="display:flex;align-items:center;gap:14px;padding:16px;background:#f8fafc;border-radius:10px;margin-bottom:16px;" id="md-karyawan"></div>

            {{-- Detail Pengajuan --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;" id="md-detail"></div>

            {{-- Progress tahap approval (tampil jika tipe=cuti) --}}
            <div id="md-stage-wrap" style="display:none;margin-bottom:16px;">
                <div style="font-size:11px;font-weight:700;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Progress Approval</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;" id="md-stage-boxes"></div>
            </div>

            {{-- Saldo Cuti (tampil jika tipe=cuti) --}}
            <div id="md-saldo" style="display:none;margin-bottom:16px;">
                <div style="font-size:11px;font-weight:700;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Saldo Cuti {{ now()->year }}</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;" id="md-saldo-boxes"></div>
            </div>

            {{-- Keterangan --}}
            <div id="md-ket-wrap" style="margin-bottom:16px;display:none;">
                <div style="font-size:11px;font-weight:700;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Keterangan</div>
                <div id="md-ket" style="font-size:13px;color:#4a5568;background:#f8fafc;padding:10px 14px;border-radius:8px;border-left:3px solid #4299e1;"></div>
            </div>

            {{-- Disetujui oleh --}}
            <div id="md-approver-wrap" style="margin-bottom:16px;display:none;">
                <div style="font-size:11px;font-weight:700;color:#a0aec0;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Diproses Oleh</div>
                <div id="md-approver" style="font-size:13px;color:#4a5568;"></div>
            </div>

            {{-- Tombol Aksi --}}
            <div id="md-actions" style="display:flex;gap:8px;justify-content:flex-end;padding-top:12px;border-top:1px solid #f0f0f0;flex-wrap:wrap;"></div>
        </div>
    </div>
</div>

