@extends('layouts.app')

@section('title', 'Ruang Draft API Siswa')

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
            <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Sinkronisasi /</span> Gudang Draft API Siswa</h4>
            <p class="text-muted small">
                Data di sini adalah draf "mentah" dari API pusat. Siswa di bawah ini <b>BELUM</b> memiliki akun pengguna dan <b>BELUM</b> terdaftar dalam sistem absensi sebelum Anda mengaktivasinya dengan kamera pengenal wajah.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <form action="{{ route('admin.api_siswa.fetch') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menarik/menyinkronkan draf terbaru dari API Pusat? Ini mungkin memerlukan waktu beberapa detik.')">
                @csrf
                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bx bx-cloud-download me-1"></i> Tarik / Update Data API
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white">Daftar Calon Siswa (Membutuhkan Pendaftaran Wajah)</h5>
            <span class="badge bg-warning">{{ $drafts->count() }} Menunggu Aktivasi</span>
        </div>

        <div class="card-body mt-3">
            <form method="GET" action="{{ route('admin.api_siswa.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.api_siswa.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS/No Induk</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>L/P</th>
                            <th>Rombel Asal API</th>
                            <th>Status Akun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($drafts as $index => $s)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $s->no_induk ?? '-' }}</strong></td>
                                <td>{{ $s->nisn ?? '-' }}</td>
                                <td>{{ $s->nama }}</td>
                                <td>{{ $s->jenis_kelamin }}</td>
                                <td><span class="badge bg-label-info">{{ $s->nama_rombel ?? 'Belum ada kelas' }}</span></td>
                                <td>
                                    <span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i>Belum Aktif</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.api_siswa.aktivasi.form', $s->id) }}" class="btn btn-sm btn-success text-white">
                                        <i class="bx bx-face"></i> Fiksasi & Aktifkan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">Data draf API kosong. Silakan klik tombol "Tarik Data API".</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
