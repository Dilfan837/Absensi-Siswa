@extends('layouts.app')

@section('title', 'Ruang Draft API Guru')

@section('content')
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

    <div class="row mb-4 pt-3">
        <div class="col-md-8">
            <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Sinkronisasi /</span> Gudang Draft API Guru</h4>
            <p class="text-muted small">
                Data di sini merupakan draf mentah Guru yang belum divalidasi. Sebelum bisa login atau dibuatkan akun, Admin wajib memeriksa dan mengikat masing-masing tenaga pengajar ini dengan Mata Pelajaran.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <form action="{{ route('admin.api_guru.fetch') }}" method="POST" onsubmit="return confirm('Tarik data Guru terbaru dari API Pusat? Data yang sama otomatis diperbarui tanpa duplikat.')">
                @csrf
                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bx bx-cloud-download me-1"></i> Tarik / Update Data API Guru
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white">Daftar Calon Guru (Draf API)</h5>
            <span class="badge bg-warning">{{ $drafts->count() }} Menunggu Fiksasi</span>
        </div>

        <div class="card-body mt-3">
            <form method="GET" action="{{ route('admin.api_guru.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, NIP, atau NUPTK..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.api_guru.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>NUPTK</th>
                            <th>Nama Guru</th>
                            <th>L/P</th>
                            <th>NIK</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($drafts as $index => $s)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $s->nip ?? '-' }}</strong></td>
                                <td>{{ $s->nuptk ?? '-' }}</td>
                                <td>{{ $s->nama }}</td>
                                <td>{{ $s->jenis_kelamin }}</td>
                                <td>{{ $s->nik ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.api_guru.aktivasi.form', $s->id) }}" class="btn btn-sm btn-success text-white">
                                        <i class="bx bx-check-shield"></i> Fiksasi & Aktifkan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Data draf API Guru kosong. Silakan klik tombol "Tarik Data API".</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
