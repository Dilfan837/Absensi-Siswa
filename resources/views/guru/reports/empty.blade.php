@extends('layouts.app')

@section('title', 'Laporan Kinerja Guru Kosong')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 text-center mt-5">
        <div class="card shadow-sm border-0">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="bx bx-radar text-muted" style="font-size: 6rem;"></i>
                </div>
                <h4 class="fw-bold mb-2">Belum Ada Data Penilaian</h4>
                <p class="text-muted mb-4">Guru <strong>{{ $guru->nama }}</strong> belum pernah dievaluasi oleh Admin. Laporan radar chart akan terbentuk otomatis setelah penilaian pertama dikirimkan.</p>
                <a href="{{ url()->previous() }}" class="btn btn-primary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
