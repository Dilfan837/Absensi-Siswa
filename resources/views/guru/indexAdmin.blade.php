@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white"><i class="bx bx-user-pin me-2"></i> Data Guru</h5>
        <button class="btn btn-light btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahGuru">
            <i class="bx bx-plus-circle"></i> Tambah Guru
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

        <form action="{{ route('guru.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Cari Guru</label>
                    <input type="text" name="q" class="form-control" placeholder="Cari NIP atau Nama..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">-- Semua Gender --</option>
                        <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Mapel</label>
                    <select name="id_mapel" class="form-select">
                        <option value="">-- Semua Mapel --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id_mapel }}" {{ request('id_mapel') == $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2"><i class="bx bx-search"></i> Cari</button>
                    <a href="{{ route('guru.index') }}" class="btn btn-secondary w-100"><i class="bx bx-refresh"></i> Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover table-striped border">
                <thead class="table-light">
                    <tr>
                        <th width="5%">Foto</th>
                        <th>NIP / Username</th>
                        <th>Nama Lengkap</th>
                        <th>L/P</th>
                        <th>Mata Pelajaran</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gurus as $guru)
                    <tr>
                        <td>
                            @if($guru->photo && file_exists(public_path('storage/guru/' . $guru->photo)))
                                <img src="{{ asset('storage/guru/' . $guru->photo) }}" alt="Foto Guru" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                            @else
                                <div class="avatar avatar-sm">
                                    <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($guru->nama, 0, 1) }}</span>
                                </div>
                            @endif
                        </td>
                        <td><span class="badge bg-label-dark">{{ $guru->nip }}</span></td>
                        <td class="fw-semibold">{{ $guru->nama }}</td>
                        <td>{{ $guru->jenis_kelamin }}</td>
                        <td>
                            <span class="badge bg-label-success">{{ $guru->mataPelajaran->nama_mapel ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning btn-sm edit-btn text-white shadow-sm"
                                    data-id="{{ $guru->id_guru }}"
                                    data-nip="{{ $guru->nip }}"
                                    data-nama="{{ $guru->nama }}"
                                    data-email="{{ $guru->email }}"
                                    data-jk="{{ $guru->jenis_kelamin }}"
                                    data-mapel="{{ $guru->id_mapel }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditGuru"
                                    title="Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                
                                <form action="{{ route('guru.destroy', $guru->id_guru) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus guru ini? Akun login terkait juga akan ikut terhapus otomatis.')">
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
                            Belum ada data guru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div class="modal fade" id="modalTambahGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('guru.store') }}" method="POST" class="w-100" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">Tambah Data Guru Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-primary mb-3 text-sm">
                        <i class="bx bx-info-circle me-1"></i> Akun *login* akan otomatis dibuat. NIP akan menjadi Username, dan NIP akan menjadi Password awal.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" class="form-control" placeholder="Nomor Induk Pegawai" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama Beserta Gelar" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email Aktif">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="id_mapel" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Matpel --</option>
                                @foreach($mapels as $mapel)
                                    <option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white">Simpan Data & Buat Akun</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Guru -->
<div class="modal fade" id="modalEditGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formEditGuru" method="POST" class="w-100" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Data Guru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" id="edit_nip" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="edit_nama" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" id="edit_jk" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="id_mapel" id="edit_mapel" class="form-select" required>
                                @foreach($mapels as $mapel)
                                    <option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Foto (Opsional)</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto</small>
                        </div>
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
        const formEdit = document.getElementById('formEditGuru');
        
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                document.getElementById('edit_nip').value = this.dataset.nip;
                document.getElementById('edit_nama').value = this.dataset.nama;
                document.getElementById('edit_email').value = this.dataset.email;
                document.getElementById('edit_jk').value = this.dataset.jk;
                document.getElementById('edit_mapel').value = this.dataset.mapel;

                formEdit.action = `/guru/${id}`;
            });
        });
    });
</script>
@endsection
