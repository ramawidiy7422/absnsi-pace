{{-- Saldo Cuti --}}
@php
    $belumBerhakCuti  = $saldoCuti->jatah === 0;
    $tglMasuk         = $karyawan->tanggal_masuk;
    $tglBerhakCuti    = $tglMasuk ? $tglMasuk->copy()->addYear() : null;
    $sisaHariBerhak   = $tglBerhakCuti ? (int) now()->startOfDay()->diffInDays($tglBerhakCuti->startOfDay(), false) : null;
    $pctTerpakai      = $saldoCuti->jatah > 0 ? round($saldoCuti->terpakai / $saldoCuti->jatah * 100) : 0;
    $barColor         = $pctTerpakai >= 90 ? '#c53030' : ($pctTerpakai >= 70 ? '#c05621' : '#276749');
    $jatahPenuh       = (int) (\App\Models\Pengaturan::get('jatah_cuti_tahunan') ?? 12);
    $isProporsional   = $saldoCuti->jatah > 0 && $saldoCuti->jatah < $jatahPenuh;
@endphp

@if($belumBerhakCuti)
{{-- Banner belum berhak cuti --}}
<div style="background:linear-gradient(135deg,#fff5f5,#fffaf0);border:1.5px solid #feb2b2;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:16px;">
    <div style="font-size:36px;flex-shrink:0;">⏳</div>
    <div style="flex:1;">
        <div style="font-size:14px;font-weight:800;color:#c53030;margin-bottom:4px;">Belum Berhak Mengambil Cuti Tahunan</div>
        <div style="font-size:12px;color:#718096;line-height:1.6;">
            Karyawan berhak cuti setelah masa kerja genap <strong>1 tahun</strong>.
            @if($tglMasuk)
                Tanggal masuk: <strong>{{ $tglMasuk->format('d M Y') }}</strong>.
                @if($sisaHariBerhak > 0)
                    Hak cuti dimulai pada <strong>{{ $tglBerhakCuti->format('d M Y') }}</strong>
                    <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;margin-left:4px;">{{ $sisaHariBerhak }} hari lagi</span>
                @else
                    <span style="color:#38a169;font-weight:700;"> — Hak cuti sudah aktif, hubungi HRD untuk update saldo.</span>
                @endif
            @endif
        </div>
        <div style="margin-top:10px;background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;">
            @if($tglMasuk && $sisaHariBerhak !== null)
            @php
                $totalHariMenuju = 365;
                $sudahLewat      = max(0, $totalHariMenuju - max(0, $sisaHariBerhak));
                $pctMasaKerja    = min(100, round($sudahLewat / $totalHariMenuju * 100));
            @endphp
            <div style="width:{{ $pctMasaKerja }}%;background:linear-gradient(90deg,#f6ad55,#ed8936);height:100%;border-radius:999px;"></div>
            @endif
        </div>
        @if($tglMasuk && $sisaHariBerhak !== null)
        <div style="font-size:10px;color:#a0aec0;margin-top:4px;">Masa kerja: {{ $tglMasuk->diffForHumans(now(), true) }}</div>
        @endif
    </div>
