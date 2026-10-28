{{-- Partial: Tab Cuti & Ketidakhadiran (gabungan leaverequest + izin) --}}
@php
    $saldoCuti  = \App\Models\CutiSaldo::getSaldo($karyawan->id);
    $izinList   = $karyawan->izin;
    $statColors = [
        'pending_spv'     => ['#fef9c3','#854d0e'],
        'pending_manajer' => ['#fef9c3','#854d0e'],
        'pending_hrd'     => ['#fef9c3','#854d0e'],
        'disetujui'       => ['#c6f6d5','#276749'],
        'ditolak'         => ['#fed7e2','#c53030'],
    ];
    $statLabels = [
        'pending_spv'     => 'Menunggu SPV',
        'pending_manajer' => 'Menunggu Manajer',
        'pending_hrd'     => 'Menunggu HRD',
        'disetujui'       => 'Disetujui',
        'ditolak'         => 'Ditolak',
    ];
    $typeColors = [
        'cuti'  => ['#eff6ff','#3b82f6'],
        'izin'  => ['#fffbeb','#f59e0b'],
        'sakit' => ['#fef2f2','#ef4444'],
    ];
@endphp

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- BAGIAN 1: PENGAJUAN CUTI                                   --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
    <h6 style="font-weight:700;color:#2d3748;margin:0;font-size:14px;">
        <i class="fas fa-calendar-alt" style="color:#4299e1;"></i> Pengajuan Cuti
    </h6>
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

@if($karyawan->leaverequests->isEmpty())
<div style="text-align:center;padding:24px;color:#94a3b8;background:#f8fafc;border-radius:10px;margin-bottom:20px;">
    <i class="fas fa-calendar-check" style="font-size:32px;opacity:0.3;display:block;margin-bottom:8px;"></i>
    <p style="margin:0;font-size:13px;">Belum ada pengajuan cuti</p>
</div>
@else
<div class="table-responsive" style="margin-bottom:4px;">
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
                    <span style="background:{{ $typeBg }};color:{{ $typeColor }};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;text-transform:capitalize;">
                        {{ ucfirst($lr->type) }}
                    </span>
                </td>
                <td style="font-size:13px;">{{ $lr->start_date->format('d/m/Y') }}</td>
                <td style="font-size:13px;">{{ $lr->end_date->format('d/m/Y') }}</td>
                <td><span style="font-size:12px;font-weight:600;color:#4a5568;">{{ $durasi }} hari</span></td>
                <td style="max-width:150px;font-size:12px;color:#718096;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $lr->keterangan ?? '-' }}
                </td>
                <td>
                    <span style="background:{{ $statBg }};color:{{ $statColor }};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                        {{ $statLabels[$lr->status] ?? ucfirst($lr->status) }}
                    </span>
                </td>
                <td style="font-size:12px;color:#718096;">
                    @if($lr->approver)
                        {{ $lr->approver->name }}<br>
                        <small>{{ $lr->approved_at?->format('d/m/Y') }}</small>
                    @else -
                    @endif
                </td>
                @if(auth()->user()->hasPermission('cuti.approve') || auth()->user()->hasPermission('cuti.hapus'))
                <td style="text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;align-items:center;">
                        @if(!$lr->isFinal())
                            @can('approve-leaverequest', $lr)
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
                            @endcan
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

{{-- Divider --}}
<div style="border-top:2px dashed #e2e8f0;margin:24px 0;"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- BAGIAN 2: CATATAN KETIDAKHADIRAN HARIAN (IZIN/SAKIT/ALFA) --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:{{ auth()->user()->hasPermission('izin.tambah') ? '1fr 1fr' : '1fr' }};gap:16px;align-items:start;">

