{{-- Partial: Tab Riwayat Kerja Internal --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <h6 style="font-weight:700; color:#2d3748; margin:0;"><i class="fas fa-history" style="color:#4299e1;"></i> Riwayat Kerja Internal</h6>
        <small style="color:#a0aec0;">Riwayat jabatan, mutasi, dan perubahan status di perusahaan ini</small>
    </div>
    @if(auth()->user()->hasPermission('karyawan.edit'))
    <button class="btn btn-primary btn-sm" onclick="openModal('modalAddWork')">
        <i class="fas fa-plus"></i> Tambah
    </button>
    @endif
</div>

@if($karyawan->workhistories->isEmpty())
<div style="text-align:center; padding:40px; color:#94a3b8;">
    <i class="fas fa-history" style="font-size:40px; opacity:0.2; display:block; margin-bottom:12px;"></i>
    <p style="margin:0;">Belum ada riwayat kerja</p>
</div>
@else
<div style="position:relative; padding-left:24px;">
    {{-- garis timeline --}}
    <div style="position:absolute; left:8px; top:0; bottom:0; width:2px; background:#e2e8f0;"></div>

    @foreach($karyawan->workhistories as $wh)
    @php
        $colors = [
            'Mutasi'               => ['bg'=>'#ebf8ff','color'=>'#2b6cb0','icon'=>'fa-exchange-alt'],
            'Promosi'              => ['bg'=>'#f0fff4','color'=>'#276749','icon'=>'fa-arrow-up'],
            'Demosi'               => ['bg'=>'#fff5f5','color'=>'#c53030','icon'=>'fa-arrow-down'],
            'Penugasan'            => ['bg'=>'#fffbeb','color'=>'#92400e','icon'=>'fa-tasks'],
            'Rotasi'               => ['bg'=>'#faf5ff','color'=>'#553c9a','icon'=>'fa-sync-alt'],
            'Kontrak'              => ['bg'=>'#bee3f8','color'=>'#2c5282','icon'=>'fa-file-contract'],
            'Pengangkatan Tetap'   => ['bg'=>'#c6f6d5','color'=>'#276749','icon'=>'fa-award'],
            'Perpanjangan Kontrak' => ['bg'=>'#e9d8fd','color'=>'#553c9a','icon'=>'fa-redo'],
            'Lainnya'              => ['bg'=>'#f7fafc','color'=>'#718096','icon'=>'fa-info-circle'],
        ];
        $c = $colors[$wh->type] ?? $colors['Lainnya'];
    @endphp
    <div style="position:relative; margin-bottom:16px;">
        {{-- titik timeline --}}
        <div style="position:absolute; left:-20px; top:16px; width:14px; height:14px; border-radius:50%; background:{{ $c['color'] }}; border:2px solid #fff; box-shadow:0 0 0 2px {{ $c['color'] }};"></div>

        <div style="border:1px solid #e2e8f0; border-radius:10px; padding:14px 16px; background:#fff;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
                <div style="flex:1;">
                    <span style="background:{{ $c['bg'] }}; color:{{ $c['color'] }}; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                        <i class="fas {{ $c['icon'] }}"></i> {{ $wh->type }}
                    </span>

                    <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:10px;">
                        @if($wh->jabatan)
                        <div>
                            <div style="font-size:10px; color:#a0aec0; font-weight:600; text-transform:uppercase;">Jabatan</div>
                            <div style="font-size:13px; font-weight:700; color:#2d3748;">{{ $wh->jabatan }}</div>
                        </div>
                        @endif
                        @if($wh->departemen)
                        <div>
                            <div style="font-size:10px; color:#a0aec0; font-weight:600; text-transform:uppercase;">Departemen</div>
                            <div style="font-size:13px; font-weight:600; color:#4a5568;">{{ $wh->departemen }}</div>
                        </div>
                        @endif
                        @if($wh->status_karyawan)
                        <div>
                            <div style="font-size:10px; color:#a0aec0; font-weight:600; text-transform:uppercase;">Status</div>
                            <span class="badge badge-{{ $wh->status_karyawan }}">{{ ucfirst($wh->status_karyawan) }}</span>
                        </div>
                        @endif
                    </div>

                    @if($wh->description)
                    <p style="font-size:12px; color:#718096; margin:8px 0 0;">{{ $wh->description }}</p>
                    @endif
                </div>

                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-size:11px; color:#a0aec0; margin-bottom:4px;">
                        <i class="fas fa-calendar-alt"></i>
                        {{ $wh->start_date->format('d M Y') }}
                        @if($wh->end_date)
                            — {{ $wh->end_date->format('d M Y') }}
                        @else
                            — <span style="color:#38a169; font-weight:600;">Sekarang</span>
                        @endif
                    </div>
                    @if(auth()->user()->hasPermission('karyawan.edit'))
                    <div style="display:flex; gap:5px; justify-content:flex-end; margin-top:6px;">
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditWork({{ $wh->id }}, '{{ $wh->start_date->format('Y-m-d') }}', '{{ $wh->end_date ? $wh->end_date->format('Y-m-d') : '' }}', '{{ addslashes($wh->type) }}', '{{ addslashes($wh->jabatan ?? '') }}', '{{ addslashes($wh->departemen ?? '') }}', '{{ addslashes($wh->status_karyawan ?? '') }}', '{{ addslashes($wh->description ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.workhistory.destroy', $wh) }}"
                              onsubmit="return confirm('Hapus riwayat kerja ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(auth()->user()->hasPermission('karyawan.edit'))
{{-- Modal Tambah --}}
<div id="modalAddWork" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:560px; padding:24px; margin:20px; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:700; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Riwayat Kerja</h6>
            <button onclick="closeModal('modalAddWork')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.workhistory.store', $karyawan) }}">
            @csrf
            <div class="form-group">
                <label>Jenis Perubahan <span style="color:#e53e3e;">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="Kontrak">Kontrak (Awal masuk)</option>
                    <option value="Pengangkatan Tetap">Pengangkatan Tetap</option>
                    <option value="Perpanjangan Kontrak">Perpanjangan Kontrak</option>
                    <option value="Promosi">Promosi</option>
                    <option value="Mutasi">Mutasi</option>
                    <option value="Rotasi">Rotasi</option>
                    <option value="Demosi">Demosi</option>
                    <option value="Penugasan">Penugasan</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" class="form-control" value="{{ $karyawan->jabatan }}" placeholder="Jabatan saat itu">
                </div>
                <div class="form-group">
                    <label>Departemen</label>
                    <input type="text" name="departemen" class="form-control" value="{{ $karyawan->departemen }}" placeholder="Departemen saat itu">
                </div>
                <div class="form-group">
                    <label>Status Karyawan</label>
                    <select name="status_karyawan" class="form-select">
                        <option value="">-- Pilih --</option>
                        <option value="kontrak" {{ $karyawan->status=='kontrak' ? 'selected' : '' }}>Karyawan Kontrak</option>
                        <option value="tetap" {{ $karyawan->status=='tetap' ? 'selected' : '' }}>Karyawan Tetap</option>
                        <option value="magang" {{ $karyawan->status=='magang' ? 'selected' : '' }}>Magang</option>
                        <option value="training" {{ $karyawan->status=='training' ? 'selected' : '' }}>Karyawan Training</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Mulai <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control">
                    <small style="color:#a0aec0;">Kosongkan jika masih berlaku</small>
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Keterangan tambahan..."></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddWork')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEditWork" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:560px; padding:24px; margin:20px; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:700; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Riwayat Kerja</h6>
            <button onclick="closeModal('modalEditWork')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEditWork">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Jenis Perubahan <span style="color:#e53e3e;">*</span></label>
                <select name="type" id="editWorkType" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="Kontrak">Kontrak (Awal masuk)</option>
                    <option value="Pengangkatan Tetap">Pengangkatan Tetap</option>
                    <option value="Perpanjangan Kontrak">Perpanjangan Kontrak</option>
                    <option value="Promosi">Promosi</option>
                    <option value="Mutasi">Mutasi</option>
                    <option value="Rotasi">Rotasi</option>
                    <option value="Demosi">Demosi</option>
                    <option value="Penugasan">Penugasan</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" id="editWorkJabatan" class="form-control">
                </div>
                <div class="form-group">
                    <label>Departemen</label>
                    <input type="text" name="departemen" id="editWorkDepartemen" class="form-control">
                </div>
                <div class="form-group">
                    <label>Status Karyawan</label>
                    <select name="status_karyawan" id="editWorkStatus" class="form-select">
                        <option value="">-- Pilih --</option>
                        <option value="kontrak">Karyawan Kontrak</option>
                        <option value="tetap">Karyawan Tetap</option>
                        <option value="magang">Magang</option>
                        <option value="training">Karyawan Training</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Mulai <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="start_date" id="editWorkStart" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" id="editWorkEnd" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="description" id="editWorkDesc" class="form-control" rows="2"></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditWork')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function openEditWork(id, start, end, type, jabatan, departemen, status, desc) {
    document.getElementById('editWorkStart').value      = start;
    document.getElementById('editWorkEnd').value        = end;
    document.getElementById('editWorkType').value       = type;
    document.getElementById('editWorkJabatan').value    = jabatan;
    document.getElementById('editWorkDepartemen').value = departemen;
    document.getElementById('editWorkStatus').value     = status;
    document.getElementById('editWorkDesc').value       = desc;
    document.getElementById('formEditWork').action      = '{{ route("admin.workhistory.update", ":id") }}'.replace(':id', id);
    openModal('modalEditWork');
}
['modalAddWork','modalEditWork'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target===this) closeModal(id); });
});
</script>
@endif
