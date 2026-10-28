@extends('layouts.admin')
@section('title', 'Data Divisi')
@section('page-title', 'Data Divisi')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-sitemap" style="color:#4299e1;"></i> Daftar Divisi</h6>
        @if(auth()->user()->hasPermission('departemen.kelola'))
        <button class="btn btn-primary" onclick="openModal('modalTambah')">
            <i class="fas fa-plus"></i> Tambah Divisi
        </button>
        @endif
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Divisi</th>
                        <th>Departemen</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devisions as $i => $dev)
                    <tr>
                        <td style="color:#94a3b8; font-size:12px;">{{ $devisions->firstItem() + $i }}</td>
                        <td><strong>{{ $dev->name }}</strong></td>
                        <td>
                            <span style="background:#f0fdf4; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                {{ $dev->department?->name ?? '-' }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if(auth()->user()->hasPermission('departemen.kelola'))
                            <div style="display:flex; gap:5px; justify-content:center;">
                                <button class="btn btn-warning btn-sm"
                                    onclick="openEdit({{ $dev->id }}, '{{ addslashes($dev->name) }}', {{ $dev->department_id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.devision.destroy', $dev) }}"
                                      onsubmit="return confirm('Hapus divisi {{ addslashes($dev->name) }}?')">
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
                        <td colspan="4" style="text-align:center; padding:40px; color:#94a3b8;">
                            <i class="fas fa-sitemap" style="font-size:36px; opacity:0.3; display:block; margin-bottom:10px;"></i>
                            Belum ada data divisi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="font-size:12px; color:#94a3b8;">Total: {{ $devisions->total() }} divisi</div>
            <div class="pagination">{{ $devisions->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalTambah" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-plus-circle" style="color:#4299e1;"></i> Tambah Divisi</h6>
            <button onclick="closeModal('modalTambah')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.devision.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Divisi <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="Contoh: Pengembangan Produk">
            </div>
            <div class="form-group">
                <label>Departemen <span style="color:#e53e3e;">*</span></label>
                <select name="department_id" class="form-select" required>
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
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
            <h6 style="font-weight:600; color:#2d3748; margin:0;"><i class="fas fa-edit" style="color:#ed8936;"></i> Edit Divisi</h6>
            <button onclick="closeModal('modalEdit')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#718096;">&times;</button>
        </div>
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Divisi <span style="color:#e53e3e;">*</span></label>
                <input type="text" name="name" id="editName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Departemen <span style="color:#e53e3e;">*</span></label>
                <select name="department_id" id="editDeptId" class="form-select" required>
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
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
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function openEdit(id, name, deptId) {
    document.getElementById('editName').value = name;
    document.getElementById('editDeptId').value = deptId;
    document.getElementById('formEdit').action = '{{ route("admin.devision.update", ":id") }}'.replace(':id', id);
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