@if(auth()->user()->hasPermission('izin.tambah'))
    {{-- Form Tambah Izin Harian --}}
    <div class="card">
        <div class="card-header">
            <h6 style="margin:0;font-weight:700;">
                <i class="fas fa-user-times" style="color:#ed8936;"></i> Catat Ketidakhadiran Harian
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.izin.store', $karyawan) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Tanggal <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Jenis <span style="color:#e53e3e;">*</span></label>
                    <select name="jenis" class="form-select" required>
                        <option value="sakit"  {{ old('jenis')=='sakit' ?'selected':'' }}>Sakit</option>
                        <option value="izin"   {{ old('jenis','izin')=='izin'  ?'selected':'' }}>Izin</option>
                        <option value="alfa"   {{ old('jenis')=='alfa'  ?'selected':'' }}>Alfa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Misal: demam, keperluan keluarga...">{{ old('keterangan') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Lampiran <small style="color:#718096;">(Surat dokter, dll. PDF/JPG maks 2MB)</small></label>
                    <input type="file" name="lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;background:#ed8936;border-color:#ed8936;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>
@endif

    {{-- Riwayat Ketidakhadiran --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;">
                <i class="fas fa-history" style="color:#4299e1;"></i> Riwayat Ketidakhadiran
            </h6>
            <div style="font-size:12px;color:#718096;">Total: {{ $izinList->count() }} hari</div>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($izinList as $iz)
            @php
                $badge = $iz->badge_color;
                $statusColor = match($iz->status) {
                    'disetujui' => ['#c6f6d5','#276749'],
                    'ditolak'   => ['#fed7e2','#c53030'],
                    default     => ['#fef9c3','#854d0e'],
                };
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <div style="flex:1;">
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <span style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;">
                                {{ $iz->label_jenis }}
                            </span>
                            <span style="font-weight:700;color:#2d3748;font-size:13px;">{{ $iz->tanggal->translatedFormat('d M Y') }}</span>
                            <span style="font-size:11px;color:#a0aec0;">{{ $iz->tanggal->translatedFormat('l') }}</span>
                        </div>
                        @if($iz->keterangan)
                        <div style="font-size:12px;color:#718096;margin-top:4px;">{{ $iz->keterangan }}</div>
                        @endif
                        @if($iz->lampiran)
                        <a href="{{ Storage::url($iz->lampiran) }}" target="_blank"
                           style="font-size:11px;color:#4299e1;margin-top:2px;display:inline-block;">
                            <i class="fas fa-paperclip"></i> Lihat Lampiran
                        </a>
                        @endif
                        @if($iz->catatan_approver)
                        <div style="font-size:11px;color:#805ad5;margin-top:2px;font-style:italic;">
                            <i class="fas fa-comment"></i> {{ $iz->catatan_approver }}
                        </div>
                        @endif
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                        <span style="background:{{ $statusColor[0] }};color:{{ $statusColor[1] }};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;">
                            {{ ucfirst($iz->status) }}
                        </span>
                        @if($iz->status === 'pending' && auth()->user()->hasPermission('izin.approve'))
                        <div style="display:flex;gap:4px;">
                            <form method="POST" action="{{ route('admin.izin.approve', $iz) }}">
                                @csrf
                                <button type="submit" style="padding:3px 8px;background:#c6f6d5;color:#276749;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">&#10003; Setuju</button>
                            </form>
                            <form method="POST" action="{{ route('admin.izin.reject', $iz) }}">
                                @csrf
                                <button type="submit" style="padding:3px 8px;background:#fed7e2;color:#c53030;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">&#10007; Tolak</button>
                            </form>
                        </div>
                        @endif
                        @if(auth()->user()->hasPermission('izin.hapus'))
                        <form method="POST" action="{{ route('admin.izin.destroy', $iz) }}" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:2px 7px;background:#f7fafc;color:#a0aec0;border:1px solid #e2e8f0;border-radius:5px;font-size:10px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#a0aec0;">Belum ada riwayat ketidakhadiran.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Divider --}}
<div style="border-top:2px dashed #e2e8f0;margin:24px 0;"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- BAGIAN 3: IZIN TERLAMBAT (VIA WA)                          --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@php $izinTerlambatList = $karyawan->izinTerlambat; @endphp
<div style="display:grid;grid-template-columns:{{ auth()->user()->hasPermission('izin-terlambat.tambah') ? '1fr 1fr' : '1fr' }};gap:16px;align-items:start;">

@if(auth()->user()->hasPermission('izin-terlambat.tambah'))
    {{-- Form Tambah Izin Terlambat --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-clock" style="color:#2b6cb0;"></i> Input Izin Terlambat (via WA)</h6></div>
        <div class="card-body">
            <p style="font-size:12px;color:#718096;margin-top:-4px;margin-bottom:12px;">
                Gunakan form ini untuk mencatat pengajuan izin terlambat yang disampaikan karyawan lewat chat WhatsApp.
            </p>
            <form method="POST" action="{{ route('admin.izin-terlambat.store', $karyawan) }}">
                @csrf
                <div class="form-group">
                    <label>Tanggal <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Keterangan (alasan dari WA) <span style="color:#e53e3e;">*</span></label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Misal: ban motor bocor, akan datang sekitar jam 09.00..." required>{{ old('keterangan') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;background:#2b6cb0;border-color:#2b6cb0;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>
@endif

    {{-- Riwayat Izin Terlambat --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;"><i class="fas fa-history" style="color:#2b6cb0;"></i> Riwayat Izin Terlambat</h6>
            <div style="font-size:12px;color:#718096;">Total: {{ $izinTerlambatList->count() }}</div>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($izinTerlambatList as $it)
            @php
                $itStatusColor = match($it->status) {
                    'disetujui' => ['#c6f6d5','#276749'],
                    'ditolak'   => ['#fed7e2','#c53030'],
                    default     => ['#fef9c3','#854d0e'],
                };
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <div style="flex:1;">
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <span style="font-weight:700;color:#2d3748;font-size:13px;">{{ $it->tanggal->translatedFormat('d M Y') }}</span>
                            <span style="font-size:11px;color:#a0aec0;">{{ $it->tanggal->translatedFormat('l') }}</span>
                        </div>
                        @if($it->keterangan)
                        <div style="font-size:12px;color:#718096;margin-top:4px;">{{ $it->keterangan }}</div>
                        @endif
                        <div style="font-size:11px;color:#a0aec0;margin-top:2px;">Diinput oleh: {{ $it->inputOleh?->name ?? '-' }}</div>
                        @if($it->catatan_approver)
                        <div style="font-size:11px;color:#805ad5;margin-top:2px;font-style:italic;">
                            <i class="fas fa-comment"></i> {{ $it->catatan_approver }}
                        </div>
                        @endif
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                        <span style="background:{{ $itStatusColor[0] }};color:{{ $itStatusColor[1] }};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;">
                            {{ ucfirst($it->status) }}
                        </span>
                        @if($it->status === 'pending' && auth()->user()->can('approve-izin-terlambat', $it))
                        <div style="display:flex;gap:4px;">
                            <form method="POST" action="{{ route('admin.izin-terlambat.approve', $it) }}">
                                @csrf
                                <button type="submit" style="padding:3px 8px;background:#c6f6d5;color:#276749;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">✓ Setuju</button>
                            </form>
                            <form method="POST" action="{{ route('admin.izin-terlambat.reject', $it) }}">
                                @csrf
                                <button type="submit" style="padding:3px 8px;background:#fed7e2;color:#c53030;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">✗ Tolak</button>
                            </form>
                        </div>
                        @endif
                        @if(auth()->user()->hasPermission('izin-terlambat.hapus'))
                        <form method="POST" action="{{ route('admin.izin-terlambat.destroy', $it) }}" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:2px 7px;background:#f7fafc;color:#a0aec0;border:1px solid #e2e8f0;border-radius:5px;font-size:10px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#a0aec0;">Belum ada riwayat izin terlambat.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Divider --}}
<div style="border-top:2px dashed #e2e8f0;margin:24px 0;"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- BAGIAN 4: IZIN PULANG CEPAT                                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@php $izinPulangCepatList = $karyawan->izinPulangCepat; @endphp
<div style="display:grid;grid-template-columns:{{ auth()->user()->hasPermission('izin-pulang-cepat.tambah') ? '1fr 1fr' : '1fr' }};gap:16px;align-items:start;">

@if(auth()->user()->hasPermission('izin-pulang-cepat.tambah'))
    {{-- Form Tambah Izin Pulang Cepat --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-sign-out-alt" style="color:#b7791f;"></i> Input Izin Pulang Cepat</h6></div>
        <div class="card-body">
            <p style="font-size:12px;color:#718096;margin-top:-4px;margin-bottom:12px;">
                Gunakan form ini untuk mencatat izin pulang lebih awal dari jam shift, supaya absen keluar karyawan tidak tercatat pulang cepat tanpa izin.
            </p>
            <form method="POST" action="{{ route('admin.izin-pulang-cepat.store', $karyawan) }}">
                @csrf
                <div class="form-group">
                    <label>Tanggal <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Keterangan (alasan) <span style="color:#e53e3e;">*</span></label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Misal: ada keperluan keluarga mendadak, izin pulang jam 14.00..." required>{{ old('keterangan') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;background:#b7791f;border-color:#b7791f;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>
@endif

    {{-- Riwayat Izin Pulang Cepat --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;"><i class="fas fa-history" style="color:#b7791f;"></i> Riwayat Izin Pulang Cepat</h6>
            <div style="font-size:12px;color:#718096;">Total: {{ $izinPulangCepatList->count() }}</div>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($izinPulangCepatList as $it)
            @php
                $itStatusColor = match($it->status) {
                    'disetujui' => ['#c6f6d5','#276749'],
                    'ditolak'   => ['#fed7e2','#c53030'],
                    default     => ['#fef9c3','#854d0e'],
                };
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <div style="flex:1;">
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <span style="font-weight:700;color:#2d3748;font-size:13px;">{{ $it->tanggal->translatedFormat('d M Y') }}</span>
                            <span style="font-size:11px;color:#a0aec0;">{{ $it->tanggal->translatedFormat('l') }}</span>
                        </div>
                        @if($it->keterangan)
                        <div style="font-size:12px;color:#718096;margin-top:4px;">{{ $it->keterangan }}</div>
                        @endif
                        <div style="font-size:11px;color:#a0aec0;margin-top:2px;">Diinput oleh: {{ $it->inputOleh?->name ?? '-' }}</div>
                        @if($it->catatan_approver)
                        <div style="font-size:11px;color:#805ad5;margin-top:2px;font-style:italic;">
                            <i class="fas fa-comment"></i> {{ $it->catatan_approver }}
                        </div>
                        @endif
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                        <span style="background:{{ $itStatusColor[0] }};color:{{ $itStatusColor[1] }};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;">
                            {{ ucfirst($it->status) }}
                        </span>
                        @if($it->status === 'pending' && auth()->user()->can('approve-izin-pulang-cepat', $it))
                        <div style="display:flex;gap:4px;">
                            <form method="POST" action="{{ route('admin.izin-pulang-cepat.approve', $it) }}">
                                @csrf
                                <button type="submit" style="padding:3px 8px;background:#c6f6d5;color:#276749;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">✓ Setuju</button>
                            </form>
                            <form method="POST" action="{{ route('admin.izin-pulang-cepat.reject', $it) }}">
                                @csrf
                                <button type="submit" style="padding:3px 8px;background:#fed7e2;color:#c53030;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">✗ Tolak</button>
                            </form>
                        </div>
                        @endif
                        @if(auth()->user()->hasPermission('izin-pulang-cepat.hapus'))
                        <form method="POST" action="{{ route('admin.izin-pulang-cepat.destroy', $it) }}" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:2px 7px;background:#f7fafc;color:#a0aec0;border:1px solid #e2e8f0;border-radius:5px;font-size:10px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#a0aec0;">Belum ada riwayat izin pulang cepat.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Tambah Cuti --}}
@if(auth()->user()->hasPermission('cuti.tambah') || auth()->user()->hasPermission('cuti.edit'))
<div id="modalAddLeave" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:520px;padding:24px;margin:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 style="font-weight:600;color:#2d3748;margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Pengajuan Cuti</h6>
            <button onclick="closeModal('modalAddLeave')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#718096;">&times;</button>
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
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Alasan/keterangan..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddLeave')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditLeave" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:520px;padding:24px;margin:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 style="font-weight:600;color:#2d3748;margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Pengajuan</h6>
            <button onclick="closeModal('modalEditLeave')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#718096;">&times;</button>
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
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
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
