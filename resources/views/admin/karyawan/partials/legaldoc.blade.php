{{-- Partial: Tab Dokumen Legal --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-file-alt" style="color:#4299e1;"></i> Dokumen Legal</h6>
    @if(auth()->user()->hasPermission('karyawan.edit'))
    <button class="btn btn-primary btn-sm" onclick="openModal('modalLegaldoc')">
        <i class="fas fa-{{ $karyawan->legaldoc ? 'edit' : 'plus' }}"></i>
        {{ $karyawan->legaldoc ? 'Edit Dokumen' : 'Tambah Dokumen' }}
    </button>
    @endif
</div>

@if($karyawan->legaldoc)
<div class="grid-2">
    @php $doc = $karyawan->legaldoc; @endphp
    @foreach([
        ['label'=>'NIK / KTP',      'val'=>$doc->nik,            'icon'=>'fa-id-card'],
        ['label'=>'No. KK',         'val'=>$doc->kk,             'icon'=>'fa-home'],
        ['label'=>'NPWP',           'val'=>$doc->npwp,           'icon'=>'fa-file-invoice-dollar'],
        ['label'=>'BPJS TK',        'val'=>$doc->bpjs_tk,        'icon'=>'fa-shield-alt'],
        ['label'=>'BPJS Kesehatan', 'val'=>$doc->bpjs_kesehatan, 'icon'=>'fa-heartbeat'],
    ] as $item)
    <div style="background:#f8fafc; border-radius:8px; padding:14px 16px; border:1px solid #e2e8f0;">
        <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">
            <i class="fas {{ $item['icon'] }}" style="margin-right:4px;"></i> {{ $item['label'] }}
        </div>
        @if($item['val'])
        <div style="font-size:14px; font-weight:600; color:#1e293b; font-family:monospace; letter-spacing:0.3px;">
            {{ $item['val'] }}
        </div>
        @else
        <div style="font-size:13px; color:#cbd5e0; font-weight:400; font-style:italic;">
            Belum diisi
        </div>
        @endif
    </div>
    @endforeach
</div>

@if(auth()->user()->hasPermission('karyawan.edit'))
<div style="margin-top:12px;">
    <form method="POST" action="{{ route('admin.legaldoc.destroy', $doc) }}"
          onsubmit="return confirm('Hapus seluruh data dokumen legal?')" style="display:inline;">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Hapus Dokumen
        </button>
    </form>
</div>
@endif

@else
<div style="text-align:center; padding:30px; color:#94a3b8;">
    <i class="fas fa-file-alt" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
    <p>Belum ada dokumen legal yang tersimpan</p>
</div>
@endif

@if(auth()->user()->hasPermission('karyawan.edit'))
{{-- Modal Tambah/Edit Legaldoc --}}
<div id="modalLegaldoc" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:540px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;">
                <i class="fas fa-file-alt" style="color:#4299e1;"></i>
                {{ $karyawan->legaldoc ? 'Edit' : 'Tambah' }} Dokumen Legal
            </h6>
            <button onclick="closeModal('modalLegaldoc')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        @if($karyawan->legaldoc)
        <form method="POST" action="{{ route('admin.legaldoc.update', $karyawan->legaldoc) }}">
            @csrf @method('PUT')
        @else
        <form method="POST" action="{{ route('admin.legaldoc.store', $karyawan) }}">
            @csrf
        @endif
            <div class="grid-2">
                <div class="form-group">
                    <label>NIK / KTP</label>
                    <input type="text" name="nik" class="form-control" maxlength="20"
                           value="{{ $karyawan->legaldoc->nik ?? '' }}" placeholder="16 digit NIK">
                </div>
                <div class="form-group">
                    <label>No. KK</label>
                    <input type="text" name="kk" class="form-control" maxlength="20"
                           value="{{ $karyawan->legaldoc->kk ?? '' }}" placeholder="16 digit KK">
                </div>
                <div class="form-group">
                    <label>NPWP</label>
                    <input type="text" name="npwp" class="form-control" maxlength="30"
                           value="{{ $karyawan->legaldoc->npwp ?? '' }}" placeholder="XX.XXX.XXX.X-XXX.XXX">
                </div>
                <div class="form-group">
                    <label>BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_tk" class="form-control" maxlength="30"
                           value="{{ $karyawan->legaldoc->bpjs_tk ?? '' }}" placeholder="No. BPJS TK">
                </div>
                <div class="form-group">
                    <label>BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kesehatan" class="form-control" maxlength="30"
                           value="{{ $karyawan->legaldoc->bpjs_kesehatan ?? '' }}" placeholder="No. BPJS Kesehatan">
                </div>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalLegaldoc')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
const _ldModal = document.getElementById('modalLegaldoc');
if (_ldModal) _ldModal.addEventListener('click', function(e) { if (e.target===this) closeModal('modalLegaldoc'); });
</script>
@endif
