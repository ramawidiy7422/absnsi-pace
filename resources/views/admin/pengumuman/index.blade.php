@extends('layouts.admin')
@section('title','Pengumuman & Memo')
@section('page-title','Pengumuman & Memo Internal')

@section('content')


{{-- Header --}}
<div style="background:linear-gradient(135deg,#744210,#c05621);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-bullhorn"></i> Pengumuman & Memo Internal</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Kelola pengumuman dan memo untuk seluruh karyawan</p>
    </div>
    <button onclick="openModal('modal-buat')" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
        <i class="fas fa-plus"></i> Buat Baru
    </button>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="judul pengumuman..." class="form-control" style="padding:8px 12px;font-size:13px;min-width:200px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jenis</label>
                <select name="jenis" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="pengumuman" {{ request('jenis')=='pengumuman'?'selected':'' }}>Pengumuman</option>
                    <option value="memo" {{ request('jenis')=='memo'?'selected':'' }}>Memo</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Prioritas</label>
                <select name="prioritas" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="mendesak" {{ request('prioritas')=='mendesak'?'selected':'' }}>Mendesak</option>
                    <option value="penting" {{ request('prioritas')=='penting'?'selected':'' }}>Penting</option>
                    <option value="normal" {{ request('prioritas')=='normal'?'selected':'' }}>Normal</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Status</label>
                <select name="aktif" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="1" {{ request('aktif')==='1'?'selected':'' }}>Aktif</option>
                    <option value="0" {{ request('aktif')==='0'?'selected':'' }}>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
        </form>
    </div>
</div>

