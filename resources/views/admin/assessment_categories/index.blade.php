@extends('layouts.app')

@section('title', 'Kategori Penilaian')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white"><i class="bx bx-list-check me-2"></i> Manajemen Kategori Penilaian</h5>
        <button class="btn btn-light btn-sm fw-bold text-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="bx bx-plus-circle"></i> Tambah Kategori
        </button>
    </div>
    <div class="card-body mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive text-nowrap">
            <table class="table table-hover table-striped border">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Konteks</th>
                        <th>Nama Indikator</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $key => $row)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            @if($row->type === 'siswa')
                                <span class="badge bg-label-info"><i class="bx bx-user-badge me-1"></i>Siswa</span>
                            @else
                                <span class="badge bg-label-warning"><i class="bx bx-user-pin me-1"></i>Guru</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $row->name }}</td>
                        <td class="text-wrap" style="max-width: 250px;">{{ $row->description }}</td>
                        <td class="text-center">
                            @if($row->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning btn-sm edit-btn text-white shadow-sm"
                                    data-id="{{ $row->id }}"
                                    data-type="{{ $row->type }}"
                                    data-name="{{ $row->name }}"
                                    data-description="{{ $row->description }}"
                                    data-is_active="{{ $row->is_active ? '1' : '0' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditKategori"
                                    title="Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                
                                <form action="{{ route('assessment-categories.destroy', $row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus indikator ini? Menghapus indikator yang sudah dinilai akan menyebabkan error atau hilangnya data rekap nilai tersebut.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm shadow-sm" title="Hapus">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bx bx-folder-open fs-1 d-block mb-2"></i>
                            Belum ada Kategori Penilaian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('assessment-categories.store') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Tambah Kategori Penilaian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Konteks Penilaian <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="siswa">Untuk Siswa (Dinilai oleh Guru)</option>
                            <option value="guru">Untuk Guru (Dinilai oleh Admin)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Indikator <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kesopanan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan secara spesifik agar evaluator memiliki standar baku." required></textarea>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="flexSwitchCheckChecked" checked>
                        <label class="form-check-label" for="flexSwitchCheckChecked">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditKategori" method="POST" class="w-100">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Kategori Penilaian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Konteks Penilaian <span class="text-danger">*</span></label>
                        <select name="type" id="edit_type" class="form-select" required>
                            <option value="siswa">Untuk Siswa (Dinilai oleh Guru)</option>
                            <option value="guru">Untuk Guru (Dinilai oleh Admin)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Indikator <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                        <label class="form-check-label" for="edit_is_active">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">Update Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editBtns = document.querySelectorAll('.edit-btn');
        const formEdit = document.getElementById('formEditKategori');
        const editType = document.getElementById('edit_type');
        const editName = document.getElementById('edit_name');
        const editDescription = document.getElementById('edit_description');
        const editIsActive = document.getElementById('edit_is_active');

        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                editType.value = this.dataset.type;
                editName.value = this.dataset.name;
                editDescription.value = this.dataset.description;
                editIsActive.checked = this.dataset.is_active === '1';

                // Update action URL form
                formEdit.action = `/assessment-categories/${id}`;
            });
        });
    });
</script>
@endsection
