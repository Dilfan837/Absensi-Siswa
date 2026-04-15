@extends('layouts.app')

@section('title', 'Laporan Sikap Kosong')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 text-center mt-5">
        <div class="card shadow-sm border-0">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="bx bx-radar text-muted" style="font-size: 6rem;"></i>
                </div>
                <h4 class="fw-bold mb-2">Siswa Belum Dinilai</h4>
                <p class="text-muted mb-4">Siswa <strong>{{ $siswa->nama_siswa }}</strong> belum menerima penilaian sikap/perilaku dari guru sejauh ini. Laporan radar akan muncul seketika setelah penilaian terbentuk di sistem.</p>
                <a href="{{ url()->previous() }}" class="btn btn-primary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
