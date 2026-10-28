<div class="card">
    <div class="card-header">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-history" style="color:#4299e1;"></i> Riwayat Kerja Internal</h6>
        <small style="color:#a0aec0;">Riwayat jabatan, mutasi, dan perubahan status Anda di perusahaan</small>
    </div>
    <div class="card-body">
        @if($riwayatKerja->isEmpty())
        <div style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-history" style="font-size:40px;opacity:0.2;display:block;margin-bottom:12px;"></i>
            <p style="margin:0;">Belum ada riwayat kerja.</p>
        </div>
        @else
        <div style="position:relative;padding-left:24px;">
            <div style="position:absolute;left:8px;top:0;bottom:0;width:2px;background:#e2e8f0;"></div>

            @foreach($riwayatKerja as $wh)
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
            <div style="position:relative;margin-bottom:16px;">
                <div style="position:absolute;left:-20px;top:16px;width:14px;height:14px;border-radius:50%;background:{{ $c['color'] }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $c['color'] }};"></div>

                <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;background:#fff;">
                    <span style="background:{{ $c['bg'] }};color:{{ $c['color'] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                        <i class="fas {{ $c['icon'] }}"></i> {{ $wh->type }}
                    </span>

                    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:10px;">
                        @if($wh->jabatan)
                        <div>
                            <div style="font-size:10px;color:#a0aec0;font-weight:600;text-transform:uppercase;">Jabatan</div>
                            <div style="font-size:13px;font-weight:700;color:#2d3748;">{{ $wh->jabatan }}</div>
                        </div>
                        @endif
                        @if($wh->departemen)
                        <div>
                            <div style="font-size:10px;color:#a0aec0;font-weight:600;text-transform:uppercase;">Departemen</div>
                            <div style="font-size:13px;font-weight:600;color:#4a5568;">{{ $wh->departemen }}</div>
                        </div>
                        @endif
                        @if($wh->status_karyawan)
                        <div>
                            <div style="font-size:10px;color:#a0aec0;font-weight:600;text-transform:uppercase;">Status</div>
                            <span class="badge badge-{{ $wh->status_karyawan }}">{{ ucfirst($wh->status_karyawan) }}</span>
                        </div>
                        @endif
                    </div>

                    @if($wh->description)
                    <p style="font-size:12px;color:#718096;margin:8px 0 0;">{{ $wh->description }}</p>
                    @endif

                    <div style="font-size:11px;color:#a0aec0;margin-top:10px;">
                        <i class="fas fa-calendar-alt"></i>
                        {{ $wh->start_date->format('d M Y') }}
                        @if($wh->end_date)
                            — {{ $wh->end_date->format('d M Y') }}
                        @else
                            — <span style="color:#38a169;font-weight:600;">Sekarang</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