{{-- List --}}
<div style="display:flex;flex-direction:column;gap:12px;">
    @forelse($list as $p)
    @php $bj = $p->badge_jenis; $bp = $p->badge_prioritas; @endphp
    <div style="background:white;border-radius:14px;padding:20px 22px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:5px solid {{ $bp['text'] }};position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                    <span style="background:{{ $bj['bg'] }};color:{{ $bj['text'] }};padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;">
                        <i class="{{ $bj['icon'] }}"></i> {{ $bj['label'] }}
                    </span>
                    <span style="background:{{ $bp['bg'] }};color:{{ $bp['text'] }};padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;">
                        {{ $bp['label'] }}
                    </span>
                    @if(!$p->aktif)
                    <span style="background:#f7fafc;color:#a0aec0;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;">Nonaktif</span>
                    @endif
                    @if($p->tampil_popup)
                    <span style="background:#fff3cd;color:#856404;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;">
                        <i class="fas fa-bell"></i> Pop-up
                    </span>
                    @endif
                    @if($p->target_departemen)
                    <span style="background:#faf5ff;color:#553c9a;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:600;">
                        <i class="fas fa-building"></i> {{ $p->target_departemen }}
                    </span>
                    @else
                    <span style="background:#f0fff4;color:#276749;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:600;">
                        <i class="fas fa-globe"></i> Semua Karyawan
                    </span>
                    @endif
                </div>
                <h5 style="margin:0 0 6px;font-size:15px;font-weight:800;color:#2d3748;">{{ $p->judul }}</h5>
                <div style="font-size:13px;color:#4a5568;line-height:1.6;max-height:60px;overflow:hidden;">{{ Str::limit(strip_tags($p->isi), 200) }}</div>
                <div style="margin-top:10px;font-size:11px;color:#a0aec0;display:flex;gap:16px;flex-wrap:wrap;">
                    <span><i class="fas fa-user"></i> {{ $p->penulis?->name ?? '-' }}</span>
                    <span><i class="fas fa-calendar"></i> {{ $p->tanggal_mulai->format('d/m/Y') }}{{ $p->tanggal_selesai ? ' s/d '.$p->tanggal_selesai->format('d/m/Y') : '' }}</span>
                    <span><i class="fas fa-clock"></i> {{ $p->created_at->diffForHumans() }}</span>
                    @if($p->lampiran)
                    <a href="{{ asset('storage/'.$p->lampiran) }}" target="_blank" style="color:#2b6cb0;text-decoration:none;">
                        <i class="fas fa-paperclip"></i> Lampiran
                    </a>
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                <button onclick="openEdit({{ $p->id }})" class="btn btn-sm btn-outline-secondary" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                @if(auth()->user()->hasPermission('pengumuman.kelola'))
                <form method="POST" action="{{ route('admin.pengumuman.toggle-aktif', $p) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm"
                        style="{{ $p->aktif ? 'background:#fef9c3;color:#854d0e;border:1px solid #fbd38d;' : 'background:#c6f6d5;color:#276749;border:1px solid #9ae6b4;' }}"
                        title="{{ $p->aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                        <i class="fas {{ $p->aktif ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.pengumuman.destroy', $p) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>

        {{-- Isi lengkap (hidden, untuk edit) --}}
        <div id="isi-{{ $p->id }}" style="display:none;">{{ $p->isi }}</div>
        <div id="data-{{ $p->id }}" style="display:none;"
            data-judul="{{ $p->judul }}"
            data-jenis="{{ $p->jenis }}"
            data-prioritas="{{ $p->prioritas }}"
            data-target="{{ $p->target_departemen }}"
            data-mulai="{{ $p->tanggal_mulai->format('Y-m-d') }}"
            data-selesai="{{ $p->tanggal_selesai?->format('Y-m-d') }}"
            data-aktif="{{ $p->aktif ? '1' : '0' }}"
            data-popup="{{ $p->tampil_popup ? '1' : '0' }}"
            data-action="{{ route('admin.pengumuman.update', $p) }}">
        </div>
    </div>
    @empty
    <div style="background:white;border-radius:14px;padding:60px;text-align:center;color:#a0aec0;">
        <i class="fas fa-bullhorn" style="font-size:48px;margin-bottom:12px;display:block;"></i>
        <div style="font-size:15px;font-weight:600;">Belum ada pengumuman</div>
    </div>
    @endforelse
</div>

@if($list->hasPages())
<div style="margin-top:16px;">{{ $list->appends(request()->query())->links() }}</div>
@endif

{{-- Modal Buat --}}
<div id="modal-buat" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:16px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <h5 style="margin:0;font-weight:800;color:#744210;"><i class="fas fa-bullhorn"></i> Buat Pengumuman / Memo</h5>
            <button onclick="closeModal('modal-buat')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.pengumuman.store') }}" enctype="multipart/form-data" style="padding:20px 24px;">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:span 2;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Judul <span style="color:red">*</span></label>
                    <input type="text" name="judul" required class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jenis</label>
                    <select name="jenis" class="form-select" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                        <option value="pengumuman">Pengumuman</option>
                        <option value="memo">Memo</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Prioritas</label>
                    <select name="prioritas" class="form-select" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                        <option value="normal">Normal</option>
                        <option value="penting">Penting</option>
                        <option value="mendesak">Mendesak</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Target Departemen</label>
                    <select name="target_departemen" class="form-select" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                        <option value="">Semua Karyawan</option>
                        @foreach($departemenList as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal Mulai <span style="color:red">*</span></label>
                    <input type="date" name="tanggal_mulai" required value="{{ date('Y-m-d') }}" class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div style="grid-column:span 2;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Isi <span style="color:red">*</span></label>
                    <textarea name="isi" required rows="6" class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;resize:vertical;"></textarea>
                </div>
                <div style="grid-column:span 2;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Lampiran (PDF/Gambar, maks 5MB)</label>
                    <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png" class="form-control" style="padding:8px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div style="grid-column:span 2;display:flex;align-items:center;gap:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;">
                    <input type="checkbox" name="tampil_popup" id="b-popup" value="1" style="width:16px;height:16px;">
                    <label for="b-popup" style="font-size:13px;font-weight:600;color:#92400e;margin:0;">
                        <i class="fas fa-bell"></i> Tampilkan sebagai Pop-up di Portal Karyawan
                    </label>
                    <span style="font-size:11px;color:#b45309;margin-left:4px;">(muncul otomatis saat karyawan buka portal, selama rentang tanggal berlaku)</span>
                </div>
            </div>
            <div style="margin-top:18px;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeModal('modal-buat')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:16px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <h5 style="margin:0;font-weight:800;color:#2d3748;"><i class="fas fa-edit"></i> Edit Pengumuman</h5>
            <button onclick="closeModal('modal-edit')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form id="form-edit" method="POST" enctype="multipart/form-data" style="padding:20px 24px;">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:span 2;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Judul <span style="color:red">*</span></label>
                    <input type="text" name="judul" id="e-judul" required class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jenis</label>
                    <select name="jenis" id="e-jenis" class="form-select" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                        <option value="pengumuman">Pengumuman</option>
                        <option value="memo">Memo</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Prioritas</label>
                    <select name="prioritas" id="e-prioritas" class="form-select" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                        <option value="normal">Normal</option>
                        <option value="penting">Penting</option>
                        <option value="mendesak">Mendesak</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Target Departemen</label>
                    <select name="target_departemen" id="e-target" class="form-select" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                        <option value="">Semua Karyawan</option>
                        @foreach($departemenList as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="e-mulai" class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="e-selesai" class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div style="grid-column:span 2;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Isi <span style="color:red">*</span></label>
                    <textarea name="isi" id="e-isi" required rows="6" class="form-control" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;resize:vertical;"></textarea>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Lampiran Baru</label>
                    <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png" class="form-control" style="padding:8px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                </div>
                <div style="display:flex;align-items:center;gap:8px;padding-top:24px;">
                    <input type="checkbox" name="aktif" id="e-aktif" value="1" style="width:16px;height:16px;">
                    <label for="e-aktif" style="font-size:13px;font-weight:600;color:#4a5568;margin:0;">Aktif</label>
                </div>
                <div style="grid-column:span 2;display:flex;align-items:center;gap:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;">
                    <input type="checkbox" name="tampil_popup" id="e-popup" value="1" style="width:16px;height:16px;">
                    <label for="e-popup" style="font-size:13px;font-weight:600;color:#92400e;margin:0;">
                        <i class="fas fa-bell"></i> Tampilkan sebagai Pop-up di Portal Karyawan
                    </label>
                </div>
            </div>
            <div style="margin-top:18px;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeModal('modal-edit')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function openEdit(id) {
    const d = document.getElementById('data-' + id).dataset;
    const isi = document.getElementById('isi-' + id).textContent;
    document.getElementById('form-edit').action = d.action;
    document.getElementById('e-judul').value   = d.judul;
    document.getElementById('e-jenis').value   = d.jenis;
    document.getElementById('e-prioritas').value = d.prioritas;
    document.getElementById('e-target').value  = d.target;
    document.getElementById('e-mulai').value   = d.mulai;
    document.getElementById('e-selesai').value = d.selesai;
    document.getElementById('e-isi').value     = isi;
    document.getElementById('e-aktif').checked = d.aktif === '1';
    document.getElementById('e-popup').checked = d.popup === '1';
    openModal('modal-edit');
}
// Close on backdrop click
['modal-buat','modal-edit'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) closeModal(id);
    });
});
</script>
@endpush

@endsection
