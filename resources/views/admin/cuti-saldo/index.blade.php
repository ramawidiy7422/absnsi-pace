@extends('layouts.admin')
@section('title', 'Kelola Saldo Cuti')
@section('page-title', 'Kelola Saldo Cuti Karyawan')

@section('content')


{{-- Bulk Set Jatah --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h6><i class="fas fa-layer-group" style="color:#805ad5;"></i> Set Jatah Cuti Massal</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cuti-saldo.bulk') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            @csrf
            <div class="form-group" style="margin:0;min-width:100px;">
                <label style="font-size:12px;">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2020" max="2030" required style="width:100px;">
            </div>
            <div class="form-group" style="margin:0;min-width:120px;">
                <label style="font-size:12px;">Jatah (hari)</label>
                <input type="number" name="jatah" class="form-control" value="{{ $jatahDefault }}" min="0" max="365" required style="width:120px;">
            </div>
            <div class="form-group" style="margin:0;min-width:180px;">
                <label style="font-size:12px;">Departemen <small style="color:#a0aec0;">(kosong = semua)</small></label>
                <select name="department_id" class="form-select">
                    <option value="">— Semua Departemen —</option>
                    @foreach($deptList as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Set jatah cuti massal untuk semua karyawan terpilih?')">
                <i class="fas fa-magic"></i> Apply ke Semua
            </button>
        </form>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="form-group" style="margin:0;min-width:100px;">
                <label style="font-size:12px;">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2020" max="2030" style="width:100px;">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                <label style="font-size:12px;">Cari Karyawan</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nama atau ID Karyawan...">
            </div>
            <div class="form-group" style="margin:0;min-width:180px;">
                <label style="font-size:12px;">Departemen</label>
                <select name="department_id" class="form-select">
                    <option value="">— Semua —</option>
                    @foreach($deptList as $dept)
                        <option value="{{ $dept->id }}" {{ $department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.cuti-saldo.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

{{-- Tabel Saldo Cuti --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-umbrella-beach" style="color:#38a169;"></i> Saldo Cuti Tahun {{ $tahun }}</h6>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:12px;color:#a0aec0;">{{ $karyawan->count() }} karyawan</span>
            @if(auth()->user()->hasPermission('laporan.export'))
            <a href="{{ route('admin.cuti-saldo.export-pdf', request()->query()) }}"
               style="padding:5px 12px;background:#c53030;color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.cuti-saldo.export-csv', request()->query()) }}"
               style="padding:5px 12px;background:#2f855a;color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            @endif
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th style="text-align:center;">Jatah</th>
                        <th style="text-align:center;">Carry-Over</th>
                        <th style="text-align:center;">Terpakai</th>
                        <th style="text-align:center;">Sisa</th>
                        <th style="text-align:center;">Progress</th>
                        <th style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawan as $i => $k)
                    @php
                        $saldo      = $saldoMap[$k->id] ?? null;
                        $jatahHitung= \App\Models\CutiSaldo::hitungJatah($k->id, $tahun);
                        $jatah      = $saldo ? $saldo->jatah : $jatahHitung;
                        $carryOver  = $saldo ? ($saldo->carry_over ?? 0) : 0;
                        $terpakai   = $saldo ? $saldo->terpakai : 0;
                        $sisa       = max(0, $jatah + $carryOver - $terpakai);
                        $totalJatah = $jatah + $carryOver;
                        $pct        = $totalJatah > 0 ? min(100, round($terpakai / $totalJatah * 100)) : 0;
                        $barColor   = $pct >= 90 ? '#e53e3e' : ($pct >= 70 ? '#ed8936' : '#38a169');
                        // Cek masa kerja per 1 Jan tahun bersangkutan
                        $acuan      = \Carbon\Carbon::createFromDate($tahun, 1, 1);
                        $bulanKerja = $k->tanggal_masuk ? $k->tanggal_masuk->diffInMonths($acuan) : 999;
                        $belumBerhak= $jatahHitung === 0;
                        $tglBerhak  = $k->tanggal_masuk ? $k->tanggal_masuk->copy()->addYear() : null;
                    @endphp
                    <tr style="{{ $belumBerhak ? 'background:#fffaf0;' : '' }}">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div style="font-weight:600;color:#2d3748;">{{ $k->nama }}</div>
                            <small style="color:#a0aec0;">{{ $k->nik }}</small>
                            @if($k->tanggal_masuk)
                            <div style="font-size:10px;color:#a0aec0;margin-top:2px;">
                                Masuk: {{ $k->tanggal_masuk->format('d/m/Y') }} · {{ $k->masa_kerja }}
                            </div>
                            @endif
                        </td>
                        <td style="color:#718096;font-size:13px;">{{ $k->department?->name ?? $k->departemen ?? '-' }}</td>
                        <td style="text-align:center;">
                            @if($belumBerhak)
                                <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:8px;font-size:10px;font-weight:700;">
                                    Belum Berhak
                                </span>
                                @if($tglBerhak)
                                <div style="font-size:10px;color:#a0aec0;margin-top:2px;">Per {{ $tglBerhak->format('d/m/Y') }}</div>
                                @endif
                            @else
                                <span style="font-weight:700;color:#2d3748;">{{ $jatah }}</span>
                                <small style="color:#a0aec0;">hari</small>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($carryOver > 0)
                            <span style="font-weight:700;color:#7c3aed;">+{{ $carryOver }}</span>
                            <small style="color:#a0aec0;">hari</small>
                            @else
                            <span style="color:#cbd5e0;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <span style="font-weight:700;color:#e53e3e;">{{ $terpakai }}</span>
                            <small style="color:#a0aec0;">hari</small>
                        </td>
                        <td style="text-align:center;">
                            <span style="font-weight:700;color:{{ $sisa < 0 ? '#c53030' : ($sisa <= 3 ? '#c05621' : '#276749') }};">
                                {{ $sisa }}
                            </span>
                            <small style="color:#a0aec0;">hari</small>
                        </td>
                        <td style="min-width:120px;">
                            <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;background:{{ $barColor }};height:100%;border-radius:99px;transition:width 0.3s;"></div>
                            </div>
                            <div style="font-size:11px;color:#a0aec0;text-align:center;margin-top:2px;">{{ $pct }}%</div>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick='openEditSaldo({{ $k->id }}, "{{ addslashes($k->nama) }}", {{ $jatah }}, {{ $terpakai }})' title="Edit Saldo">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-umbrella-beach" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                            Tidak ada karyawan ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Edit Saldo --}}
<div id="modalEditSaldo" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;width:400px;max-width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:18px 22px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <h6 style="margin:0;font-weight:700;color:#2d3748;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Saldo Cuti</h6>
            <button onclick="closeEditSaldo()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <form method="POST" id="formEditSaldo" style="padding:22px;">
            @csrf @method('PUT')
            <p style="color:#718096;margin-bottom:16px;font-size:14px;">Karyawan: <strong id="saldoNama"></strong></p>
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <div class="grid-2">
                <div class="form-group">
                    <label>Jatah Cuti (hari)</label>
                    <input type="number" name="jatah" id="saldoJatah" class="form-control" min="0" max="365" required>
                </div>
                <div class="form-group">
                    <label>Terpakai (hari)</label>
                    <input type="number" name="terpakai" id="saldoTerpakai" class="form-control" min="0" required>
                </div>
            </div>
            <div style="background:#ebf8ff;border:1px solid #bee3f8;border-radius:8px;padding:10px;margin-bottom:16px;font-size:13px;color:#2b6cb0;">
                <i class="fas fa-info-circle"></i> Sisa cuti dihitung otomatis: Jatah − Terpakai
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeEditSaldo()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function openEditSaldo(karyawanId, nama, jatah, terpakai) {
    document.getElementById('saldoNama').textContent    = nama;
    document.getElementById('saldoJatah').value         = jatah;
    document.getElementById('saldoTerpakai').value      = terpakai;
    document.getElementById('formEditSaldo').action     = '/admin/cuti-saldo/' + karyawanId;
    document.getElementById('modalEditSaldo').style.display = 'flex';
}
function closeEditSaldo() {
    document.getElementById('modalEditSaldo').style.display = 'none';
}
document.getElementById('modalEditSaldo').addEventListener('click', function(e) {
    if (e.target === this) closeEditSaldo();
});
</script>
@endpush
@endsection

