@extends('layouts.app')

@section('title', 'Pemantauan Kinerja Guru')

@push('styles')
<style>
    .radar-container-mini { position: relative; height: 180px; width: 100%; }
    .teacher-card { transition: transform 0.2s; }
    .teacher-card:hover { transform: translateY(-5px); }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 bg-info text-white">
            <div class="card-body">
                <h4 class="text-white mb-1"><i class="bx bx-group me-2"></i> Dashboard Kinerja Guru Global</h4>
                <p class="mb-0">Pantau indeks kinerja seluruh Tenaga Pendidik. Klik pada tombol detail untuk masuk ke halaman Rapor spesifik.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @forelse($gurus as $guru)
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="card teacher-card h-100 shadow-sm border {{ isset($guruRadarData[$guru->id_guru]) && $guruRadarData[$guru->id_guru]['skor_akhir'] < 3 ? 'border-danger' : '' }}">
            <div class="card-body p-3 text-center border-bottom bg-light">
                <div class="avatar avatar-md mx-auto mb-2">
                    @if($guru->photo && file_exists(public_path('storage/photos/'.$guru->photo)))
                        <img src="{{ asset('storage/photos/'.$guru->photo) }}" alt="Avatar" class="rounded-circle" style="object-fit:cover;">
                    @else
                        <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle">
                    @endif
                </div>
                <h6 class="mb-0 text-truncate" title="{{ $guru->nama }}">{{ $guru->nama }}</h6>
                <small class="text-muted d-block text-truncate">{{ $guru->mataPelajaran->nama_mapel ?? 'Semua Mapel/Kosong' }}</small>
            </div>
            <div class="card-body p-2 mt-1">
                @if(isset($guruRadarData[$guru->id_guru]))
                    <div class="radar-container-mini mx-auto">
                        <canvas id="guruRadar_{{ $guru->id_guru }}"></canvas>
                    </div>
                    <div class="text-center mt-2 border-top pt-2">
                        @php $skor = $guruRadarData[$guru->id_guru]['skor_akhir']; @endphp
                        <span class="badge {{ $skor >= 4 ? 'bg-success' : ($skor >= 3 ? 'bg-warning' : 'bg-danger') }}">
                            Indeks: {{ number_format($skor, 1) }}
                        </span>
                        <a href="{{ route('admin.monitoring.guru.detail', $guru->id_guru) }}" class="btn btn-xs btn-outline-info d-block mt-2">Detail</a>
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        <i class="bx bx-file-blank mb-1 fs-3"></i><br>
                        Guru ini belum pernah<br>dievaluasi Admin.
                    </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <p class="text-muted mb-0">Belum ada data Guru aktif di sistem.</p>
    </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const labels = @json($chartLabels);
    const guruData = @json($guruRadarData);
    
    for (const [idGuru, payload] of Object.entries(guruData)) {
        const ctxMini = document.getElementById('guruRadar_' + idGuru);
        if(ctxMini) {
            let colorBorder = payload.skor_akhir >= 4 ? '#00cfdd' : (payload.skor_akhir >= 3 ? '#ffab00' : '#ff3e1d');
            let colorBg = payload.skor_akhir >= 4 ? 'rgba(0, 207, 221, 0.2)' : (payload.skor_akhir >= 3 ? 'rgba(255, 171, 0, 0.2)' : 'rgba(255, 62, 29, 0.2)');
            
            new Chart(ctxMini.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: payload.data,
                        backgroundColor: colorBg,
                        borderColor: colorBorder,
                        pointRadius: 2,
                        borderWidth: 1.5,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: { 
                        r: { 
                            min: 0, max: 5, 
                            ticks: { display: false },
                            pointLabels: { display: false } 
                        } 
                    }
                }
            });
        }
    }
});
</script>
@endpush
