@extends('layouts.admin')
@section('title', 'Koreksi Absensi')
@section('page-title', 'Koreksi Absensi')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#1e40af,#3b82f6);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-edit"></i> Koreksi Absensi</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Pengajuan koreksi jam masuk/keluar dari karyawan</p>
    </div>
    @if($totalPending > 0)
    <span style="background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:700;">
        <i class="fas fa-clock"></i> {{ $totalPending }} menunggu
    </span>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success" style="border-radius:10px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="border-radius:10px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:150px;">
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Cari Karyawan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIK..." class="form-control" style="font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Status</label>
                <select name="status" class="form-select" style="font-size:13px;">
                    <option value="">Semua</option>
                    <option value="pending"   {{ request('status')=='pending'   ?'selected':'' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status')=='disetujui' ?'selected':'' }}>Disetujui</option>
                    <option value="ditolak"   {{ request('status')=='ditolak'   ?'selected':'' }}>Ditolak</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="font-size:13px;">
                    <option value="">Semua</option>
                    @foreach($departemen as $d)
                        <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Bulan</label>
                <select name="bulan" class="form-select" style="font-size:13px;">
                    <option value="">Semua</option>
                    @for($m=1;$m<=12;$m++)
                        <option value="{{ $m }}" {{ request('bulan')==$m?'selected':'' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="font-size:13px;padding:8px 16px;">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('admin.koreksi-absensi.index') }}" class="btn btn-secondary" style="font-size:13px;padding:8px 16px;">Reset</a>
        </form>
    </div>
</div>

{{-- Bulk approve --}}
<form id="bulkForm" method="POST" action="{{ route('admin.koreksi-absensi.bulk-approve') }}">
@csrf
<div class="card">
    <div class="card-body" style="padding:0;">
        <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <div style="display:flex;align-items:center;gap:10px;">
                <input type="checkbox" id="checkAll" style="width:16px;height:16px;" onchange="toggleAll(this)">
                <label for="checkAll" style="margin:0;font-size:13px;cursor:pointer;">Pilih Semua</label>
            </div>
            <button type="submit" class="btn btn-success" style="font-size:12px;padding:6px 14px;"
                    onclick="return confirm('Setujui semua yang dipilih?')">
                <i class="fas fa-check-double"></i> Bulk Setujui
            </button>
        </div>

        <div style="overflow-x:auto;">
        <table class="table" style="margin:0;font-size:13px;">
            <thead style="background:#f7fafc;">
                <tr>
                    <th style="width:36px;padding:10px 16px;"></th>
                    <th style="padding:10px 8px;">Karyawan</th>
                    <th style="padding:10px 8px;">Tanggal</th>
                    <th style="padding:10px 8px;">Tipe</th>
                    <th style="padding:10px 8px;">Jam Diajukan</th>
                    <th style="padding:10px 8px;">Alasan</th>
                    <th style="padding:10px 8px;">Status</th>
                    <th style="padding:10px 16px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($koreksiList as $k)
            <tr style="{{ $k->status==='pending'?'background:#fffbeb;':'' }}">
                <td style="padding:10px 16px;">
                    @if($k->status === 'pending')
                    <input type="checkbox" name="ids[]" value="{{ $k->id }}" style="width:15px;height:15px;" class="row-check">
                    @endif
                </td>
                <td style="padding:10px 8px;">
                    <div style="font-weight:700;">{{ $k->karyawan->nama }}</div>
                    <div style="font-size:11px;color:#718096;">{{ $k->karyawan->department?->name ?? '-' }}</div>
                </td>
                <td style="padding:10px 8px;white-space:nowrap;">{{ $k->tanggal->translatedFormat('d M Y') }}</td>
                <td style="padding:10px 8px;">
                    <span style="background:#dbeafe;color:#1e40af;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:700;">{{ $k->label_tipe }}</span>
                </td>
                <td style="padding:10px 8px;font-size:12px;">
                    @if($k->jam_masuk_diajukan)
                    <div>Masuk: <strong>{{ substr($k->jam_masuk_diajukan,0,5) }}</strong></div>
                    @endif
                    @if($k->jam_keluar_diajukan)
                    <div>Keluar: <strong>{{ substr($k->jam_keluar_diajukan,0,5) }}</strong></div>
                    @endif
                </td>
                <td style="padding:10px 8px;max-width:200px;">
                    <div style="font-size:12px;color:#4a5568;">{{ $k->alasan }}</div>
                    @if($k->lampiran)
                    <a href="{{ Storage::url($k->lampiran) }}" target="_blank" style="font-size:11px;color:#3b82f6;">
                        <i class="fas fa-paperclip"></i> Lihat Lampiran
                    </a>
                    @endif
                    @if($k->catatan_approver)
                    <div style="font-size:11px;color:#e53e3e;margin-top:3px;"><i class="fas fa-comment"></i> {{ $k->catatan_approver }}</div>
                    @endif
                </td>
                <td style="padding:10px 8px;">
                    @if($k->status === 'pending')
                        <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">⏳ Menunggu</span>
                    @elseif($k->status === 'disetujui')
                        <span style="background:#d1fae5;color:#065f46;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">✅ Disetujui</span>
                        <div style="font-size:10px;color:#718096;margin-top:2px;">{{ $k->approver?->name }} • {{ $k->approved_at?->format('d/m H:i') }}</div>
                    @else
                        <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">❌ Ditolak</span>
                        <div style="font-size:10px;color:#718096;margin-top:2px;">{{ $k->approver?->name }} • {{ $k->approved_at?->format('d/m H:i') }}</div>
                    @endif
                </td>
                <td style="padding:10px 16px;">
                    @if($k->status === 'pending')
                    <div style="display:flex;gap:6px;">
                        {{-- Approve --}}
                        <form method="POST" action="{{ route('admin.koreksi-absensi.approve', $k) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success" style="font-size:11px;padding:4px 10px;"
                                    onclick="return confirm('Setujui koreksi ini? Absensi akan langsung diperbarui.')">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        {{-- Reject --}}
                        <button type="button" class="btn btn-danger" style="font-size:11px;padding:4px 10px;"
                                onclick="showReject({{ $k->id }})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @else
                        <span style="font-size:11px;color:#a0aec0;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="padding:40px;text-align:center;color:#a0aec0;">
                <i class="fas fa-inbox" style="font-size:32px;margin-bottom:8px;display:block;"></i>
                Tidak ada data koreksi absensi.
            </td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($koreksiList->hasPages())
    <div class="card-footer" style="padding:12px 16px;">
        {{ $koreksiList->links() }}
    </div>
    @endif
</div>
</form>

{{-- Modal Reject --}}
<div id="modalReject" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;padding:24px;width:440px;max-width:90vw;">
        <h6 style="margin:0 0 16px;font-weight:800;"><i class="fas fa-times-circle" style="color:#e53e3e;"></i> Tolak Koreksi Absensi</h6>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="form-group">
                <label>Alasan Penolakan <span style="color:#e53e3e;">*</span></label>
                <textarea name="catatan_approver" class="form-control" rows="3" required
                          placeholder="Jelaskan alasan penolakan..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeReject()">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
function showReject(id) {
    document.getElementById('rejectForm').action = '{{ url("admin/koreksi-absensi") }}/' + id + '/reject';
    document.getElementById('modalReject').style.display = 'flex';
}
function closeReject() {
    document.getElementById('modalReject').style.display = 'none';
}
function toggleAll(cb) {
    document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked);
}
</script>
@endsection
