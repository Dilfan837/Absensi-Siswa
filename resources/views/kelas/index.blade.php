@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white">Data Kelas</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
            <i class="bx bx-plus"></i> Tambah Kelas
        </button>
    </div>
    <div class="card-body">
        {{-- Form Pencarian --}}
        <form action="{{ route('kelas.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tingkat</label>
                    <select name="tingkat" class="form-select">
                        <option value="">-- Semua --</option>
                        <option value="X" {{ request('tingkat') == 'X' ? 'selected' : '' }}>X</option>
                        <option value="XI" {{ request('tingkat') == 'XI' ? 'selected' : '' }}>XI</option>
                        <option value="XII" {{ request('tingkat') == 'XII' ? 'selected' : '' }}>XII</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nama Kelas</label>
                    <input type="text" name="q" class="form-control" placeholder="Cari Nama Kelas..." value="{{ request('q') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Jurusan</label>
                    <select name="id_jurusan" class="form-select">
                        <option value="">-- Semua Jurusan --</option>
                        @foreach($jurusan as $j)
                            <option value="{{ $j->id_jurusan }}" {{ request('id_jurusan') == $j->id_jurusan ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2"><i class="bx bx-search"></i></button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary w-100"><i class="bx bx-refresh"></i></a>
                </div>
            </div>
        </form>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tingkat</th>
                        <th>Nama Kelas</th>
                        <th>Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($kelas as $k)
                    <tr>
                        <td><span class="badge bg-label-primary">{{ $k->tingkat }}</span></td>
                        <td><strong>{{ $k->nama_kelas }}</strong></td>
                        <td>{{ $k->jurusan->nama_jurusan ?? 'Tanpa Jurusan' }}</td>
                        <td>
                            <form action="{{ route('kelas.destroy', $k->id_kelas) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bx bx-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahKelasTitle">Tambah Data Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('kelas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Jurusan</label>
                            <select name="id_jurusan" class="form-select" required>
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusan as $j)
                                    <option value="{{ $j->id_jurusan }}">{{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label class="form-label">Tingkat</label>
                            <select name="tingkat" class="form-select" required>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                        <div class="col mb-0">
                            <label class="form-label">Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: RPL 1" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection