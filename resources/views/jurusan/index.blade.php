@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Jurusan</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahJurusan">
            <i class="fas fa-plus"></i> Tambah Jurusan
        </button>
    </div>
    <div class="card-body">
        
        {{-- Form Pencarian --}}
        <form action="{{ route('jurusan.index') }}" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Cari Nama Jurusan..." value="{{ request('q') }}">
                <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Cari</button>
                <a href="{{ route('jurusan.index') }}" class="btn btn-secondary"><i class="bx bx-refresh"></i> Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Jurusan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurusan as $key => $row)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $row->nama_jurusan }}</td>
                        <td>
                            <form action="{{ route('jurusan.destroy', $row->id_jurusan) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus jurusan ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahJurusan" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('jurusan.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>Tambah Jurusan</h5></div>
                <div class="modal-body">
                    <label>Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" class="form-control" placeholder="Masukan nama jurusan" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection