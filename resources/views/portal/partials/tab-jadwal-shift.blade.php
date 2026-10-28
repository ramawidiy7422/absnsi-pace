<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-calendar-week" style="color:#4299e1;"></i> Jadwal Shift — {{ $startShift->translatedFormat('F Y') }}</h6>
        <form method="GET" action="{{ route('portal.profil-lengkap') }}#tab-jadwal-shift" style="display:flex;gap:8px;">
            <select name="bulan" class="form-select" style="padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $bulanShift == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
                </option>
                @endforeach
            </select>
            <select name="tahun" class="form-select" style="padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                @foreach(range(now()->year, now()->year - 2) as $y)
                <option value="{{ $y }}" {{ $tahunShift == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:10px 16px;text-align:left;">Tanggal</th>
                        <th style="padding:10px 12px;text-align:left;">Shift</th>
                        <th style="padding:10px 12px;">Jam Masuk</th>
                        <th style="padding:10px 12px;">Jam Keluar</th>
                        <th style="padding:10px 12px;text-align:left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwalShift as $j)
                    @php $isPast = $j['tanggal']->isPast() && !$j['tanggal']->isToday(); @endphp
                    <tr style="border-bottom:1px solid #f0f0f0;{{ $j['tanggal']->isToday() ? 'background:#ebf8ff;' : '' }}">
                        <td style="padding:10px 16px;font-weight:600;color:{{ $isPast ? '#a0aec0' : '#2d3748' }};">
                            {{ $j['tanggal']->translatedFormat('d M Y') }}
                            <span style="font-size:11px;color:#a0aec0;">({{ $j['tanggal']->translatedFormat('l') }})</span>
                            @if($j['tanggal']->isToday())
                            <span style="background:#4299e1;color:white;font-size:10px;padding:1px 6px;border-radius:8px;margin-left:4px;">Hari ini</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;">
                            @if($j['isLibur'])
                            <span style="background:#c6f6d5;color:#276749;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                                <i class="fas fa-umbrella-beach"></i> Libur — {{ $j['namaLibur'] }}
                            </span>
                            @elseif($j['shift'])
                            <span style="background:#eff6ff;color:#3b82f6;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                {{ $j['shift']->nama_shift }}
                            </span>
                            @if($j['isCustom'])
                            <span style="font-size:10px;color:#805ad5;margin-left:4px;">(khusus)</span>
                            @endif
                            @else
                            <span style="color:#a0aec0;">Tidak ada shift</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:#718096;">
                            {{ $j['shift'] && !$j['isLibur'] ? substr($j['shift']->jam_masuk,0,5) : '-' }}
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:#718096;">
                            {{ $j['shift'] && !$j['isLibur'] ? substr($j['shift']->jam_keluar,0,5) : '-' }}
                        </td>
                        <td style="padding:10px 12px;color:#a0aec0;font-size:12px;">{{ $j['keterangan'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
