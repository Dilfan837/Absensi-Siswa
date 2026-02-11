@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
    {{-- Notifikasi Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white">Data Guru</h5>
            {{-- Optional: Add manual create button if needed --}}
            {{-- <a href="{{ route('guru.create') }}" class="btn btn-light btn-sm"><i class="bx bx-plus me-1"></i> Tambah Guru</a> --}}
        </div>

        <div class="card-body">
            {{-- Form Pencarian --}}
            <form action="{{ route('guru.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cari Guru</label>
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Cari NIP, NUPTK, atau Nama..." value="{{ request('q') }}">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Cari</button>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('guru.index') }}" class="btn btn-secondary w-100"><i class="bx bx-refresh"></i> Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>NIP / NUPTK</th>
                            <th>Nama Guru</th>
                            <th>L/P</th>
                            <th>No HP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($gurus as $g)
                            <tr>
                                <td>
                                    @if($g->photo)
                                        {{-- Assuming photo is stored or URL --}}
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($g->nama, 0, 2) }}</span>
                                        </div>
                                    @else
                                        <div class="avatar avatar-sm bg-secondary rounded-circle"></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $g->nip ?? '-' }}</span>
                                        <small class="text-muted">{{ $g->nuptk ?? '-' }}</small>
                                    </div>
                                </td>
                                <td><strong>{{ $g->nama }}</strong></td>
                                <td>{{ $g->jenis_kelamin }}</td>
                                <td>{{ $g->no_hp ?? '-' }}</td>
                                <td>
                                    @if($g->status_aktif)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-danger">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('guru.destroy', $g->id_guru) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                    {{-- Optional: Edit button --}}
                                    {{-- <a href="{{ route('guru.edit', $g->guru_id) }}" class="btn btn-sm btn-outline-warning"><i class="bx bx-edit"></i></a> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Data guru belum tersedia. Silakan lakukan sinkronisasi data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $gurus->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
