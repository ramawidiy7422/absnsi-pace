{{-- Stat bulan ini --}}
@php
    $tarifPerJam  = (float) (\App\Models\Pengaturan::get('tarif_lembur_per_jam') ?? 0);
    $estimasiUpah = $jamBulanIni * $tarifPerJam;
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px;">
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#c05621;">{{ number_format($jamBulanIni,1) }}</div>
        <div style="font-size:11px;color:#718096;">Jam Lembur Bulan Ini</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#38a169;">{{ $lemburApproved }}</div>
        <div style="font-size:11px;color:#718096;">Disetujui</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#d69e2e;">{{ $lemburPending }}</div>
        <div style="font-size:11px;color:#718096;">Pending</div>
    </div>
    @if($tarifPerJam > 0)
    <div style="background:#f0fff4;border-radius:12px;padding:16px;border:1px solid #9ae6b4;text-align:center;">
        <div style="font-size:14px;font-weight:800;color:#276749;">Rp {{ number_format($estimasiUpah,0,',','.') }}</div>
        <div style="font-size:11px;color:#718096;">Estimasi Upah Lembur</div>
    </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
    {{-- Form --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-plus-circle" style="color:#ed8936;"></i> Ajukan Lembur</h6></div>
        <div class="card-body">

            {{-- Banner SPK aktif --}}
            @if($spkAktif)
            <div style="background:#dbeafe;border:1px solid #93c5fd;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                <div style="font-weight:700;color:#1e40af;font-size:13px;"><i class="fas fa-file-alt"></i> Ada SPK Lembur Aktif Hari Ini</div>
                <div style="font-size:12px;color:#1e3a8a;margin-top:4px;">
                    <b>{{ $spkAktif->nomor_spk }}</b> — {{ substr($spkAktif->jam_mulai,0,5) }} s/d {{ substr($spkAktif->jam_selesai,0,5) }}<br>
                    {{ $spkAktif->pekerjaan }}
                </div>
                <div style="font-size:11px;color:#3b82f6;margin-top:4px;">Pengajuan lembur Anda akan dikaitkan dengan SPK ini.</div>
            </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" style="border-radius:8px;margin-bottom:12px;">
                    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
                </div>
            @endif

            @if($lemburWajibSpk && !$spkAktif)
            <div style="background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;padding:14px 16px;text-align:center;color:#c53030;">
                <i class="fas fa-lock" style="font-size:20px;display:block;margin-bottom:8px;"></i>
                <div style="font-weight:700;font-size:13px;">Belum Ada SPK Lembur Aktif</div>
                <div style="font-size:12px;margin-top:4px;color:#9b2c2c;">
                    Sistem mengharuskan Surat Perintah Kerja (SPK) sebelum lembur. Hubungi SPV/Manajer/HRD Anda untuk pembuatan SPK.
                </div>
            </div>
            @else
            <form method="POST" action="{{ route('portal.lembur.store') }}">
                @csrf
                <div class="form-group">
                    <label>Tanggal Lembur <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', today()->toDateString()) }}" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Jam Mulai <span style="color:#e53e3e;">*</span></label>
                        <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', $spkAktif ? substr($spkAktif->jam_mulai,0,5) : '') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai <span style="color:#e53e3e;">*</span></label>
                        <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', $spkAktif ? substr($spkAktif->jam_selesai,0,5) : '') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alasan / Tujuan Lembur <span style="color:#e53e3e;">*</span></label>
                    <textarea name="alasan" class="form-control" rows="2" required placeholder="Alasan singkat mengapa lembur...">{{ old('alasan') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Deskripsi Pekerjaan yang Dilakukan</label>
                    <textarea name="deskripsi_kerja" class="form-control" rows="3" maxlength="500" placeholder="Detail pekerjaan/tugas yang dikerjakan saat lembur...">{{ old('deskripsi_kerja', $spkAktif?->pekerjaan) }}</textarea>
                </div>
                @if(!$spkAktif)
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;font-size:13px;">
                    <input type="checkbox" id="is_mendadak" name="is_mendadak" value="1" {{ old('is_mendadak') ? 'checked' : '' }}>
                    <label for="is_mendadak" style="margin:0;cursor:pointer;color:#92400e;">Lembur mendadak / tidak terencana</label>
                </div>
                @endif
                <button type="submit" class="btn btn-primary" style="width:100%;background:#ed8936;border-color:#ed8936;">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-history" style="color:#4299e1;"></i> Riwayat Lembur</h6></div>
        <div class="card-body" style="padding:0;">
            @forelse($riwayatLembur as $lb)
            @php
                $sbLb  = $lb->status_badge;
                $jbLb  = $lb->jenis_badge;
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
                            <div style="font-weight:700;color:#2d3748;font-size:13px;">{{ $lb->tanggal->format('d M Y') }}</div>
                            <span style="background:{{ $jbLb['bg'] }};color:{{ $jbLb['color'] }};padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700;">
                                <i class="{{ $jbLb['icon'] }}" style="font-size:9px;"></i> {{ $jbLb['label'] }}
                            </span>
                        </div>
                        <div style="font-size:12px;color:#718096;">
                            {{ substr($lb->jam_mulai,0,5) }} — {{ substr($lb->jam_selesai,0,5) }}
                            <span style="font-weight:700;color:#2d3748;">({{ $lb->total_jam }} jam)</span>
                        </div>
                        @if($lb->spk)
                        <div style="font-size:10px;color:#3b82f6;margin-top:2px;">
                            <i class="fas fa-link"></i> {{ $lb->spk->nomor_spk }}
                        </div>
                        @endif
                        @if($lb->alasan)
                        <div style="font-size:11px;color:#a0aec0;margin-top:2px;">{{ Str::limit($lb->alasan, 60) }}</div>
                        @endif
                        @if($lb->status === 'rejected' && $lb->alasan_tolak)
                        <div style="font-size:11px;color:#c53030;margin-top:2px;">Alasan tolak: {{ $lb->alasan_tolak }}</div>
                        @endif
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                        <span style="background:{{ $sbLb['bg'] }};color:{{ $sbLb['color'] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;">
                            {{ $sbLb['label'] }}
                        </span>
                        @if($lb->status === 'pending')
                        <form method="POST" action="{{ route('portal.lembur.cancel', $lb) }}" onsubmit="return confirm('Batalkan pengajuan lembur ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:10px;color:#dc2626;background:none;border:1px solid #fca5a5;border-radius:20px;padding:2px 8px;cursor:pointer;">
                                <i class="fas fa-times"></i> Batalkan
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#a0aec0;">Belum ada riwayat lembur.</div>
            @endforelse
            <div style="padding:12px;">{{ $riwayatLembur->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>
