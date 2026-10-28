@php
    $jenisLabel = \App\Models\Pelatihan::jenisLabel();
    $formId     = isset($p) && $p === 'edit' ? 'edit' : 'tambah';
@endphp

{{-- Baris 1: Tipe & Jenis --}}
<div class="form-row" style="margin-bottom:14px;">
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">
            Tipe Pelatihan <span style="color:#e53e3e;">*</span>
        </label>
        <div style="display:flex;gap:0;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;" id="tipe-toggle-{{ $formId }}">
            <label style="flex:1;margin:0;cursor:pointer;">
                <input type="radio" name="tipe" value="internal" style="display:none;"
                       onchange="onTipeChange('{{ $formId }}', 'internal')" checked>
                <div id="btn-internal-{{ $formId }}"
                     style="text-align:center;padding:8px 12px;font-size:12px;font-weight:700;background:#f0fff4;color:#276749;border-right:1px solid #e2e8f0;transition:all .15s;">
                    <i class="fas fa-home" style="margin-right:4px;"></i> Internal
                </div>
            </label>
            <label style="flex:1;margin:0;cursor:pointer;">
                <input type="radio" name="tipe" value="eksternal" style="display:none;"
                       onchange="onTipeChange('{{ $formId }}', 'eksternal')">
                <div id="btn-eksternal-{{ $formId }}"
                     style="text-align:center;padding:8px 12px;font-size:12px;font-weight:700;background:white;color:#718096;transition:all .15s;">
                    <i class="fas fa-globe" style="margin-right:4px;"></i> Eksternal
                </div>
            </label>
        </div>
        {{-- Info kontekstual berdasar tipe --}}
        <div id="info-internal-{{ $formId }}" style="margin-top:6px;font-size:11px;color:#276749;background:#f0fff4;padding:6px 10px;border-radius:6px;border-left:3px solid #9ae6b4;">
            <i class="fas fa-info-circle"></i> Pelatihan yang diselenggarakan secara mandiri oleh perusahaan (in-house training, briefing internal, dll).
        </div>
        <div id="info-eksternal-{{ $formId }}" style="display:none;margin-top:6px;font-size:11px;color:#2b6cb0;background:#ebf8ff;padding:6px 10px;border-radius:6px;border-left:3px solid #90cdf4;">
            <i class="fas fa-info-circle"></i> Pelatihan dari lembaga luar: BNSP, BPOM, K3 Kemenaker, vendor, konsultan, dll. Wajib isi penyelenggara.
        </div>
    </div>
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">Materi / Bidang <span style="color:#e53e3e;">*</span></label>
        <select name="jenis" class="form-select" required>
            @foreach($jenisLabel as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>
        <div style="font-size:11px;color:#a0aec0;margin-top:4px;">Topik atau bidang pelatihan</div>
    </div>
</div>

{{-- Baris 2: Nama Pelatihan --}}
<div class="form-group" style="margin-bottom:14px;">
    <label style="font-size:12px;font-weight:700;color:#4a5568;">Nama Pelatihan <span style="color:#e53e3e;">*</span></label>
    <input type="text" name="nama_pelatihan" class="form-control"
           placeholder="Contoh: Sertifikasi K3 Umum BNSP 2024 / CPOB Dasar Internal"
           value="{{ old('nama_pelatihan') }}" required>
</div>

{{-- Baris 3: Penyelenggara --}}
<div class="form-group" style="margin-bottom:14px;">
    <label style="font-size:12px;font-weight:700;color:#4a5568;" id="label-penyelenggara-{{ $formId }}">
        Penyelenggara
    </label>
    <input type="text" name="penyelenggara" class="form-control" id="input-penyelenggara-{{ $formId }}"
           placeholder="Contoh: BNSP, BPOM, Kemenaker, Internal HRD...">
    <div id="hint-penyelenggara-{{ $formId }}" style="display:none;font-size:11px;color:#2b6cb0;margin-top:4px;">
        <i class="fas fa-lightbulb"></i> Contoh lembaga eksternal: BNSP, BPOM, Kemenaker, Sucofindo, SGS, vendor training, konsultan GMP
    </div>
</div>

{{-- Baris 4: Tanggal Mulai & Selesai --}}
<div class="form-row" style="margin-bottom:14px;">
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">Tanggal Mulai <span style="color:#e53e3e;">*</span></label>
        <input type="date" name="tanggal_mulai" class="form-control" required>
    </div>
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">Tanggal Selesai</label>
        <input type="date" name="tanggal_selesai" class="form-control">
    </div>
</div>

{{-- Baris 5: Berlaku Sampai & Status --}}
<div class="form-row" style="margin-bottom:14px;">
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">
            Berlaku Sampai
            <span style="font-weight:400;color:#a0aec0;">(kosongkan jika tidak ada masa berlaku)</span>
        </label>
        <input type="date" name="tanggal_expired" class="form-control">
    </div>
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">Status <span style="color:#e53e3e;">*</span></label>
        <select name="status" class="form-select" required>
            <option value="lulus">Lulus</option>
            <option value="tidak_lulus">Tidak Lulus</option>
            <option value="sedang_berjalan">Sedang Berjalan</option>
            <option value="belum_ikut">Belum Ikut</option>
        </select>
    </div>
</div>

{{-- Baris 6: Nomor Sertifikat & Upload --}}
<div class="form-row" style="margin-bottom:14px;">
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">Nomor Sertifikat</label>
        <input type="text" name="nomor_sertifikat" class="form-control" placeholder="Opsional">
    </div>
    <div class="form-group" style="margin-bottom:0;">
        <label style="font-size:12px;font-weight:700;color:#4a5568;">Upload Sertifikat (PDF/JPG)</label>
        <input type="file" name="lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="padding:6px;">
    </div>
</div>

{{-- Catatan --}}
<div class="form-group" style="margin-bottom:0;">
    <label style="font-size:12px;font-weight:700;color:#4a5568;">Catatan</label>
    <textarea name="catatan" class="form-control" rows="2" placeholder="Keterangan tambahan..." style="resize:vertical;"></textarea>
</div>

<script>
function onTipeChange(formId, tipe) {
    var isEksternal = tipe === 'eksternal';

    // Toggle button style
    var btnInt = document.getElementById('btn-internal-' + formId);
    var btnExt = document.getElementById('btn-eksternal-' + formId);
    if (btnInt && btnExt) {
        btnInt.style.background  = isEksternal ? 'white'   : '#f0fff4';
        btnInt.style.color       = isEksternal ? '#718096' : '#276749';
        btnExt.style.background  = isEksternal ? '#ebf8ff' : 'white';
        btnExt.style.color       = isEksternal ? '#2b6cb0' : '#718096';
    }

    // Toggle info box
    var infoInt = document.getElementById('info-internal-' + formId);
    var infoExt = document.getElementById('info-eksternal-' + formId);
    if (infoInt) infoInt.style.display = isEksternal ? 'none' : 'block';
    if (infoExt) infoExt.style.display = isEksternal ? 'block' : 'none';

    // Toggle penyelenggara hint
    var hint  = document.getElementById('hint-penyelenggara-' + formId);
    var label = document.getElementById('label-penyelenggara-' + formId);
    var input = document.getElementById('input-penyelenggara-' + formId);
    if (hint)  hint.style.display = isEksternal ? 'block' : 'none';
    if (label) label.innerHTML = isEksternal
        ? 'Lembaga Penyelenggara <span style="color:#e53e3e;">*</span>'
        : 'Penyelenggara';
    if (input) {
        input.required    = isEksternal;
        input.placeholder = isEksternal
            ? 'Contoh: BNSP, BPOM, Kemenaker, PT Training ABC...'
            : 'Contoh: HRD PT Global Multi Pharmalab';
    }
}
</script>