</div>
@else
{{-- Saldo normal --}}
<div style="background:linear-gradient(135deg,#f0fff4,#ebf8ff);border:1px solid #9ae6b4;border-radius:14px;padding:20px 24px;margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
        <div>
            <div style="font-size:14px;font-weight:700;color:#2d3748;"><i class="fas fa-calendar-check" style="color:#276749;margin-right:6px;"></i>Saldo Cuti Tahunan {{ now()->year }}</div>
            <div style="font-size:12px;color:#718096;margin-top:2px;">Pengajuan yang disetujui otomatis tercatat di absensi</div>
        </div>
        <div style="text-align:right;">
            <span style="font-size:32px;font-weight:900;color:{{ $barColor }};">{{ $saldoCuti->sisa }}</span>
            <span style="font-size:13px;color:#718096;"> / {{ $saldoCuti->jatah }} hari tersisa</span>
        </div>
    </div>
    <div style="background:#e2e8f0;border-radius:999px;height:10px;overflow:hidden;">
        <div style="width:{{ $pctTerpakai }}%;background:{{ $barColor }};height:100%;border-radius:999px;transition:width 0.4s;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:11px;color:#718096;">
        <span>Terpakai: <strong style="color:#c53030;">{{ $saldoCuti->terpakai }} hari</strong></span>
        <span>{{ $pctTerpakai }}% digunakan</span>
        <span>Jatah: <strong>{{ $saldoCuti->jatah }} hari</strong></span>
    </div>
    @if($isProporsional)
    <div style="margin-top:10px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:11px;color:#92400e;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-info-circle" style="flex-shrink:0;"></i>
        <span>
            Jatah cuti Anda <strong>{{ $saldoCuti->jatah }} dari {{ $jatahPenuh }} hari</strong> karena dihitung proporsional sejak ulang tahun kerja
            @if($tglBerhakCuti) (<strong>{{ $tglBerhakCuti->format('d M Y') }}</strong>) @endif
            hingga akhir tahun {{ now()->year }}.
            Tahun berikutnya Anda mendapat jatah penuh {{ $jatahPenuh }} hari.
        </span>
    </div>
    @endif
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
    {{-- Form Pengajuan --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Ajukan Cuti / Izin / Sakit</h6></div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger" style="border-radius:8px;margin-bottom:12px;">
                    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('portal.cuti.store') }}">
                @csrf
                <div class="form-group">
                    <label>Jenis <span style="color:#e53e3e;">*</span></label>
                    <select name="type" id="cutiType" class="form-select" required onchange="toggleTanggalMin(this.value)">
                        <option value="cuti" {{ old('type')=='cuti' ? 'selected' : '' }} {{ $belumBerhakCuti ? 'disabled' : '' }}>
                            Cuti Tahunan{{ $belumBerhakCuti ? ' (belum berhak)' : '' }}
                        </option>
                        <option value="izin"  {{ old('type')=='izin'  ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ old('type')=='sakit' ? 'selected' : '' }}>Sakit</option>
                    </select>
                    @if($belumBerhakCuti)
                    <div style="font-size:11px;color:#c05621;margin-top:4px;"><i class="fas fa-info-circle"></i> Cuti tahunan hanya bisa diajukan setelah masa kerja 1 tahun. Anda tetap bisa mengajukan Izin atau Sakit.</div>
                    @endif
                    <div style="font-size:11px;color:#718096;margin-top:4px;"><i class="fas fa-info-circle"></i> Cuti tahunan diproses berjenjang (SPV → Manajer → HRD). Izin/Sakit bisa langsung diproses HRD.</div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Tanggal Mulai <span style="color:#e53e3e;">*</span></label>
                        <input type="date" name="start_date" id="startDate" class="form-control" value="{{ old('start_date') }}" required min="{{ today()->toDateString() }}">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai <span style="color:#e53e3e;">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required min="{{ today()->toDateString() }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Alasan cuti/izin/sakit...">{{ old('keterangan') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                </button>
            </form>
        </div>

        <script>
        function toggleTanggalMin(type) {
            var startDate = document.getElementById('startDate');
            if (type === 'cuti') {
                startDate.min = '{{ today()->toDateString() }}';
            } else {
                startDate.removeAttribute('min'); // Izin/Sakit boleh tanggal lampau (lapor telat)
            }
        }
        toggleTanggalMin(document.getElementById('cutiType').value);
        </script>
    </div>

    {{-- Riwayat --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-history" style="color:#4299e1;"></i> Riwayat Pengajuan</h6></div>
        <div class="card-body" style="padding:0;">
            @forelse($riwayatCuti as $lr)
            @php
                $warna = ['pending'=>['#fef9c3','#854d0e'],'disetujui'=>['#c6f6d5','#276749'],'ditolak'=>['#fed7e2','#c53030']];
                $w = $warna[$lr->status] ?? $warna['pending'];
                $labelStatus = match($lr->status) {
                    'pending_spv'     => 'Menunggu SPV',
                    'pending_manajer' => 'Menunggu Manajer',
                    'pending_hrd'     => 'Menunggu HRD',
                    default           => ucfirst($lr->status),
                };
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <span style="font-weight:700;color:#2d3748;font-size:13px;">{{ ucfirst($lr->type) }}</span>
                        <div style="font-size:12px;color:#718096;margin-top:2px;">
                            {{ $lr->start_date->format('d M Y') }} — {{ $lr->end_date->format('d M Y') }}
                        </div>
                        @if($lr->keterangan)
                        <div style="font-size:11px;color:#a0aec0;margin-top:2px;">{{ $lr->keterangan }}</div>
                        @endif
                    </div>
                    <span style="background:{{ $w[0] }};color:{{ $w[1] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;">
                        {{ $labelStatus }}
                    </span>
                </div>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#a0aec0;">Belum ada riwayat pengajuan.</div>
            @endforelse
            <div style="padding:12px;">{{ $riwayatCuti->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

{{-- Riwayat Lembur --}}
@if($riwayatLembur->count())
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-business-time" style="color:#276749;"></i> Riwayat Lembur</h6></div>
    <div class="card-body" style="padding:0;">
        @foreach($riwayatLembur as $lb)
        @php $lbWarna = $lb->status_badge; @endphp
        <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <span style="font-weight:700;color:#2d3748;font-size:13px;">{{ $lb->tanggal->translatedFormat('d M Y') }}</span>
                    <div style="font-size:12px;color:#718096;margin-top:2px;">
                        {{ $lb->jam_mulai }} — {{ $lb->jam_selesai }} ({{ $lb->total_jam ?? '-' }} jam)
                    </div>
                    @if($lb->alasan)
                    <div style="font-size:11px;color:#a0aec0;margin-top:2px;">{{ $lb->alasan }}</div>
                    @endif
                </div>
                <span style="background:{{ $lbWarna['bg'] }};color:{{ $lbWarna['color'] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;">
                    {{ $lbWarna['label'] }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Riwayat Izin Terlambat (via WA) --}}
@if($riwayatIzinTerlambat->count())
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-clock" style="color:#2b6cb0;"></i> Riwayat Izin Terlambat</h6></div>
    <div class="card-body" style="padding:0;">
        @foreach($riwayatIzinTerlambat as $it)
        @php
            $itWarna = match($it->status) {
                'disetujui' => ['#c6f6d5','#276749'],
                'ditolak'   => ['#fed7e2','#c53030'],
                default     => ['#fef9c3','#854d0e'],
            };
        @endphp
        <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <span style="font-weight:700;color:#2d3748;font-size:13px;">{{ $it->tanggal->translatedFormat('d M Y') }}</span>
                    <div style="font-size:12px;color:#718096;margin-top:2px;">{{ $it->keterangan }}</div>
                    <div style="font-size:11px;color:#a0aec0;margin-top:2px;">Dicatat oleh: {{ $it->inputOleh?->name ?? '-' }}</div>
                    @if($it->catatan_approver)
                    <div style="font-size:11px;color:#805ad5;margin-top:2px;font-style:italic;">
                        <i class="fas fa-comment"></i> {{ $it->catatan_approver }}
                    </div>
                    @endif
                </div>
                <span style="background:{{ $itWarna[0] }};color:{{ $itWarna[1] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;">
                    {{ ucfirst($it->status) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
