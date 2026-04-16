@extends('layouts.app')

@section('title', 'Fiksasi Kelas API')

@section('content')
<div class="container-fluid pt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Aktivasi / Fiksasi Data Kelas</h4>
            <p class="text-muted small">Mendaftarkan draf Rombel <strong>{{ $draft->nama }}</strong> dari API ke dalam Parameter Master Kelas.</p>
        </div>
        <a href="{{ route('admin.api_kelas.index') }}" class="btn btn-outline-secondary">
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
                    <h5 class="mb-0 card-title">Parameter dari API</h5>
                </div>
                <div class="card-body pt-3">
                    <table class="table table-borderless table-sm">
                        <tr><td width="30%" class="text-muted">Nama Kelas</td><td>: <strong>{{ $draft->nama }}</strong></td></tr>
                        <tr><td class="text-muted">ID Sinkronisasi</td><td>: <code>#{{ $draft->kelas_id }}</code></td></tr>
                        <tr><td class="text-muted">Jurusan API</td><td>: <span class="badge bg-secondary">{{ $draft->jurusan_api ?? '-' }}</span> <em>(Bukan relasi asli)</em></td></tr>
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        Sistem telah menganalisis awalan rombel ini dan memprediksi bahwa kelas ini berada pada <strong>Tingkat {{ $prediksiTingkat }}</strong>. Anda dapat mengubahnya di form sebelah jika keliru.
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM FIKSASI -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow border-0 border-top border-info border-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-info"><i class="bx bx-link me-2"></i>Asosiasi Data Lokal</h5>
                </div>
                <form action="{{ route('admin.api_kelas.aktivasi.store', $draft->id) }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Jurusan Master Lokal <span class="text-danger">*</span></label>
                            <select name="id_jurusan" class="form-select select2" required>
                                <option value="">-- Tentukan Jurusan Resmi --</option>
                                @foreach($list_jurusan as $j)
                                    <option value="{{ $j->id_jurusan }}" {{ str_contains(strtoupper($j->nama_jurusan), strtoupper($draft->jurusan_api)) || str_contains(strtoupper($j->kode_jurusan ?? ''), strtoupper($draft->jurusan_api)) ? 'selected' : '' }}>
                                        {{ $j->nama_jurusan }} {{ $j->kode_jurusan ? "($j->kode_jurusan)" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Kaitkan kelas ini dengan jurusan yang sebenarnya di aplikasi E-Absensi Anda.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Tingkat / Derajat (10, 11, 12, dll) <span class="text-danger">*</span></label>
                            <input type="number" name="tingkat" class="form-control" value="{{ $prediksiTingkat }}" min="1" max="13" required>
                        </div>
                        
                    </div>
                    <div class="card-footer text-end mt-2">
                        <button type="submit" class="btn btn-info text-white" onclick="return confirm('Apakah Anda yakin data ini sudah dihubungkan dengan Parameter Lokal secara benar?')">
                            <i class="bx bx-check-double me-1"></i> Fiksasi Rombel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
