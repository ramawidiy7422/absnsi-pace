{{-- Partial: Tab Pendidikan --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-graduation-cap" style="color:#4299e1;"></i> Riwayat Pendidikan</h6>
    @if(auth()->user()->hasPermission('karyawan.edit'))
    <button class="btn btn-primary btn-sm" onclick="openModal('modalAddEdu')">
        <i class="fas fa-plus"></i> Tambah
    </button>
    @endif
</div>

@if($karyawan->educations->isEmpty())
<div style="text-align:center; padding:30px; color:#94a3b8;">
    <i class="fas fa-graduation-cap" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
    <p>Belum ada riwayat pendidikan</p>
</div>
@else
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Jenjang</th>
                <th>Jurusan</th>
                <th>Institusi</th>
                <th>Tahun Lulus</th>
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <th style="text-align:center;">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($karyawan->educations as $edu)
            <tr>
                <td>
                    <span style="background:#eff6ff; color:#3b82f6; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                        {{ $edu->level }}
                    </span>
                </td>
                <td style="font-size:13px;">{{ $edu->major ?? '-' }}</td>
                <td style="font-size:13px; font-weight:600;">{{ $edu->institution }}</td>
                <td style="font-size:13px;">{{ $edu->year ?? '-' }}</td>
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <td style="text-align:center;">
                    <div style="display:flex; gap:5px; justify-content:center;">
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditEdu({{ $edu->id }}, '{{ addslashes($edu->level) }}', '{{ addslashes($edu->major ?? '') }}', '{{ addslashes($edu->institution) }}', '{{ $edu->year ?? '' }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.education.destroy', $edu) }}"
                              onsubmit="return confirm('Hapus data pendidikan ini?')">
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
{{-- Modal Tambah Pendidikan --}}
<div id="modalAddEdu" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Pendidikan</h6>
            <button onclick="closeModal('modalAddEdu')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.education.store', $karyawan) }}">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label>Jenjang <span style="color:#e53e3e;">*</span></label>
                    <select name="level" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA/SMK">SMA/SMK</option>
                        <option value="D1">D1</option>
                        <option value="D2">D2</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tahun Lulus</label>
                    <input type="number" name="year" class="form-control" min="1950" max="{{ date('Y') }}" placeholder="{{ date('Y') }}">
                </div>
            </div>
            <div class="form-group">
                <label>Jurusan</label>
                <input type="text" name="major" class="form-control" placeholder="Contoh: Teknik Informatika">
            </div>
            <div class="form-group">
                <label>Institusi / Sekolah <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="institution" class="form-control" required placeholder="Nama sekolah/universitas">
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddEdu')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Pendidikan --}}
<div id="modalEditEdu" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Pendidikan</h6>
            <button onclick="closeModal('modalEditEdu')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEditEdu">
            @csrf @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label>Jenjang <span style="color:#e53e3e;">*</span></label>
                    <select name="level" id="editEduLevel" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA/SMK">SMA/SMK</option>
                        <option value="D1">D1</option>
                        <option value="D2">D2</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tahun Lulus</label>
                    <input type="number" name="year" id="editEduYear" class="form-control" min="1950" max="{{ date('Y') }}">
                </div>
            </div>
            <div class="form-group">
                <label>Jurusan</label>
                <input type="text" name="major" id="editEduMajor" class="form-control">
            </div>
            <div class="form-group">
                <label>Institusi / Sekolah <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="institution" id="editEduInstitution" class="form-control" required>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditEdu')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function openEditEdu(id, level, major, institution, year) {
    document.getElementById('editEduLevel').value       = level;
    document.getElementById('editEduMajor').value       = major;
    document.getElementById('editEduInstitution').value = institution;
    document.getElementById('editEduYear').value        = year;
    document.getElementById('formEditEdu').action       = '{{ rtrim(route("admin.education.update", ":id"), "") }}'.replace(':id', id);
    openModal('modalEditEdu');
}
['modalAddEdu','modalEditEdu'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target===this) closeModal(id); });
});
</script>
@endif
