@extends('layouts.app')

@section('title', 'Data Mata Pelajaran')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white"><i class="bx bx-book-bookmark me-2"></i> Manajemen Mata Pelajaran</h5>
        <button class="btn btn-light btn-sm fw-bold text-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMapel">
            <i class="bx bx-plus-circle"></i> Tambah Pelajaran
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
                        <th>Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $key => $row)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td><span class="badge bg-label-info">{{ $row->kode_mapel ?? '-' }}</span></td>
                        <td class="fw-semibold">{{ $row->nama_mapel }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning btn-sm edit-btn text-white shadow-sm"
                                    data-id="{{ $row->id_mapel }}"
                                    data-nama="{{ $row->nama_mapel }}"
                                    data-kode="{{ $row->kode_mapel }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditMapel"
                                    title="Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                
                                <form action="{{ route('mata-pelajaran.destroy', $row->id_mapel) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus mata pelajaran ini? Aksi ini berpotensi menghilangkan referensi data Guru yang memakainya.')">
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
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="bx bx-folder-open fs-1 d-block mb-2"></i>
                            Belum ada data mata pelajaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Mapel -->
<div class="modal fade" id="modalTambahMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('mata-pelajaran.store') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Tambah Mata Pelajaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel" class="form-control" placeholder="Contoh: Matematika" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Mata Pelajaran</label>
                        <input type="text" name="kode_mapel" class="form-control" placeholder="Contoh: MAT (Opsional)">
                        <small class="text-muted">Biarkan kosong jika tidak ada kode spesifik.</small>
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

<!-- Modal Edit Mapel -->
<div class="modal fade" id="modalEditMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditMapel" method="POST" class="w-100">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Mata Pelajaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel" id="edit_nama_mapel" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Mata Pelajaran</label>
                        <input type="text" name="kode_mapel" id="edit_kode_mapel" class="form-control">
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
        const formEdit = document.getElementById('formEditMapel');
        const editNama = document.getElementById('edit_nama_mapel');
        const editKode = document.getElementById('edit_kode_mapel');

        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                editNama.value = this.dataset.nama;
                editKode.value = this.dataset.kode !== '-' ? this.dataset.kode : '';

                // Update action URL form
                formEdit.action = `/mata-pelajaran/${id}`;
            });
        });
    });
</script>
@endsection
