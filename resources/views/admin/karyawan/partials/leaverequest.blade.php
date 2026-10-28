{{-- Partial: Tab Cuti & Izin --}}
@php
    $saldoCuti = \App\Models\CutiSaldo::getSaldo($karyawan->id);
@endphp
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;flex-wrap:wrap;gap:10px;">
    <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-calendar-alt" style="color:#4299e1;"></i> Pengajuan Cuti & Izin</h6>
    @if(auth()->user()->hasPermission('cuti.tambah'))
    <button class="btn btn-primary btn-sm" onclick="openModal('modalAddLeave')">
        <i class="fas fa-plus"></i> Tambah Pengajuan
    </button>
    @endif
</div>

{{-- Saldo Cuti --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;">
    <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:12px;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#276749;">{{ $saldoCuti->jatah }}</div>
        <div style="font-size:11px;color:#718096;">Jatah {{ now()->year }}</div>
    </div>
    <div style="background:#fff5f5;border:1px solid #feb2b2;border-radius:10px;padding:12px;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#c53030;">{{ $saldoCuti->terpakai }}</div>
        <div style="font-size:11px;color:#718096;">Terpakai</div>
    </div>
    <div style="background:#ebf8ff;border:1px solid #90cdf4;border-radius:10px;padding:12px;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#2b6cb0;">{{ $saldoCuti->sisa }}</div>
        <div style="font-size:11px;color:#718096;">Sisa</div>
    </div>
</div>

@php
    $statColors = [
        'pending'   => ['#fef9c3','#854d0e'],
        'disetujui' => ['#c6f6d5','#276749'],
        'ditolak'   => ['#fed7e2','#c53030'],
    ];
    $typeColors = [
        'cuti'  => ['#eff6ff','#3b82f6'],
        'izin'  => ['#fffbeb','#f59e0b'],
        'sakit' => ['#fef2f2','#ef4444'],
    ];
@endphp

@if($karyawan->leaverequests->isEmpty())
<div style="text-align:center; padding:30px; color:#94a3b8;">
    <i class="fas fa-calendar-check" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
    <p>Belum ada pengajuan cuti/izin</p>
</div>
@else
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Tipe</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Durasi</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Disetujui Oleh</th>
                @if(auth()->user()->hasPermission('cuti.approve') || auth()->user()->hasPermission('cuti.hapus'))
                <th style="text-align:center;">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($karyawan->leaverequests as $lr)
            @php
                [$statBg, $statColor] = $statColors[$lr->status] ?? ['#f3f4f6','#374151'];
                [$typeBg, $typeColor] = $typeColors[$lr->type]   ?? ['#f3f4f6','#374151'];
                $durasi = $lr->start_date->diffInDays($lr->end_date) + 1;
            @endphp
            <tr>
                <td>
                    <span style="background:{{ $typeBg }}; color:{{ $typeColor }}; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; text-transform:capitalize;">
                        {{ ucfirst($lr->type) }}
                    </span>
                </td>
                <td style="font-size:13px;">{{ $lr->start_date->format('d/m/Y') }}</td>
                <td style="font-size:13px;">{{ $lr->end_date->format('d/m/Y') }}</td>
                <td>
                    <span style="font-size:12px; font-weight:600; color:#4a5568;">{{ $durasi }} hari</span>
                </td>
                <td style="max-width:150px; font-size:12px; color:#718096; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $lr->keterangan ?? '-' }}
                </td>
                <td>
                    <span style="background:{{ $statBg }}; color:{{ $statColor }}; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; text-transform:capitalize;">
                        {{ ucfirst($lr->status) }}
                    </span>
                </td>
                <td style="font-size:12px; color:#718096;">
                    @if($lr->approver)
                        {{ $lr->approver->name }}<br>
                        <small>{{ $lr->approved_at?->format('d/m/Y') }}</small>
                    @else
                        -
                    @endif
                </td>
                @if(auth()->user()->hasPermission('cuti.approve') || auth()->user()->hasPermission('cuti.hapus'))
                <td style="text-align:center;">
                    <div style="display:flex; gap:4px; justify-content:center; align-items:center;">
                        @if($lr->status === 'pending')
                        @if(auth()->user()->hasPermission('cuti.approve'))
                        <form method="POST" action="{{ route('admin.leaverequest.approve', $lr) }}?from_karyawan=1">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" title="Setujui"
                                    onclick="return confirm('Setujui pengajuan ini?')">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.leaverequest.reject', $lr) }}?from_karyawan=1">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" title="Tolak"
                                    onclick="return confirm('Tolak pengajuan ini?')">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        @endif
                        @if(auth()->user()->hasPermission('cuti.edit'))
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditLeave({{ $lr->id }}, '{{ $lr->start_date->format('Y-m-d') }}', '{{ $lr->end_date->format('Y-m-d') }}', '{{ $lr->type }}', '{{ addslashes($lr->keterangan ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        @endif
                        @endif
                        @if(auth()->user()->hasPermission('cuti.hapus'))
                        <form method="POST" action="{{ route('admin.leaverequest.destroy', $lr) }}"
                              onsubmit="return confirm('Hapus pengajuan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(auth()->user()->hasPermission('cuti.tambah') || auth()->user()->hasPermission('cuti.edit'))
{{-- Modal Tambah --}}
<div id="modalAddLeave" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Pengajuan Cuti/Izin</h6>
            <button onclick="closeModal('modalAddLeave')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.leaverequest.store', $karyawan) }}">
            @csrf
            <div class="form-group">
                <label>Tipe Pengajuan <span style="color:#e53e3e;">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="">-- Pilih Tipe --</option>
                    <option value="cuti">Cuti</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                </select>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Tanggal Mulai <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Alasan/keterangan cuti atau izin..."></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddLeave')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEditLeave" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:520px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Pengajuan</h6>
            <button onclick="closeModal('modalEditLeave')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEditLeave">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Tipe Pengajuan <span style="color:#e53e3e;">*</span></label>
                <select name="type" id="editLeaveType" class="form-select" required>
                    <option value="cuti">Cuti</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                </select>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Tanggal Mulai <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="start_date" id="editLeaveStart" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="end_date" id="editLeaveEnd" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" id="editLeaveKet" class="form-control" rows="3"></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditLeave')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function openEditLeave(id, start, end, type, ket) {
    document.getElementById('editLeaveStart').value = start;
    document.getElementById('editLeaveEnd').value   = end;
    document.getElementById('editLeaveType').value  = type;
    document.getElementById('editLeaveKet').value   = ket;
    document.getElementById('formEditLeave').action = '{{ route("admin.leaverequest.update", ":id") }}'.replace(':id', id);
    openModal('modalEditLeave');
}
['modalAddLeave','modalEditLeave'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target===this) closeModal(id); });
});
</script>
@endif
