@extends('layouts.admin')
@section('title','Hari Libur & Kerja Pengganti')
@section('page-title','Hari Libur & Kerja Pengganti')

@section('content')
@php
    $nasional    = $libur->where('jenis','nasional')->count();
    $cutBers     = $libur->where('jenis','cuti_bersama')->count();
    $internal    = $libur->where('jenis','internal')->count();
    $kerjaPeng   = $libur->where('jenis','kerja_pengganti')->count();
    $jenisColor  = [
        'nasional'        => ['bg'=>'#fee2e2','color'=>'#dc2626','icon'=>'fas fa-flag'],
        'cuti_bersama'    => ['bg'=>'#ede9fe','color'=>'#7c3aed','icon'=>'fas fa-umbrella-beach'],
        'internal'        => ['bg'=>'#dbeafe','color'=>'#2563eb','icon'=>'fas fa-building'],
        'kerja_pengganti' => ['bg'=>'#fef3c7','color'=>'#d97706','icon'=>'fas fa-exchange-alt'],
    ];
@endphp

{{-- Stat Bar --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:18px;">
    <div style="background:var(--bg-card,#fff);border-radius:12px;padding:14px 16px;border:1px solid var(--border,#e2e8f0);display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:18px;color:#dc2626;">
            <i class="fas fa-calendar-times"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-main,#1e293b);">{{ $libur->count() }}</div>
            <div style="font-size:11px;color:#94a3b8;">Total {{ $tahun }}</div>
        </div>
    </div>
    <div style="background:var(--bg-card,#fff);border-radius:12px;padding:14px 16px;border:1px solid var(--border,#e2e8f0);display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:16px;color:#dc2626;"><i class="fas fa-flag"></i></div>
        <div><div style="font-size:20px;font-weight:800;color:#dc2626;">{{ $nasional }}</div><div style="font-size:11px;color:#94a3b8;">Libur Nasional</div></div>
    </div>
    <div style="background:var(--bg-card,#fff);border-radius:12px;padding:14px 16px;border:1px solid var(--border,#e2e8f0);display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#ede9fe;display:flex;align-items:center;justify-content:center;font-size:16px;color:#7c3aed;"><i class="fas fa-umbrella-beach"></i></div>
        <div><div style="font-size:20px;font-weight:800;color:#7c3aed;">{{ $cutBers }}</div><div style="font-size:11px;color:#94a3b8;">Cuti Bersama</div></div>
    </div>
    <div style="background:var(--bg-card,#fff);border-radius:12px;padding:14px 16px;border:1px solid var(--border,#e2e8f0);display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:16px;color:#2563eb;"><i class="fas fa-building"></i></div>
        <div><div style="font-size:20px;font-weight:800;color:#2563eb;">{{ $internal }}</div><div style="font-size:11px;color:#94a3b8;">Libur Internal</div></div>
    </div>
    <div style="background:var(--bg-card,#fff);border-radius:12px;padding:14px 16px;border:1px solid var(--border,#e2e8f0);display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:16px;color:#d97706;"><i class="fas fa-exchange-alt"></i></div>
        <div><div style="font-size:20px;font-weight:800;color:#d97706;">{{ $kerjaPeng }}</div><div style="font-size:11px;color:#94a3b8;">Kerja Pengganti</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap;gap:10px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-calendar-alt" style="color:#e53e3e;"></i> Daftar Hari Libur & Kerja Pengganti</h6>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:6px;">
                <select name="tahun" class="form-select" style="width:auto;height:36px;font-size:13px;" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t==$tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
            <form method="POST" action="{{ route('admin.hari-libur.import') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button type="submit" class="btn btn-secondary btn-sm"
                        onclick="return confirm('Import hari libur nasional {{ $tahun }}?')">
                    <i class="fas fa-download"></i> Import {{ $tahun }}
                </button>
            </form>
            <button class="btn btn-warning btn-sm" onclick="document.getElementById('modalKerja').style.display='flex'">
                <i class="fas fa-exchange-alt"></i> Kerja Pengganti
            </button>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalTambah').style.display='flex'">
                <i class="fas fa-plus"></i> Tambah Libur
            </button>
        </div>
    </div>

    <div class="card-body" style="padding:0;">
        {{-- Info kerja pengganti --}}
        @if($kerjaPeng > 0)
        <div style="margin:16px;padding:12px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:13px;color:#92400e;">
            <i class="fas fa-info-circle" style="color:#d97706;"></i>
            <strong>{{ $kerjaPeng }} Hari Kerja Pengganti</strong> — Sabtu masuk kerja, libur dipindah ke hari lain.
            Karyawan yang scan di hari kerja pengganti tetap tercatat hadir.
        </div>
        @endif

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:600px;">
                <thead>
                    <tr style="background:var(--bg-thead,#f7fafc);border-bottom:2px solid var(--border,#e2e8f0);">
                        <th style="padding:10px 14px;text-align:left;font-weight:700;color:#64748b;font-size:11px;text-transform:uppercase;">#</th>
                        <th style="padding:10px 14px;text-align:left;font-weight:700;color:#64748b;font-size:11px;text-transform:uppercase;">Tanggal</th>
                        <th style="padding:10px 14px;text-align:left;font-weight:700;color:#64748b;font-size:11px;text-transform:uppercase;">Hari</th>
                        <th style="padding:10px 14px;text-align:left;font-weight:700;color:#64748b;font-size:11px;text-transform:uppercase;">Keterangan</th>
                        <th style="padding:10px 14px;text-align:left;font-weight:700;color:#64748b;font-size:11px;text-transform:uppercase;">Jenis</th>
                        <th style="padding:10px 14px;text-align:center;font-weight:700;color:#64748b;font-size:11px;text-transform:uppercase;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($libur as $i => $hl)
                    @php
                        $c = $jenisColor[$hl->jenis] ?? $jenisColor['internal'];
                        $isWeekend = $hl->tanggal->isWeekend();
                        $isKP = $hl->jenis === 'kerja_pengganti';
                    @endphp
                    <tr style="border-bottom:1px solid var(--border,#f0f0f0);{{ $isKP ? 'background:#fffdf0;' : ($isWeekend ? 'background:#fef9f0;' : '') }}">
                        <td style="padding:10px 14px;color:#94a3b8;font-size:12px;">{{ $i+1 }}</td>
                        <td style="padding:10px 14px;">
                            <div style="font-weight:700;color:var(--text-main,#1e293b);">{{ $hl->tanggal->format('d M Y') }}</div>
                            @if($isKP && $hl->tanggal_libur_pengganti)
                                <div style="font-size:11px;color:#d97706;margin-top:2px;">
                                    <i class="fas fa-arrow-right"></i> Libur pengganti: {{ $hl->tanggal_libur_pengganti->format('d M Y') }}
                                </div>
                            @endif
                        </td>
                        <td style="padding:10px 14px;color:#64748b;">
                            {{ $hl->tanggal->translatedFormat('l') }}
                            @if($isWeekend && !$isKP)
                                <span style="font-size:10px;background:#fef3c7;color:#d97706;padding:1px 6px;border-radius:4px;margin-left:4px;">Weekend</span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;font-weight:600;color:var(--text-main,#2d3748);">{{ $hl->nama }}</td>
                        <td style="padding:10px 14px;">
                            <span style="background:{{ $c['bg'] }};color:{{ $c['color'] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:5px;">
                                <i class="{{ $c['icon'] }}" style="font-size:10px;"></i>
                                {{ $hl->jenis_label }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;text-align:center;">
                            <div style="display:flex;gap:5px;justify-content:center;">
                                <button class="btn btn-warning btn-sm"
                                    onclick="openEdit({{ $hl->id }},'{{ $hl->tanggal->format('Y-m-d') }}','{{ addslashes($hl->nama) }}','{{ $hl->jenis }}','{{ $hl->tanggal_libur_pengganti?->format('Y-m-d') }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.hari-libur.destroy', $hl) }}"
                                      onsubmit="return confirm('Hapus: {{ addslashes($hl->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:50px;color:#94a3b8;">
                            <i class="fas fa-calendar-times" style="font-size:40px;opacity:0.2;display:block;margin-bottom:12px;"></i>
                            Belum ada data hari libur untuk tahun {{ $tahun }}.<br>
                            <small>Gunakan tombol "Import {{ $tahun }}" untuk mengisi otomatis.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Libur Biasa --}}
<div id="modalTambah" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:1000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:var(--bg-card,#fff);border-radius:14px;width:100%;max-width:460px;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 style="font-weight:700;margin:0;color:var(--text-main,#1e293b);"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Hari Libur</h6>
            <button onclick="document.getElementById('modalTambah').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.hari-libur.store') }}">
            @csrf
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Tanggal *</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Nama Hari Libur *</label>
                <input type="text" name="nama" class="form-control" required placeholder="Contoh: Hari Raya Idul Fitri">
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Jenis</label>
                <select name="jenis" class="form-select">
                    <option value="nasional">🔴 Libur Nasional</option>
                    <option value="cuti_bersama">🟣 Cuti Bersama</option>
                    <option value="internal">🔵 Libur Internal Perusahaan</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalTambah').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Kerja Pengganti (Sabtu Masuk) --}}
<div id="modalKerja" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:1000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:var(--bg-card,#fff);border-radius:14px;width:100%;max-width:500px;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h6 style="font-weight:700;margin:0;color:var(--text-main,#1e293b);">
                <i class="fas fa-exchange-alt" style="color:#d97706;"></i> Atur Kerja Pengganti
            </h6>
            <button onclick="document.getElementById('modalKerja').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px;margin-bottom:16px;font-size:12.5px;color:#92400e;">
            <i class="fas fa-lightbulb" style="color:#d97706;"></i>
            <strong>Kerja Pengganti</strong> = Sabtu (atau hari lain) masuk kerja, sebagai pengganti hari libur yang digeser.<br>
            Contoh: <em>Sabtu 21 Mar masuk kerja</em> → libur pengganti jatuh di <em>Senin 23 Mar</em>.
        </div>
        <form method="POST" action="{{ route('admin.hari-libur.store') }}">
            @csrf
            <input type="hidden" name="jenis" value="kerja_pengganti">
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Tanggal Kerja Pengganti *
                    <small style="font-weight:400;color:#94a3b8;">(hari Sabtu/libur yang masuk kerja)</small>
                </label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Tanggal Libur Pengganti
                    <small style="font-weight:400;color:#94a3b8;">(hari yang jadi libur sebagai gantinya)</small>
                </label>
                <input type="date" name="tanggal_libur_pengganti" class="form-control">
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Keterangan *</label>
                <input type="text" name="nama" class="form-control" required
                       placeholder="Contoh: Kerja Pengganti Libur Idulfitri">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalKerja').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-warning" style="color:white;"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEdit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:1000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:var(--bg-card,#fff);border-radius:14px;width:100%;max-width:480px;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 style="font-weight:700;margin:0;color:var(--text-main,#1e293b);"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Hari Libur</h6>
            <button onclick="document.getElementById('modalEdit').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Tanggal *</label>
                <input type="date" name="tanggal" id="editTanggal" class="form-control" required>
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Nama / Keterangan *</label>
                <input type="text" name="nama" id="editNama" class="form-control" required>
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Jenis</label>
                <select name="jenis" id="editJenis" class="form-select" onchange="toggleLiburPengganti(this.value)">
                    <option value="nasional">🔴 Libur Nasional</option>
                    <option value="cuti_bersama">🟣 Cuti Bersama</option>
                    <option value="internal">🔵 Libur Internal Perusahaan</option>
                    <option value="kerja_pengganti">🟡 Kerja Pengganti</option>
                </select>
            </div>
            <div class="form-group" id="editLiburPenggantiWrap" style="display:none;">
                <label style="font-size:12px;font-weight:700;color:#64748b;">Tanggal Libur Pengganti</label>
                <input type="date" name="tanggal_libur_pengganti" id="editLiburPengganti" class="form-control">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, tanggal, nama, jenis, tglPengganti) {
    document.getElementById('editTanggal').value        = tanggal;
    document.getElementById('editNama').value           = nama;
    document.getElementById('editJenis').value          = jenis;
    document.getElementById('editLiburPengganti').value = tglPengganti || '';
    document.getElementById('formEdit').action          = '/admin/hari-libur/' + id;
    toggleLiburPengganti(jenis);
    document.getElementById('modalEdit').style.display = 'flex';
}
function toggleLiburPengganti(jenis) {
    document.getElementById('editLiburPenggantiWrap').style.display =
        jenis === 'kerja_pengganti' ? 'block' : 'none';
}
// Tutup modal klik luar
['modalTambah','modalEdit','modalKerja'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>
@endsection
