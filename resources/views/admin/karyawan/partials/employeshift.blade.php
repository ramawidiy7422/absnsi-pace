{{-- Partial: Tab Jadwal Shift --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-calendar-week" style="color:#4299e1;"></i> Jadwal Shift</h6>
    @if(auth()->user()->hasPermission('karyawan.edit'))
    <button class="btn btn-primary btn-sm" onclick="openModal('modalAddShift')">
        <i class="fas fa-plus"></i> Tambah
    </button>
    @endif
</div>

@if($karyawan->employeshifts->isEmpty())
<div style="text-align:center; padding:30px; color:#94a3b8;">
    <i class="fas fa-calendar-week" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
    <p>Belum ada jadwal shift</p>
</div>
@else
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Shift</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Keterangan</th>
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <th style="text-align:center;">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($karyawan->employeshifts as $es)
            <tr>
                <td style="font-size:13px; font-weight:600;">{{ $es->date->format('d/m/Y') }}</td>
                <td>
                    <span style="background:#eff6ff; color:#3b82f6; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                        {{ $es->shift?->nama_shift ?? '-' }}
                    </span>
                </td>
                <td style="font-size:13px;">{{ $es->shift?->jam_masuk ?? '-' }}</td>
                <td style="font-size:13px;">{{ $es->shift?->jam_keluar ?? '-' }}</td>
                <td style="font-size:12px; color:#718096; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $es->keterangan ?? '-' }}
                </td>
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <td style="text-align:center;">
                    <div style="display:flex; gap:5px; justify-content:center;">
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditShift({{ $es->id }}, '{{ $es->date->format('Y-m-d') }}', {{ $es->shift_id }}, '{{ addslashes($es->keterangan ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.employeshift.destroy', $es) }}"
                              onsubmit="return confirm('Hapus jadwal shift ini?')">
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
{{-- Modal Tambah Jadwal Shift --}}
<div id="modalAddShift" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Jadwal Shift</h6>
            <button onclick="closeModal('modalAddShift')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.employeshift.store', $karyawan) }}">
            @csrf
            <div class="form-group">
                <label>Tanggal <span style="color:#e53e3e;">*</span></label>
                <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Shift <span style="color:#e53e3e;">*</span></label>
                <select name="shift_id" class="form-select" required>
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->nama_shift }} ({{ $shift->jam_masuk }} - {{ $shift->jam_keluar }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="keterangan" class="form-control" placeholder="Keterangan tambahan (opsional)" maxlength="255">
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddShift')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Jadwal Shift --}}
<div id="modalEditShift" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Jadwal Shift</h6>
            <button onclick="closeModal('modalEditShift')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEditShift">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Tanggal <span style="color:#e53e3e;">*</span></label>
                <input type="date" name="date" id="editShiftDate" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Shift <span style="color:#e53e3e;">*</span></label>
                <select name="shift_id" id="editShiftId" class="form-select" required>
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->nama_shift }} ({{ $shift->jam_masuk }} - {{ $shift->jam_keluar }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="keterangan" id="editShiftKet" class="form-control" maxlength="255">
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditShift')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function openEditShift(id, date, shiftId, ket) {
    document.getElementById('editShiftDate').value = date;
    document.getElementById('editShiftId').value   = shiftId;
    document.getElementById('editShiftKet').value  = ket;
    document.getElementById('formEditShift').action = '{{ route("admin.employeshift.update", ":id") }}'.replace(':id', id);
    openModal('modalEditShift');
}
['modalAddShift','modalEditShift'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target===this) closeModal(id); });
});
</script>
@endif
