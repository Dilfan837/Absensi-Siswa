@extends('layouts.app')

@section('title', 'Fiksasi Guru API')

@section('content')
<div class="container-fluid pt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Aktivasi Data Guru</h4>
            <p class="text-muted small">Mendaftarkan draf Guru <strong>{{ $draft->nama }}</strong> dari API ke dalam Database Utama.</p>
        </div>
        <a href="{{ route('admin.api_guru.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Draft
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="bx bx-error-circle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- DETAIL DATA API -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0 card-title">Biodata draf Guru dari API</h5>
                </div>
                <div class="card-body pt-3">
                    <table class="table table-borderless table-sm">
                        <tr><td width="30%" class="text-muted">Nama Lengkap</td><td>: <strong>{{ $draft->nama }}</strong></td></tr>
                        <tr><td class="text-muted">NIP</td><td>: {!! $draft->nip ? $draft->nip : '<span class="text-danger">KOSONG</span>' !!}</td></tr>
                        <tr><td class="text-muted">NUPTK</td><td>: {{ $draft->nuptk ?? '-' }}</td></tr>
                        <tr><td class="text-muted">NIK</td><td>: {{ $draft->nik ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Jenis Kelamin</td><td>: {{ $draft->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }}</td></tr>
                        <tr><td class="text-muted">TTL</td><td>: {{ $draft->tempat_lahir }}, {{ $draft->tanggal_lahir }}</td></tr>
                        <tr><td class="text-muted">Email / No HP</td><td>: {{ $draft->email ?? '-' }} / {{ $draft->no_hp ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- FORM FIKSASI -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow border-0 border-top border-success border-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-success"><i class="bx bx-user-plus me-2"></i>Fiksasi Status Kepegawaian</h5>
                </div>
                <form action="{{ route('admin.api_guru.aktivasi.store', $draft->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        
                        @if(empty($draft->nip))
                        <div class="alert alert-warning mb-3">
                            <small><i class="bx bx-bulb me-1"></i> Data API tidak memiliki NIP. Anda wajib membuatkan NIP sementara karena Aplikasi menggunakan NIP sebagai Username Login bawaan.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Manual Input NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip_manual" class="form-control" placeholder="Masukkan NIP untuk login..." required>
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Mata Pelajaran Induk <span class="text-danger">*</span></label>
                            <select name="id_mapel" class="form-select select2" required>
                                <option value="">-- Tentukan Mata Pelajaran --</option>
                                @foreach($list_mapel as $k)
                                    <option value="{{ $k->id_mapel }}">
                                        {{ $k->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seorang guru memerlukan asosiasi mata pelajaran utama di aplikasi absen.</small>
                        </div>
                        
                    </div>
                    <div class="card-footer text-end mt-2">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Data akan dipindahkan permanen menjadi Guru Aktif. Yakin?')">
                            <i class="bx bx-check-double me-1"></i> Finalisasi & Buat Akun Guru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
