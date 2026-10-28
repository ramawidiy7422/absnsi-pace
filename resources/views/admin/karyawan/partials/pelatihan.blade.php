@php
    $jenisLabel = \App\Models\Pelatihan::jenisLabel();
    $canKelola  = auth()->user()->hasPermission('karyawan.edit');

    // Hitung ringkasan per jenis
    $grouped = $karyawan->pelatihans->groupBy('jenis');

    // Cek jenis wajib yang harus ada
    $jenisWajib = ['cpob', 'k3', 'hygiene'];
    $statusWajib = [];
    foreach ($jenisWajib as $j) {
        $items = $grouped->get($j, collect());
        $valid = $items->filter(fn($p) => $p->status === 'lulus' && !$p->is_expired)->count();
        $statusWajib[$j] = $valid > 0 ? 'ok' : ($items->count() > 0 ? 'expired' : 'missing');
    }
@endphp

{{-- ── RINGKASAN STATUS WAJIB ─────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px;">
    @foreach($jenisWajib as $j)
    @php
        $st = $statusWajib[$j];
        $cfg = match($st) {
            'ok'      => ['bg'=>'#f0fff4','border'=>'#9ae6b4','icon'=>'fa-check-circle','ic'=>'#38a169','txt'=>'Valid','lbl'=>'#276749'],
            'expired' => ['bg'=>'#fff5f5','border'=>'#feb2b2','icon'=>'fa-times-circle','ic'=>'#e53e3e','txt'=>'Expired / Tidak Lulus','lbl'=>'#c53030'],
            default   => ['bg'=>'#fffff0','border'=>'#f6e05e','icon'=>'fa-exclamation-circle','ic'=>'#d69e2e','txt'=>'Belum Ada','lbl'=>'#b7791f'],
        };
    @endphp
    <div style="background:{{ $cfg['bg'] }};border:1.5px solid {{ $cfg['border'] }};border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
        <i class="fas {{ $cfg['icon'] }}" style="font-size:24px;color:{{ $cfg['ic'] }};flex-shrink:0;"></i>
        <div>
            <div style="font-size:11px;font-weight:700;color:{{ $cfg['lbl'] }};text-transform:uppercase;letter-spacing:0.5px;">{{ $jenisLabel[$j] }}</div>
            <div style="font-size:12px;color:{{ $cfg['lbl'] }};margin-top:2px;">{{ $cfg['txt'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── HEADER + TOMBOL TAMBAH ─────────────────────────────────────────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
    <div>
        <div style="font-size:14px;font-weight:700;color:#2d3748;">Riwayat Pelatihan & Sertifikasi</div>
        <div style="font-size:12px;color:#718096;margin-top:2px;">Total: {{ $karyawan->pelatihans->count() }} entri</div>
    </div>
    @if($canKelola)
    <button onclick="document.getElementById('modal-tambah-pelatihan').style.display='flex'"
            class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Tambah Pelatihan
    </button>
    @endif
</div>

{{-- ── DAFTAR PELATIHAN ────────────────────────────────────────────────────── --}}
@forelse($karyawan->pelatihans as $p)
@php $sc = $p->status_color; @endphp
<div style="border:1px solid #e2e8f0;border-radius:10px;margin-bottom:10px;overflow:hidden;">
    <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 16px;background:white;flex-wrap:wrap;">

        {{-- Ikon jenis --}}
        <div style="width:40px;height:40px;border-radius:10px;background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            @php $icon = match($p->jenis) {
                'cpob'    => 'fa-capsules',
                'k3'      => 'fa-hard-hat',
                'hygiene' => 'fa-soap',
                'sop'     => 'fa-file-alt',
                'external'=> 'fa-building',
                default   => 'fa-certificate',
            }; @endphp
            <i class="fas {{ $icon }}" style="color:{{ $sc['color'] }};font-size:16px;"></i>
        </div>

        {{-- Info utama --}}
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                <span style="font-size:14px;font-weight:700;color:#2d3748;">{{ $p->nama_pelatihan }}</span>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border:1px solid {{ $sc['border'] }};">
                    {{ match($p->status) {
                        'lulus'           => 'Lulus',
                        'tidak_lulus'     => 'Tidak Lulus',
                        'sedang_berjalan' => 'Sedang Berjalan',
                        'belum_ikut'      => 'Belum Ikut',
                    } }}
                </span>
                @if($p->is_expired)
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:#fff5f5;color:#c53030;border:1px solid #fed7d7;"><i class="fas fa-exclamation-triangle"></i> EXPIRED</span>
                @elseif($p->is_near_expired)
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:#fffaf0;color:#c05621;border:1px solid #fbd38d;"><i class="fas fa-clock"></i> Hampir Expired ({{ $p->sisa_hari }} hari)</span>
                @endif
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:12px;color:#718096;">
                @php $tb = $p->tipe_badge; @endphp
                <span style="background:{{ $tb['bg'] }};color:{{ $tb['color'] }};border:1px solid {{ $tb['border'] }};padding:2px 8px;border-radius:10px;font-weight:700;font-size:10px;">
                    <i class="fas {{ $tb['icon'] }}"></i> {{ $p->tipe_label }}
                </span>
                <span><i class="fas fa-tag" style="width:14px;"></i> {{ $jenisLabel[$p->jenis] ?? $p->jenis }}</span>
                @if($p->penyelenggara)
                    <span><i class="fas fa-building" style="width:14px;"></i> {{ $p->penyelenggara }}</span>
                @endif
                <span><i class="fas fa-calendar" style="width:14px;"></i>
                    {{ $p->tanggal_mulai->format('d M Y') }}
                    @if($p->tanggal_selesai) — {{ $p->tanggal_selesai->format('d M Y') }} @endif
                </span>
                @if($p->tanggal_expired)
                    <span style="color:{{ $p->is_expired ? '#c53030' : ($p->is_near_expired ? '#c05621' : '#718096') }};">
                        <i class="fas fa-calendar-times" style="width:14px;"></i>
                        Berlaku s/d {{ $p->tanggal_expired->format('d M Y') }}
                    </span>
                @endif
                @if($p->nomor_sertifikat)
                    <span><i class="fas fa-hashtag" style="width:14px;"></i> {{ $p->nomor_sertifikat }}</span>
                @endif
            </div>

            @if($p->catatan)
            <div style="margin-top:6px;font-size:12px;color:#4a5568;background:#f7fafc;padding:6px 10px;border-radius:6px;border-left:3px solid #cbd5e0;">
                {{ $p->catatan }}
            </div>
            @endif
        </div>

        {{-- Aksi --}}
        @if($canKelola)
        <div style="display:flex;gap:6px;flex-shrink:0;">
            @if($p->lampiran)
            <a href="{{ asset('storage/'.$p->lampiran) }}" target="_blank" class="btn btn-sm btn-secondary" title="Lihat sertifikat">
                <i class="fas fa-file-pdf"></i>
            </a>
            @endif
            <button onclick="bukaPelatihanEdit({{ json_encode($p) }})" class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <form method="POST" action="{{ route('admin.pelatihan.destroy', $p) }}"
                  onsubmit="return confirm('Hapus pelatihan \'{{ $p->nama_pelatihan }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        @endif
    </div>
</div>
@empty
<div style="text-align:center;padding:48px 20px;color:#a0aec0;">
    <i class="fas fa-certificate" style="font-size:40px;display:block;margin-bottom:12px;opacity:0.3;"></i>
    <div style="font-size:14px;font-weight:600;">Belum ada data pelatihan</div>
    <div style="font-size:12px;margin-top:6px;">Klik "Tambah Pelatihan" untuk menambahkan riwayat pelatihan dan sertifikasi karyawan ini.</div>
</div>
@endforelse

{{-- ════ MODAL TAMBAH ════════════════════════════════════════════════════════ --}}
@if($canKelola)
<div id="modal-tambah-pelatihan" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:14px;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:18px 24px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:16px;font-weight:700;color:#2d3748;"><i class="fas fa-certificate" style="color:#4299e1;margin-right:8px;"></i>Tambah Pelatihan</div>
            <button onclick="document.getElementById('modal-tambah-pelatihan').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.pelatihan.store', $karyawan) }}" enctype="multipart/form-data" style="padding:20px 24px;">
            @csrf
            @include('admin.karyawan.partials.pelatihan-form', ['p' => null])
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0;">
                <button type="button" onclick="document.getElementById('modal-tambah-pelatihan').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ════ MODAL EDIT ══════════════════════════════════════════════════════════ --}}
<div id="modal-edit-pelatihan" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:14px;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:18px 24px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:16px;font-weight:700;color:#2d3748;"><i class="fas fa-edit" style="color:#ed8936;margin-right:8px;"></i>Edit Pelatihan</div>
            <button onclick="document.getElementById('modal-edit-pelatihan').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:#718096;">&times;</button>
        </div>
        <form id="form-edit-pelatihan" method="POST" enctype="multipart/form-data" style="padding:20px 24px;">
            @csrf @method('PUT')
            @include('admin.karyawan.partials.pelatihan-form', ['p' => 'edit'])
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0;">
                <button type="button" onclick="document.getElementById('modal-edit-pelatihan').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaPelatihanEdit(p) {
    var f = document.getElementById('form-edit-pelatihan');
    f.action = '{{ route("admin.pelatihan.update", ":id") }}'.replace(':id', p.id);

    // Set tipe (internal/eksternal) via radio button
    var tipe = p.tipe || 'internal';
    f.querySelectorAll('[name=tipe]').forEach(function(r) {
        r.checked = (r.value === tipe);
    });
    // Trigger UI update untuk tipe
    if (typeof onTipeChange === 'function') onTipeChange('edit', tipe);

    // Isi semua field
    f.querySelector('[name=nama_pelatihan]').value   = p.nama_pelatihan || '';
    f.querySelector('[name=jenis]').value            = p.jenis || 'lainnya';
    f.querySelector('[name=penyelenggara]').value    = p.penyelenggara || '';
    f.querySelector('[name=tanggal_mulai]').value    = p.tanggal_mulai ? p.tanggal_mulai.substring(0,10) : '';
    f.querySelector('[name=tanggal_selesai]').value  = p.tanggal_selesai ? p.tanggal_selesai.substring(0,10) : '';
    f.querySelector('[name=tanggal_expired]').value  = p.tanggal_expired ? p.tanggal_expired.substring(0,10) : '';
    f.querySelector('[name=status]').value           = p.status || 'lulus';
    f.querySelector('[name=nomor_sertifikat]').value = p.nomor_sertifikat || '';
    f.querySelector('[name=catatan]').value          = p.catatan || '';

    document.getElementById('modal-edit-pelatihan').style.display = 'flex';
}
// Tutup modal saat klik backdrop
['modal-tambah-pelatihan','modal-edit-pelatihan'].forEach(function(id){
    document.getElementById(id).addEventListener('click', function(e){
        if(e.target === this) this.style.display = 'none';
    });
});
</script>
@endif
