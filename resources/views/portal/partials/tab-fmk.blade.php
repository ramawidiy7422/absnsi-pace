
@if(session('success'))
<div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;color:#276749;">
    <i class="fas fa-check-circle" style="font-size:18px;"></i>
    <span style="font-weight:600;">{{ session('success') }}</span>
</div>
@endif

{{-- Form Ajukan FMK --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#c05621,#e53e3e);color:white;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-door-open"></i> Ajukan Izin Keluar (FMK)</h6>
        <span style="font-size:11px;opacity:.85;">No: {{ $nomorFMK }}</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('portal.fmk.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">

                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Tanggal <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                    @error('tanggal') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Rencana Jam Keluar <span style="color:#e53e3e;">*</span></label>
                    <input type="time" name="jam_keluar" class="form-control" value="{{ old('jam_keluar') }}" required>
                    <div style="font-size:10px;color:#a0aec0;margin-top:3px;">Estimasi rencana, bukan jam absen aktual</div>
                    @error('jam_keluar') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Rencana Jam Kembali</label>
                    <input type="time" name="jam_kembali" class="form-control" value="{{ old('jam_kembali') }}">
                    <div style="font-size:10px;color:#a0aec0;margin-top:3px;">Estimasi rencana. Kosongkan jika tidak kembali</div>
                </div>

                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Status Kembali <span style="color:#e53e3e;">*</span></label>
                    <select name="kembali_bekerja" class="form-select" required>
                        <option value="kembali"       {{ old('kembali_bekerja')=='kembali'       ?'selected':'' }}>✓ Kembali Bekerja</option>
                        <option value="tidak_kembali" {{ old('kembali_bekerja')=='tidak_kembali' ?'selected':'' }}>✗ Tidak Kembali</option>
                    </select>
                    @error('kembali_bekerja') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Alasan <span style="color:#e53e3e;">*</span></label>
                    <select name="alasan" class="form-select" required>
                        <option value="">-- Pilih Alasan --</option>
                        <option value="sakit"           {{ old('alasan')=='sakit'           ?'selected':'' }}>Sakit</option>
                        <option value="urusan_keluarga" {{ old('alasan')=='urusan_keluarga' ?'selected':'' }}>Urusan Keluarga</option>
                        <option value="urusan_pribadi"  {{ old('alasan')=='urusan_pribadi'  ?'selected':'' }}>Urusan Pribadi</option>
                        <option value="keperluan_dinas" {{ old('alasan')=='keperluan_dinas' ?'selected':'' }}>Keperluan Dinas</option>
                        <option value="lainnya"         {{ old('alasan')=='lainnya'         ?'selected':'' }}>Lainnya</option>
                    </select>
                    @error('alasan') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Keterangan Tambahan</label>
                    <textarea name="keterangan" class="form-control" rows="2" maxlength="300" placeholder="Keterangan opsional...">{{ old('keterangan') }}</textarea>
                    @error('keterangan') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="btn btn-danger" style="font-weight:700;">
                    <i class="fas fa-paper-plane"></i> Ajukan FMK
                </button>
                <div style="font-size:11px;color:#a0aec0;margin-top:8px;">
                    <i class="fas fa-info-circle"></i> Formulir diproses berjenjang: SPV → Manajer → HRD.
                    Setelah disetujui HRD, absen pergi &amp; kembali dilakukan di <strong>kiosk</strong> (scan wajah/QR), tombol "Absen Pergi/Kembali (FMK)".
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Riwayat FMK --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-history" style="color:#c05621;"></i> Riwayat FMK Saya</h6>
    </div>
    <div class="card-body" style="padding:0;">
        @forelse($riwayatFmk as $fmk)
        @php
            $statusStyle = match($fmk->status) {
                'disetujui'       => ['bg'=>'#f0fff4','color'=>'#276749','label'=>'Disetujui'],
                'ditolak'         => ['bg'=>'#fff5f5','color'=>'#c53030','label'=>'Ditolak'],
                'pending_spv'     => ['bg'=>'#fffbeb','color'=>'#c05621','label'=>'Menunggu SPV'],
                'pending_manajer' => ['bg'=>'#fffbeb','color'=>'#c05621','label'=>'Menunggu Manajer'],
                'pending_hrd'     => ['bg'=>'#fffbeb','color'=>'#c05621','label'=>'Menunggu HRD'],
                default           => ['bg'=>'#fffbeb','color'=>'#c05621','label'=>'Menunggu'],
            };
            $alasanMap = [
                'sakit'           => 'Sakit',
                'urusan_keluarga' => 'Urusan Keluarga',
                'urusan_pribadi'  => 'Urusan Pribadi',
                'keperluan_dinas' => 'Keperluan Dinas',
                'lainnya'         => 'Lainnya',
            ];
        @endphp
        <div style="padding:14px 18px;border-bottom:1px solid #f0f0f0;">
            <div style="display:flex;gap:14px;align-items:center;">
                <div style="width:44px;height:44px;border-radius:10px;background:#fff5f5;border:1px solid #fed7d7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-door-open" style="color:#c05621;font-size:16px;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-family:monospace;color:#718096;">{{ $fmk->nomor_fmk }}</div>
                    <div style="font-size:13px;font-weight:600;color:#2d3748;margin-top:2px;">
                        {{ $fmk->tanggal->translatedFormat('d M Y') }}
                        &mdash; Rencana Pukul {{ substr($fmk->jam_keluar,0,5) }}
                        @if($fmk->jam_kembali) s/d {{ substr($fmk->jam_kembali,0,5) }} @endif
                    </div>
                    <div style="font-size:11px;color:#a0aec0;margin-top:2px;">
                        {{ $alasanMap[$fmk->alasan] ?? ucfirst($fmk->alasan) }}
                        @if($fmk->keterangan) — {{ $fmk->keterangan }} @endif
                    </div>
                </div>
                <div style="flex-shrink:0;text-align:right;">
                    <span style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['color'] }};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">
                        {{ $statusStyle['label'] }}
                    </span>
                    <div style="font-size:10px;color:#a0aec0;margin-top:4px;">{{ $fmk->created_at->diffForHumans() }}</div>
                </div>
            </div>

            {{-- Progress Approval Berjenjang --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:12px;margin-left:58px;">
                @foreach([
                    ['SPV', $fmk->spvApprover?->name, 'spv'],
                    ['Manajer', $fmk->manajerApprover?->name, 'manajer'],
                    ['HRD', $fmk->hrdApprover?->name, 'hrd'],
                ] as [$label, $nama, $stageKey])
                @php
                    $ditolakDiSini = $fmk->status === 'ditolak' && ($fmk->rejected_stage ?? null) === $stageKey;
                    $stepBg  = $ditolakDiSini ? '#fff5f5' : ($nama ? '#f0fff4' : '#f8fafc');
                    $stepCol = $ditolakDiSini ? '#c53030' : ($nama ? '#276749' : '#a0aec0');
                @endphp
                <div style="background:{{ $stepBg }};border-radius:8px;padding:6px 8px;text-align:center;">
                    <i class="fas {{ $ditolakDiSini ? 'fa-times-circle' : ($nama ? 'fa-check-circle' : 'fa-clock') }}" style="color:{{ $stepCol }};font-size:12px;"></i>
                    <div style="font-size:10px;font-weight:700;color:#2d3748;">{{ $label }}</div>
                    <div style="font-size:9px;color:{{ $stepCol }};">{{ $nama ?? 'Menunggu' }}</div>
                </div>
                @endforeach
            </div>

            {{-- Absen Pergi/Kembali via Kiosk (tampil setelah disetujui) --}}
            @if($fmk->status === 'disetujui')
            <div style="margin-top:10px;margin-left:58px;background:#f8fafc;border-radius:8px;padding:10px 12px;font-size:11px;">
                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <div>
                        <span style="color:#a0aec0;">Absen Pergi (kiosk):</span>
                        <strong style="color:{{ $fmk->pergi_at ? '#276749' : '#c05621' }};">
                            {{ $fmk->pergi_at ? $fmk->pergi_at->translatedFormat('d M Y, H:i') : 'Belum absen pergi' }}
                        </strong>
                    </div>
                    <div>
                        <span style="color:#a0aec0;">Absen Kembali (kiosk):</span>
                        <strong style="color:{{ $fmk->kembali_at ? '#276749' : '#c05621' }};">
                            {{ $fmk->kembali_at ? $fmk->kembali_at->translatedFormat('d M Y, H:i') : 'Belum absen kembali' }}
                        </strong>
                    </div>
                    @if($fmk->kembali_at)
                    <div>
                        <span style="color:#a0aec0;">Durasi:</span>
                        <strong style="color:#2d3748;">
                            @if($fmk->alasan === 'keperluan_dinas')
                                Jam kerja penuh (dinas)
                            @else
                                {{ $fmk->durasi_menit }} menit
                            @endif
                        </strong>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @empty
        <div style="padding:50px;text-align:center;color:#a0aec0;">
            <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:10px;opacity:.4;"></i>
            <div>Belum ada riwayat FMK.</div>
        </div>
        @endforelse
    </div>
    @if($riwayatFmk->hasPages())
    <div style="padding:14px 18px;">{{ $riwayatFmk->links() }}</div>
    @endif
</div>

