@php $izinList = $karyawan->izin; @endphp

<div style="display:grid;grid-template-columns:{{ auth()->user()->hasPermission('izin.tambah') ? '1fr 1fr' : '1fr' }};gap:16px;align-items:start;">

@if(auth()->user()->hasPermission('izin.tambah'))
    {{-- Form Tambah --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-plus-circle" style="color:#ed8936;"></i> Catat Izin / Tidak Masuk</h6></div>
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
                        <option value="sakit"  {{ old('jenis')=='sakit' ?'selected':'' }}>🤒 Sakit</option>
                        <option value="izin"   {{ old('jenis','izin')=='izin'  ?'selected':'' }}>📋 Izin</option>
                        <option value="alfa"   {{ old('jenis')=='alfa'  ?'selected':'' }}>❌ Alfa</option>
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

    {{-- Riwayat --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;"><i class="fas fa-history" style="color:#4299e1;"></i> Riwayat Izin</h6>
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
                                <button type="submit" style="padding:3px 8px;background:#c6f6d5;color:#276749;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">✓ Setuju</button>
                            </form>
                            <form method="POST" action="{{ route('admin.izin.reject', $iz) }}">
                                @csrf
                                <button type="submit" style="padding:3px 8px;background:#fed7e2;color:#c53030;border:none;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;">✗ Tolak</button>
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
            <div style="padding:30px;text-align:center;color:#a0aec0;">Belum ada riwayat izin.</div>
            @endforelse
        </div>
    </div>
</div>
