{{-- Partial: Tab Surat Peringatan --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-exclamation-triangle" style="color:#ed8936;"></i> Surat Peringatan</h6>
    @if(auth()->user()->hasPermission('karyawan.edit'))
    <button class="btn btn-primary btn-sm" onclick="openModal('modalAddSP')">
        <i class="fas fa-plus"></i> Tambah SP
    </button>
    @endif
</div>

@if($karyawan->warningletters->isEmpty())
<div style="text-align:center; padding:30px; color:#94a3b8;">
    <i class="fas fa-check-circle" style="font-size:36px; opacity:0.5; color:#48bb78; display:block; margin-bottom:10px;"></i>
    <p>Tidak ada surat peringatan aktif</p>
</div>
@else
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Tipe SP</th>
                <th>Tanggal</th>
                <th>Berlaku Sampai</th>
                <th>Keterangan</th>
                <th>Status</th>
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <th style="text-align:center;">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($karyawan->warningletters as $sp)
            <tr>
                <td>
                    @php
                        $spColors = ['SP1'=>['#fef9c3','#854d0e'],'SP2'=>['#fed7e2','#9b1c1c'],'SP3'=>['#fecaca','#7f1d1d']];
                        [$bg, $color] = $spColors[$sp->type] ?? ['#f3f4f6','#374151'];
                    @endphp
                    <span style="background:{{ $bg }}; color:{{ $color }}; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">
                        {{ $sp->type }}
                    </span>
                </td>
                <td style="font-size:13px;">{{ $sp->date->format('d/m/Y') }}</td>
                <td style="font-size:13px;">{{ $sp->valid_until ? $sp->valid_until->format('d/m/Y') : '-' }}</td>
                <td style="max-width:200px; font-size:12px; color:#718096; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $sp->description ?? '-' }}
                </td>
                <td>
                    <span class="badge badge-{{ $sp->status === 'aktif' ? 'terlambat' : 'hadir' }}">
                        {{ ucfirst($sp->status) }}
                    </span>
                </td>
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <td style="text-align:center;">
                    <div style="display:flex; gap:5px; justify-content:center;">
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditSP({{ $sp->id }}, '{{ $sp->type }}', '{{ $sp->date->format('Y-m-d') }}', '{{ $sp->valid_until ? $sp->valid_until->format('Y-m-d') : '' }}', '{{ addslashes($sp->description ?? '') }}', '{{ $sp->status }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.warninglettter.destroy', $sp) }}"
                              onsubmit="return confirm('Hapus surat peringatan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(auth()->user()->hasPermission('karyawan.edit'))
{{-- Modal Tambah SP --}}
<div id="modalAddSP" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Surat Peringatan</h6>
            <button onclick="closeModal('modalAddSP')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.warninglettter.store', $karyawan) }}">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label>Tipe SP <span style="color:#e53e3e;">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="SP1">SP 1 (Surat Peringatan Pertama)</option>
                        <option value="SP2">SP 2 (Surat Peringatan Kedua)</option>
                        <option value="SP3">SP 3 (Surat Peringatan Ketiga)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status <span style="color:#e53e3e;">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal SP <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label>Berlaku Sampai</label>
                    <input type="date" name="valid_until" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan / Alasan</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Alasan pemberian surat peringatan..."></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddSP')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit SP --}}
<div id="modalEditSP" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Surat Peringatan</h6>
            <button onclick="closeModal('modalEditSP')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEditSP">
            @csrf @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label>Tipe SP <span style="color:#e53e3e;">*</span></label>
                    <select name="type" id="editSPType" class="form-select" required>
                        <option value="SP1">SP 1</option>
                        <option value="SP2">SP 2</option>
                        <option value="SP3">SP 3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status <span style="color:#e53e3e;">*</span></label>
                    <select name="status" id="editSPStatus" class="form-select" required>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal SP <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="date" id="editSPDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Berlaku Sampai</label>
                    <input type="date" name="valid_until" id="editSPValid" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="description" id="editSPDesc" class="form-control" rows="3"></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSP')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function openEditSP(id, type, date, valid, desc, status) {
    document.getElementById('editSPType').value   = type;
    document.getElementById('editSPDate').value   = date;
    document.getElementById('editSPValid').value  = valid;
    document.getElementById('editSPDesc').value   = desc;
    document.getElementById('editSPStatus').value = status;
    document.getElementById('formEditSP').action  = '{{ route("admin.warninglettter.update", ":id") }}'.replace(':id', id);
    openModal('modalEditSP');
}
['modalAddSP','modalEditSP'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target===this) closeModal(id); });
});
</script>
@endif
