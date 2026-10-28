@extends('layouts.admin')
@section('title', 'Data Departemen')
@section('page-title', 'Data Departemen')

@push('styles')
<style>
.tab-nav {
    display: flex; gap: 4px; border-bottom: 2px solid #e2e8f0;
    margin-bottom: 20px; overflow-x: auto; flex-wrap: nowrap;
}
.tab-btn {
    padding: 10px 16px; border: none; background: none; cursor: pointer;
    font-size: 13px; font-weight: 500; color: #718096;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    white-space: nowrap; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 6px 6px 0 0; text-decoration: none;
}
.tab-btn:hover  { color: #4299e1; background: #f7fafc; }
.tab-btn.active { color: #4299e1; border-bottom-color: #4299e1; background: #ebf8ff; font-weight: 600; }
.tab-content    { display: none; }
.tab-content.active { display: block; }
</style>
@endpush

@section('content')

<div class="tab-nav">
    <button class="tab-btn" data-tab="tab-data-departemen" onclick="switchTab('tab-data-departemen')">
        <i class="fas fa-building"></i> Data Departemen
    </button>
    <button class="tab-btn" data-tab="tab-approval-cuti" onclick="switchTab('tab-approval-cuti')">
        <i class="fas fa-user-check"></i> Approval Cuti & Izin
    </button>
</div>

{{-- ══ TAB: DATA DEPARTEMEN (master data organisasi) ══ --}}
<div class="tab-content" id="tab-data-departemen">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-building" style="color:#4299e1;"></i> Daftar Departemen</h6>
            @if(auth()->user()->hasPermission('departemen.kelola'))
            <button class="btn btn-primary" onclick="openModal('modalTambah')">
                <i class="fas fa-plus"></i> Tambah Departemen
            </button>
            @endif
        </div>
        <div class="card-body">
            <p style="font-size:12.5px;color:#718096;margin:0 0 12px;">
                <i class="fas fa-info-circle"></i> Struktur unit organisasi perusahaan (nama & deskripsi). Untuk atur siapa yang menyetujui cuti/izin per departemen, buka tab <strong>Approval Cuti & Izin</strong>.
            </p>
            {{-- Search --}}
            <form method="GET" action="{{ route('admin.department.index') }}" style="display:flex; gap:8px; align-items:center;">
                <div style="position:relative; flex:1; max-width:320px;">
                    <i class="fas fa-search" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#a0aec0; font-size:13px;"></i>
                    <input type="text" name="search" class="form-control" style="padding-left:34px;"
                           placeholder="Cari departemen..." value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
                @if(request('search'))
                <a href="{{ route('admin.department.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                @endif
            </form>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Departemen</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Divisi</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $i => $dept)
                        <tr>
                            <td style="color:#94a3b8; font-size:12px;">{{ $departments->firstItem() + $i }}</td>
                            <td><strong>{{ $dept->name }}</strong></td>
                            <td style="color:#718096; font-size:13px;">{{ $dept->description ?? '-' }}</td>
                            <td>
                                <span style="background:#eff6ff; color:#3b82f6; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                    {{ $dept->devisions_count }} Divisi
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @if(auth()->user()->hasPermission('departemen.kelola'))
                                <div style="display:flex; gap:5px; justify-content:center;">
                                    <button class="btn btn-warning btn-sm"
                                        onclick="openEdit({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ addslashes($dept->description ?? '') }}', {{ $dept->spv_user_id ?? 'null' }}, {{ $dept->manager_user_id ?? 'null' }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.department.destroy', $dept) }}"
                                          onsubmit="return confirm('Hapus departemen {{ addslashes($dept->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">
                                <i class="fas fa-building" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
                                Belum ada data departemen
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div style="font-size:12px; color:#94a3b8;">Total: {{ $departments->total() }} departemen</div>
                <div class="pagination">{{ $departments->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ══ TAB: APPROVAL CUTI & IZIN ══ --}}
<div class="tab-content" id="tab-approval-cuti">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-user-check" style="color:#48bb78;"></i> Approval Cuti, Izin, Lembur & FMK per Departemen</h6>
        </div>
        <div class="card-body">
            <p style="font-size:12.5px;color:#718096;margin:0;">
                <i class="fas fa-info-circle"></i> Setiap pengajuan cuti/izin/lembur/FMK karyawan disetujui berjenjang: <strong>SPV → Manajer → HRD</strong>, sesuai departemen karyawan tersebut. Kalau SPV atau Manajer belum diisi, tahap itu otomatis dilewati.
            </p>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Departemen</th>
                            <th>SPV (Tahap 1)</th>
                            <th>Manajer (Tahap 2)</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $i => $dept)
                        <tr>
                            <td style="color:#94a3b8; font-size:12px;">{{ $departments->firstItem() + $i }}</td>
                            <td><strong>{{ $dept->name }}</strong></td>
                            <td style="font-size:12.5px;">
                                @if($dept->spvUser)
                                    <span style="color:#276749;">{{ $dept->spvUser->name }}</span>
                                @else
                                    <span style="color:#c05621;"><i class="fas fa-exclamation-triangle"></i> Belum diatur</span>
                                @endif
                            </td>
                            <td style="font-size:12.5px;">
                                @if($dept->managerUser)
                                    <span style="color:#276749;">{{ $dept->managerUser->name }}</span>
                                @else
                                    <span style="color:#c05621;"><i class="fas fa-exclamation-triangle"></i> Belum diatur</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if(auth()->user()->hasPermission('departemen.kelola'))
                                <button class="btn btn-warning btn-sm"
                                    onclick="openEdit({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ addslashes($dept->description ?? '') }}', {{ $dept->spv_user_id ?? 'null' }}, {{ $dept->manager_user_id ?? 'null' }})">
                                    <i class="fas fa-edit"></i> Atur Approval
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">
                                Belum ada data departemen
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div style="font-size:12px; color:#94a3b8;">Total: {{ $departments->total() }} departemen</div>
                <div class="pagination">{{ $departments->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalTambah" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Departemen</h6>
            <button onclick="closeModal('modalTambah')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.department.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Departemen <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="Contoh: Human Resources">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat departemen..."></textarea>
            </div>
            <hr style="margin:16px 0;border:none;border-top:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:700;color:#48bb78;margin-bottom:10px;text-transform:uppercase;">
                <i class="fas fa-user-check"></i> Approval Cuti & Izin (opsional, bisa diatur nanti)
            </div>
            <div class="form-group">
                <label>SPV (Approval Tahap 1)</label>
                <select name="spv_user_id" class="form-select">
                    <option value="">-- Belum diisi --</option>
                    @foreach($calonApprover as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Manajer (Approval Tahap 2)</label>
                <select name="manager_user_id" class="form-select">
                    <option value="">-- Belum diisi --</option>
                    @foreach($calonApprover as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEdit" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Departemen</h6>
            <button onclick="closeModal('modalEdit')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Departemen <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="name" id="editName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" id="editDesc" class="form-control" rows="3"></textarea>
            </div>
            <hr style="margin:16px 0;border:none;border-top:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:700;color:#48bb78;margin-bottom:10px;text-transform:uppercase;">
                <i class="fas fa-user-check"></i> Approval Cuti & Izin
            </div>
            <div class="form-group">
                <label>SPV (Approval Tahap 1)</label>
                <select name="spv_user_id" id="editSpv" class="form-select">
                    <option value="">-- Belum diisi --</option>
                    @foreach($calonApprover as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Manajer (Approval Tahap 2)</label>
                <select name="manager_user_id" id="editManager" class="form-select">
                    <option value="">-- Belum diisi --</option>
                    @foreach($calonApprover as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) target.classList.add('active');
    document.querySelectorAll('.tab-btn[data-tab="' + tabId + '"]').forEach(btn => btn.classList.add('active'));
    history.replaceState(null, null, '#' + tabId);
}

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function openEdit(id, name, desc, spvId, managerId) {
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value  = desc;
    document.getElementById('editSpv').value     = spvId ?? '';
    document.getElementById('editManager').value = managerId ?? '';
    document.getElementById('formEdit').action = '{{ route("admin.department.update", ":id") }}'.replace(':id', id);
    openModal('modalEdit');
}
// Close on backdrop click
document.querySelectorAll('[id^="modal"]').forEach(m => {
    m.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

(function() {
    const validTabs = ['tab-data-departemen', 'tab-approval-cuti'];
    const hash = window.location.hash.replace('#', '');
    switchTab(validTabs.includes(hash) ? hash : 'tab-data-departemen');
})();
</script>
@endpush
@endsection
