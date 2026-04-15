@extends('layouts.app')

@section('title', 'Pemantauan Sikap Siswa')

@push('styles')
<style>
    .radar-container-main { position: relative; height: 350px; width: 100%; }
    .radar-container-mini { position: relative; height: 180px; width: 100%; }
    .student-card { transition: transform 0.2s; }
    .student-card:hover { transform: translateY(-5px); }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 bg-primary text-white">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="mb-3 mb-md-0">
                    <h4 class="text-white mb-1">Dashboard Pemantauan Karakter Siswa</h4>
                    <p class="mb-0">Pilih Kelas untuk melihat Radar Chart Rata-rata Gabungan</p>
                </div>
                
                <form action="{{ route('admin.monitoring.siswa') }}" method="GET" class="d-flex align-items-center" id="filterForm">
                    <label class="me-2 fw-bold text-white mb-0">Kelas:</label>
                    <select name="kelas_id" class="form-select border-0 shadow-sm" style="min-width: 200px;" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}" {{ $selectedKelasId == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} {{ $k->jurusan->nama_jurusan ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

@if($selectedKelasId)
<div class="row">
    <!-- Chart Radar 1 Kelas (Agregat) -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header border-bottom">
                <h5 class="mb-0"><i class="bx bx-radar text-primary me-2"></i> Kinerja Sikap Rata-Rata Kelas</h5>
            </div>
            <div class="card-body pt-4">
                @if(count($categories) > 0 && count($siswas) > 0)
                <div class="row align-items-center">
                    <div class="col-md-8 border-end">
                        <div class="radar-container-main">
                            <canvas id="classRadarChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <h1 class="display-3 text-primary mb-0">
                            {{ number_format(count($classAverages) > 0 ? array_sum($classAverages)/count($classAverages) : 0, 1) }}
                        </h1>
                        <p class="text-muted">Indeks Sikap Kelas (Max 5.0)</p>
                        <hr>
                        <div class="text-start ps-3">
                            <small class="text-muted d-block mb-1">Total Siswa Aktif: <strong>{{ count($siswas) }}</strong></small>
                            <small class="text-muted d-block">Penilaian Diambil dari: <strong>Seluruh Guru PBM</strong></small>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="bx bx-data fs-1 mb-2"></i>
                    <p>Kategori belum diatur atau kelas ini kosong/belum ada nilai.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Title Siswa -->
    <div class="col-12 mb-3">
        <h5 class="text-secondary border-bottom pb-2">Rincian Radar Individu Siswa</h5>
    </div>
    
    <!-- Mini Radar Cards for each Siswa -->
    @foreach($siswas as $siswa)
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="card student-card h-100 shadow-sm border {{ isset($siswaRadarData[$siswa->id_siswa]) && $siswaRadarData[$siswa->id_siswa]['skor_akhir'] < 3 ? 'border-danger' : '' }}">
            <div class="card-body p-3 text-center border-bottom bg-light">
                <div class="avatar avatar-md mx-auto mb-2">
                    @if($siswa->foto && file_exists(public_path('storage/photos/'.$siswa->foto)))
                        <img src="{{ asset('storage/photos/'.$siswa->foto) }}" alt="Avatar" class="rounded-circle" style="object-fit:cover;">
                    @else
                        <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle">
                    @endif
                </div>
                <h6 class="mb-0 text-truncate" title="{{ $siswa->nama_siswa }}">{{ $siswa->nama_siswa }}</h6>
                <small class="text-muted">{{ $siswa->nis }}</small>
            </div>
            <div class="card-body p-2 mt-1">
                @if(isset($siswaRadarData[$siswa->id_siswa]))
                    <div class="radar-container-mini mx-auto">
                        <canvas id="studentRadar_{{ $siswa->id_siswa }}"></canvas>
                    </div>
                    <div class="text-center mt-2 border-top pt-2">
                        @php $skor = $siswaRadarData[$siswa->id_siswa]['skor_akhir']; @endphp
                        <span class="badge {{ $skor >= 4 ? 'bg-success' : ($skor >= 3 ? 'bg-warning' : 'bg-danger') }}">
                            Skor Akhir: {{ number_format($skor, 1) }}
                        </span>
                        <a href="{{ route('admin.monitoring.siswa.detail', $siswa->id_siswa) }}" class="btn btn-xs btn-outline-primary d-block mt-2">Detail</a>
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        <i class="bx bx-sleep mb-1 fs-3"></i><br>
                        Belum ada masuk rating.
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const labels = @json($chartLabels);
    
    // Class Radar Chart
    @if($selectedKelasId && count($classAverages) > 0)
    const ctxMain = document.getElementById('classRadarChart').getContext('2d');
    new Chart(ctxMain, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Rata-rata Kelas',
                data: @json($classAverages),
                backgroundColor: 'rgba(105, 108, 255, 0.2)',
                borderColor: '#696cff',
                pointBackgroundColor: '#696cff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { r: { min: 0, max: 5, ticks: { display: false } } }
        }
    });
    @endif

    // Mini Student Radar Charts
    @if($selectedKelasId)
        const siswaData = @json($siswaRadarData);
        for (const [idSiswa, payload] of Object.entries(siswaData)) {
            const ctxMini = document.getElementById('studentRadar_' + idSiswa);
            if(ctxMini) {
                let colorBorder = payload.skor_akhir >= 4 ? '#71dd37' : (payload.skor_akhir >= 3 ? '#ffab00' : '#ff3e1d');
                let colorBg = payload.skor_akhir >= 4 ? 'rgba(113, 221, 55, 0.2)' : (payload.skor_akhir >= 3 ? 'rgba(255, 171, 0, 0.2)' : 'rgba(255, 62, 29, 0.2)');
                
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
                                pointLabels: { display: false } // Hide labels to save space on mini radar
                            } 
                        }
                    }
                });
            }
        }
    @endif
});
</script>
@endpush
