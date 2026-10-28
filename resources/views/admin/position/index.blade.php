@extends('layouts.admin')
@section('title', 'Data Jabatan')
@section('page-title', 'Data Jabatan')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-id-badge" style="color:#4299e1;"></i> Daftar Jabatan</h6>
        @if(auth()->user()->hasPermission('departemen.kelola'))
        <button class="btn btn-primary" onclick="openModal('modalTambah')">
            <i class="fas fa-plus"></i> Tambah Jabatan
        </button>
        @endif
    </div>
    <div class="card-body">
        {{-- Search --}}
        <form method="GET" action="{{ route('admin.position.index') }}" style="display:flex; gap:8px; margin-bottom:16px; align-items:center;">
            <div style="position:relative; flex:1; max-width:320px;">
                <i class="fas fa-search" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#a0aec0; font-size:13px;"></i>
                <input type="text" name="search" class="form-control" style="padding-left:34px;"
                       placeholder="Cari jabatan, level, grade..." value="{{ $search ?? '' }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
            @if(request('search'))
            <a href="{{ route('admin.position.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
            @endif
        </form>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jabatan</th>
                        <th>Level</th>
                        <th>Grade Gaji</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $i => $pos)
                    <tr>
                        <td style="color:#94a3b8; font-size:12px;">{{ $positions->firstItem() + $i }}</td>
                        <td><strong>{{ $pos->name }}</strong></td>
                        <td>
                            @if($pos->level)
                            <span style="background:#f5f3ff; color:#7c3aed; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                {{ $pos->level }}
                            </span>
                            @else
                            <span style="color:#cbd5e0;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($pos->salary_grade)
                            <span style="background:#fefce8; color:#ca8a04; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                {{ $pos->salary_grade }}
                            </span>
                            @else
                            <span style="color:#cbd5e0;">-</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if(auth()->user()->hasPermission('departemen.kelola'))
                            <div style="display:flex; gap:5px; justify-content:center;">
                                <button class="btn btn-warning btn-sm"
                                    onclick="openEdit({{ $pos->id }}, '{{ addslashes($pos->name) }}', '{{ addslashes($pos->level ?? '') }}', '{{ addslashes($pos->salary_grade ?? '') }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.position.destroy', $pos) }}"
                                      onsubmit="return confirm('Hapus jabatan {{ addslashes($pos->name) }}?')">
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
                            <i class="fas fa-id-badge" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
                            Belum ada data jabatan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="font-size:12px; color:#94a3b8;">Total: {{ $positions->total() }} jabatan</div>
            <div class="pagination">{{ $positions->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalTambah" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Jabatan</h6>
            <button onclick="closeModal('modalTambah')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.position.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Jabatan <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="Contoh: Staff Keuangan">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Level</label>
                    <input type="text" name="level" class="form-control" placeholder="Contoh: Junior / Senior">
                </div>
                <div class="form-group">
                    <label>Grade Gaji</label>
                    <input type="text" name="salary_grade" class="form-control" placeholder="Contoh: Grade A">
                </div>
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
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Jabatan</h6>
            <button onclick="closeModal('modalEdit')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Jabatan <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="name" id="editName" class="form-control" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Level</label>
                    <input type="text" name="level" id="editLevel" class="form-control">
                </div>
                <div class="form-group">
                    <label>Grade Gaji</label>
                    <input type="text" name="salary_grade" id="editGrade" class="form-control">
                </div>
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
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function openEdit(id, name, level, grade) {
    document.getElementById('editName').value  = name;
    document.getElementById('editLevel').value = level;
    document.getElementById('editGrade').value = grade;
    document.getElementById('formEdit').action = '{{ route("admin.position.update", ":id") }}'.replace(':id', id);
    openModal('modalEdit');
}
document.querySelectorAll('[id^="modal"]').forEach(m => {
    m.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
</script>
@endpush
@endsection

