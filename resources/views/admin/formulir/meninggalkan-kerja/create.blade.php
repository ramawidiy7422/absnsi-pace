@extends('layouts.admin')
@section('title', 'Buat Formulir Meninggalkan Kerja')
@section('page-title', 'Formulir Meninggalkan Kerja')

@section('content')
<div style="max-width:800px;margin:0 auto;">

    <div style="margin-bottom:16px;">
        <a href="{{ route('admin.formulir.meninggalkan-kerja.index') }}" style="color:#718096;text-decoration:none;font-size:13px;">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar FMK
        </a>
    </div>

    @if($errors->any())
    <div style="background:#fff5f5;border-left:4px solid #fc8181;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
        <ul style="margin:0;padding-left:16px;color:#c53030;font-size:13px;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.store') }}">
        @csrf

        <div style="background:white;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;">

            {{-- Header surat --}}
            <div style="background:linear-gradient(135deg,#c05621,#dd6b20);padding:16px 28px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="color:white;font-size:17px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;">Formulir Meninggalkan Kerja</div>
                    <div style="color:rgba(255,255,255,0.75);font-size:12px;margin-top:2px;">PT Global Multi Pharmalab</div>
                </div>
                <div style="background:rgba(255,255,255,0.15);padding:8px 16px;border-radius:8px;text-align:right;">
                    <div style="color:rgba(255,255,255,0.65);font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Nomor</div>
                    <div style="color:white;font-family:monospace;font-weight:800;font-size:14px;">{{ $nomorFMK }}</div>
                </div>
            </div>

            <div style="padding:28px 32px;">

                {{-- Pilih Karyawan --}}
                <div style="margin-bottom:22px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:7px;">
                        Pilih Karyawan <span style="color:#c53030;">*</span>
                    </label>
                    <select name="karyawan_id" id="karyawan_id" required
                        style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#2d3748;background:white;"
                        onchange="isiDataKaryawan(this)">
                        <option value="">— Pilih Karyawan —</option>
                        @foreach($karyawan as $k)
                        <option value="{{ $k->id }}"
                            data-nama="{{ $k->nama }}"
                            data-dept="{{ $k->department?->name ?? '-' }}"
                            data-jabatan="{{ $k->position?->name ?? '-' }}"
                            {{ old('karyawan_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nik }} — {{ $k->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('karyawan_id')<div style="color:#c53030;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <hr style="border:none;border-top:1px dashed #e2e8f0;margin:0 0 24px;">

                {{-- Data Pemohon --}}
                <div style="background:#fffbeb;border:1px solid #f6e05e;border-radius:10px;padding:20px 24px;margin-bottom:22px;">
                    <div style="font-size:11px;font-weight:700;color:#975a16;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:14px;">
                        <i class="fas fa-user-circle"></i>&nbsp; Data Pemohon
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Nama</div>
                            <input type="text" id="nama_display" readonly
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;font-weight:600;background:#f8fafc;color:#2d3748;"
                                placeholder="Otomatis dari pilihan">
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Departemen</div>
                            <input type="text" id="dept_display" readonly
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;background:#f8fafc;color:#2d3748;"
                                placeholder="Otomatis dari pilihan">
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Bagian</div>
                            <input type="text" name="bagian" value="{{ old('bagian') }}"
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;color:#2d3748;"
                                placeholder="Bagian / Sub-departemen (opsional)">
                            @error('bagian')<div style="color:#c53030;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Jabatan</div>
                            <input type="text" id="jabatan_display" readonly
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;background:#f8fafc;color:#2d3748;"
                                placeholder="Otomatis dari pilihan">
                        </div>
                    </div>
                </div>

                {{-- Permohonan --}}
                <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:20px 24px;margin-bottom:22px;">
                    <div style="font-size:11px;font-weight:700;color:#276749;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:12px;">
                        <i class="fas fa-file-alt"></i>&nbsp; Permohonan Izin
                    </div>
                    <div style="font-size:14px;color:#2d3748;line-height:1.9;margin-bottom:18px;font-style:italic;">
                        Saya yang bertanda tangan di bawah ini mengajukan permohonan izin meninggalkan kerja pada:
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Hari / Tanggal <span style="color:#c53030;">*</span></div>
                            <input type="date" name="tanggal" required
                                value="{{ old('tanggal', date('Y-m-d')) }}"
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;color:#2d3748;">
                            @error('tanggal')<div style="color:#c53030;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Alasan <span style="color:#c53030;">*</span></div>
                            <select name="alasan" required
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;color:#2d3748;background:white;">
                                <option value="">— Pilih Alasan —</option>
                                @foreach(['sakit'=>'Sakit','urusan_keluarga'=>'Urusan Keluarga','urusan_pribadi'=>'Urusan Pribadi','keperluan_dinas'=>'Keperluan Dinas','lainnya'=>'Lainnya'] as $v=>$l)
                                <option value="{{ $v }}" {{ old('alasan')===$v ? 'selected':'' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            @error('alasan')<div style="color:#c53030;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Jam Keluar <span style="color:#c53030;">*</span></div>
                            <input type="time" name="jam_keluar" required value="{{ old('jam_keluar') }}"
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;color:#2d3748;">
                            @error('jam_keluar')<div style="color:#c53030;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">s/d Jam Kembali</div>
                            <input type="time" name="jam_kembali" value="{{ old('jam_kembali') }}"
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;color:#2d3748;">
                        </div>
                        <div style="grid-column:1/-1;">
                            <div style="font-size:11px;font-weight:600;color:#718096;margin-bottom:5px;">Keterangan Tambahan</div>
                            <textarea name="keterangan" rows="2"
                                style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;color:#2d3748;resize:vertical;"
                                placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Kembali / Tidak Kembali --}}
                <div style="background:#ebf8ff;border:1px solid #90cdf4;border-radius:10px;padding:18px 24px;margin-bottom:22px;">
                    <div style="font-size:11px;font-weight:700;color:#2b6cb0;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:14px;">
                        <i class="fas fa-info-circle"></i>&nbsp; Keterangan
                    </div>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;">
                        <label id="lbl_kembali"
                            style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 20px;border-radius:8px;border:2px solid #9ae6b4;background:#f0fff4;flex:1;min-width:200px;">
                            <input type="radio" name="kembali_bekerja" value="kembali"
                                {{ old('kembali_bekerja','kembali')==='kembali' ? 'checked':'' }}
                                onchange="updateKembaliStyle()"
                                style="width:18px;height:18px;accent-color:#276749;">
                            <div>
                                <div style="font-weight:700;color:#276749;font-size:14px;">Kembali Bekerja</div>
                                <div style="font-size:11px;color:#718096;margin-top:2px;">Karyawan akan kembali ke tempat kerja</div>
                            </div>
                        </label>
                        <label id="lbl_tidak"
                            style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 20px;border-radius:8px;border:2px solid transparent;background:transparent;flex:1;min-width:200px;">
                            <input type="radio" name="kembali_bekerja" value="tidak_kembali"
                                {{ old('kembali_bekerja')==='tidak_kembali' ? 'checked':'' }}
                                onchange="updateKembaliStyle()"
                                style="width:18px;height:18px;accent-color:#c53030;">
                            <div>
                                <div style="font-weight:700;color:#c53030;font-size:14px;">Tidak Kembali</div>
                                <div style="font-size:11px;color:#718096;margin-top:2px;">Tidak kembali ke tempat kerja setelah izin</div>
                            </div>
                        </label>
                    </div>
                    @error('kembali_bekerja')<div style="color:#c53030;font-size:12px;margin-top:8px;">{{ $message }}</div>@enderror
                </div>

                {{-- Tanda Tangan 3 Kolom --}}
                <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:24px;">
                    <div style="background:#f7fafc;padding:11px 18px;border-bottom:1px solid #e2e8f0;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:0.6px;">
                        <i class="fas fa-pen-nib"></i>&nbsp; Tanda Tangan
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;">
                        <div style="padding:18px 16px;text-align:center;border-right:1px solid #e2e8f0;">
                            <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;margin-bottom:10px;">Pemohon / Karyawan</div>
                            <div style="height:60px;border-bottom:1px solid #cbd5e0;margin-bottom:10px;"></div>
                            <input type="text" id="pemohon_display" readonly
                                style="width:100%;text-align:center;border:none;background:transparent;font-size:12px;font-weight:600;color:#2d3748;"
                                placeholder="(Nama karyawan)">
                        </div>
                        <div style="padding:18px 16px;text-align:center;border-right:1px solid #e2e8f0;">
                            <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;margin-bottom:10px;">Menyetujui SPV / Manager</div>
                            <div style="height:60px;border-bottom:1px solid #cbd5e0;margin-bottom:10px;"></div>
                            <input type="text" name="spv_nama" value="{{ old('spv_nama') }}"
                                style="width:100%;text-align:center;border:1px solid #e2e8f0;border-radius:5px;font-size:12px;font-weight:600;color:#2d3748;padding:4px 8px;"
                                placeholder="Nama SPV / Manager">
                            @error('spv_nama')<div style="color:#c53030;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                        </div>
                        <div style="padding:18px 16px;text-align:center;">
                            <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;margin-bottom:10px;">Mengetahui HRD</div>
                            <div style="height:60px;border-bottom:1px solid #cbd5e0;margin-bottom:10px;"></div>
                            <input type="text" name="hrd_nama" value="{{ old('hrd_nama') }}"
                                style="width:100%;text-align:center;border:1px solid #e2e8f0;border-radius:5px;font-size:12px;font-weight:600;color:#2d3748;padding:4px 8px;"
                                placeholder="Nama HRD">
                            @error('hrd_nama')<div style="color:#c53030;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <a href="{{ route('admin.formulir.meninggalkan-kerja.index') }}"
                        style="padding:10px 22px;background:#edf2f7;color:#4a5568;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;">
                        Batal
                    </a>
                    <button type="submit"
                        style="padding:10px 26px;background:linear-gradient(135deg,#c05621,#dd6b20);color:white;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">
                        <i class="fas fa-save"></i>&nbsp; Simpan Formulir
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
const karyawanMap = {
    @foreach($karyawan as $k)
    {{ $k->id }}: { nama: @json($k->nama), dept: @json($k->department?->name ?? '-'), jabatan: @json($k->position?->name ?? '-') },
    @endforeach
};

function isiDataKaryawan(sel) {
    const d = karyawanMap[sel.value] || {};
    document.getElementById('nama_display').value    = d.nama    || '';
    document.getElementById('dept_display').value    = d.dept    || '';
    document.getElementById('jabatan_display').value = d.jabatan || '';
    document.getElementById('pemohon_display').value = d.nama    || '';
}

function updateKembaliStyle() {
    const kembali = document.querySelector('input[value="kembali"]').checked;
    const lK = document.getElementById('lbl_kembali');
    const lT = document.getElementById('lbl_tidak');
    lK.style.borderColor = kembali ? '#9ae6b4' : 'transparent';
    lK.style.background  = kembali ? '#f0fff4'  : 'transparent';
    lT.style.borderColor = !kembali ? '#fc8181' : 'transparent';
    lT.style.background  = !kembali ? '#fff5f5' : 'transparent';
}

window.addEventListener('DOMContentLoaded', () => {
    updateKembaliStyle();
    const sel = document.getElementById('karyawan_id');
    if (sel.value) isiDataKaryawan(sel);
});
</script>
@endsection
