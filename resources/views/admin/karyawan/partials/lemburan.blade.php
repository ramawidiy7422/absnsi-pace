{{-- Partial: Tab Lemburan --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h6 style="font-weight:600;color:#2d3748;margin:0;">
        <i class="fas fa-business-time" style="color:#c05621;"></i> Riwayat Lembur
    </h6>
    @if(auth()->user()->hasPermission('lembur.tambah'))
    <button class="btn btn-primary btn-sm" onclick="openModal('modalAddLembur')">
        <i class="fas fa-plus"></i> Tambah Lembur
    </button>
    @endif
</div>

@php
    $lemburanList = $karyawan->lemburan ?? collect();
    $statColors = [
        'pending'  => ['#fef9c3','#854d0e'],
        'approved' => ['#c6f6d5','#276749'],
        'rejected' => ['#fed7e2','#c53030'],
    ];
    $statLabels = ['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
@endphp

@if($lemburanList->isEmpty())
<div style="text-align:center;padding:30px;color:#94a3b8;">
    <i class="fas fa-business-time" style="font-size:36px;opacity:0.3;display:block;margin-bottom:10px;"></i>
    <p>Belum ada data lembur</p>
</div>
@else
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Total Jam</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Disetujui Oleh</th>
                @if(auth()->user()->hasPermission('lembur.approve') || auth()->user()->hasPermission('lembur.edit') || auth()->user()->hasPermission('lembur.hapus'))
                <th style="text-align:center;">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($lemburanList->sortByDesc('tanggal') as $lb)
            @php [$statBg, $statColor] = $statColors[$lb->status] ?? ['#f3f4f6','#374151']; @endphp
            <tr>
                <td style="font-size:13px;font-weight:600;">
                    {{ $lb->tanggal->format('d/m/Y') }}<br>
                    <span style="font-size:11px;color:#a0aec0;">{{ $lb->tanggal->translatedFormat('l') }}</span>
                </td>
                <td style="font-weight:600;">{{ substr($lb->jam_mulai,0,5) }}</td>
                <td style="font-weight:600;">{{ substr($lb->jam_selesai,0,5) }}</td>
                <td>
                    <span style="background:#ebf8ff;color:#2b6cb0;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                        {{ number_format($lb->total_jam,1) }} jam
                    </span>
                </td>
                <td style="max-width:140px;font-size:12px;color:#4a5568;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    title="{{ $lb->alasan }}">{{ $lb->alasan }}</td>
                <td>
                    <span style="background:{{ $statBg }};color:{{ $statColor }};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                        {{ $statLabels[$lb->status] ?? ucfirst($lb->status) }}
                    </span>
                </td>
                <td style="font-size:12px;color:#718096;">
                    @if($lb->approver)
                        {{ $lb->approver->name }}<br>
                        <small>{{ $lb->approved_at?->format('d/m/Y') }}</small>
                    @else —
                    @endif
                </td>
                @if(auth()->user()->hasPermission('lembur.approve') || auth()->user()->hasPermission('lembur.edit') || auth()->user()->hasPermission('lembur.hapus'))
                <td style="text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;">
                        @if($lb->status === 'pending')
                        @if(auth()->user()->hasPermission('lembur.approve'))
                        <form method="POST" action="{{ route('admin.lemburan.approve', $lb) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" title="Setujui"
                                    onclick="return confirm('Setujui lembur ini?')">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <button class="btn btn-danger btn-sm" title="Tolak"
                                onclick="openRejectLembur({{ $lb->id }})">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif
                        @if(auth()->user()->hasPermission('lembur.edit'))
                        <button class="btn btn-warning btn-sm" title="Edit"
                                onclick="openEditLembur({{ $lb->id }},'{{ $lb->tanggal->format('Y-m-d') }}','{{ substr($lb->jam_mulai,0,5) }}','{{ substr($lb->jam_selesai,0,5) }}','{{ addslashes($lb->alasan) }}','{{ addslashes($lb->keterangan ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        @endif
                        @endif
                        @if(auth()->user()->hasPermission('lembur.hapus'))
                        <form method="POST" action="{{ route('admin.lemburan.destroy', $lb) }}"
                              onsubmit="return confirm('Hapus data lembur ini?')">
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

@if(auth()->user()->hasPermission('lembur.tambah') || auth()->user()->hasPermission('lembur.edit'))
{{-- Modal Tambah --}}
<div id="modalAddLembur" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:500px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h6 style="font-weight:700;color:#2d3748;margin:0;"><i class="fas fa-plus-circle" style="color:#c05621;"></i> Tambah Lembur</h6>
            <button onclick="closeModal('modalAddLembur')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <div style="background:#fff7ed;border-radius:9px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#c05621;">
            <i class="fas fa-user"></i> <strong>{{ $karyawan->nama }}</strong> — {{ $karyawan->jabatan }}
        </div>
        <form method="POST" action="{{ route('admin.lemburan.store', $karyawan) }}">
            @csrf
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal *</label>
                <input type="date" name="tanggal" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jam Mulai *</label>
                    <input type="time" name="jam_mulai" class="form-control" required>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jam Selesai *</label>
                    <input type="time" name="jam_selesai" class="form-control" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Alasan Lembur *</label>
                <input type="text" name="alasan" class="form-control" required maxlength="255" placeholder="Contoh: Persiapan audit, deadline project...">
            </div>
            <div class="form-group" style="margin-bottom:18px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2" maxlength="500" placeholder="Catatan tambahan (opsional)"></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('modalAddLembur')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEditLembur" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:500px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h6 style="font-weight:700;color:#2d3748;margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Lembur</h6>
            <button onclick="closeModal('modalEditLembur')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form id="formEditLembur" method="POST">
            @csrf @method('PUT')
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal *</label>
                <input type="date" name="tanggal" id="elTanggal" class="form-control" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jam Mulai *</label>
                    <input type="time" name="jam_mulai" id="elMulai" class="form-control" required>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jam Selesai *</label>
                    <input type="time" name="jam_selesai" id="elSelesai" class="form-control" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Alasan *</label>
                <input type="text" name="alasan" id="elAlasan" class="form-control" required maxlength="255">
            </div>
            <div class="form-group" style="margin-bottom:18px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Keterangan</label>
                <textarea name="keterangan" id="elKet" class="form-control" rows="2" maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('modalEditLembur')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tolak --}}
<div id="modalRejectLembur" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:400px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h6 style="font-weight:700;color:#c53030;margin:0;"><i class="fas fa-times-circle"></i> Tolak Lembur</h6>
            <button onclick="closeModal('modalRejectLembur')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form id="formRejectLembur" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Alasan Penolakan</label>
                <textarea name="alasan_tolak" class="form-control" rows="3" placeholder="Tulis alasan penolakan..."></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('modalRejectLembur')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditLembur(id,tgl,mulai,selesai,alasan,ket){
    document.getElementById('elTanggal').value = tgl;
    document.getElementById('elMulai').value   = mulai;
    document.getElementById('elSelesai').value = selesai;
    document.getElementById('elAlasan').value  = alasan;
    document.getElementById('elKet').value     = ket;
    document.getElementById('formEditLembur').action = '{{ route("admin.lemburan.update", ":id") }}'.replace(':id', id);
    openModal('modalEditLembur');
}
function openRejectLembur(id){
    document.getElementById('formRejectLembur').action = '{{ route("admin.lemburan.reject", ":id") }}'.replace(':id', id);
    openModal('modalRejectLembur');
}
['modalAddLembur','modalEditLembur','modalRejectLembur'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e){ if(e.target===this) closeModal(id); });
});
</script>
@endif
