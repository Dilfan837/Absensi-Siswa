@extends('layouts.app')

@section('title', 'Laporan Sikap Siswa')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 bg-info text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-white mb-1"><i class="bx bx-radar me-1"></i> Laporan Sikap Siswa</h4>
                    <p class="mb-0">Rapor Perilaku & Sikap dari Evaluasi Guru</p>
                </div>
                <!-- Optional: Back button if accessed by Admin or Guru -->
                @if(auth()->user()->role->nama_role != 'siswa')
                    <a href="javascript:history.back()" class="btn btn-sm btn-light text-info fw-bold">Kembali</a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <!-- Profile Card -->
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mx-auto mb-3" style="width: 100px; height: 100px;">
                    @if($siswa->foto && file_exists(public_path('storage/photos/'.$siswa->foto)))
                        <img src="{{ asset('storage/photos/'.$siswa->foto) }}" alt="Avatar" class="rounded-circle" style="object-fit: cover; width: 100px; height: 100px;">
                    @else
                        <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle w-100 h-100">
                    @endif
                </div>
                <h5 class="mb-1 fw-bold">{{ $siswa->nama_siswa }}</h5>
                <p class="text-muted mb-0">NIS: {{ $siswa->nis }}</p>
                <span class="badge bg-label-primary mt-2">{{ $siswa->kelas->nama_kelas ?? '-' }} {{ $siswa->kelas->jurusan->nama_jurusan ?? '' }}</span>
                
                <hr class="my-4">
                
                <div class="text-start">
                    <h6 class="text-muted text-uppercase fw-bold mb-3 d-flex align-items-center"><i class="bx bx-info-circle me-1"></i> Informasi Lengkap</h6>
                    <ul class="list-unstyled mb-0 text-muted">
                        <li class="d-flex align-items-center mb-2"><i class="bx bx-envelope me-2"></i> {{ $siswa->user->email ?? '-' }}</li>
                        <li class="d-flex align-items-center mb-2"><i class="bx bx-phone me-2"></i> {{ $siswa->no_hp ?? '-' }}</li>
                        <li class="d-flex align-items-center"><i class="bx bx-map me-2"></i> {{ Str::limit($siswa->alamat, 30) ?? '-' }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <!-- Radar Chart Card -->
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="mb-0"><i class="bx bx-pie-chart-alt-2 text-primary me-1"></i> Rata-rata Penilaian Sikap (Overall)</h5>
                <small class="text-muted">Visualisasi radar dari hasil penilaian semua indikator.</small>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center py-4" style="position: relative; height: 400px;">
                <canvas id="radarChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="mb-0"><i class="bx bx-history text-primary me-1"></i> Histori Penilaian</h5>
            </div>
            <div class="card-body mt-3">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Periode / Tanggal</th>
                                <th>Penilai (Guru)</th>
                                <th>Detail Nilai (Skala 1-5)</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $ast)
                            <tr>
                                <td>
                                    <strong>{{ $ast->period }}</strong><br>
                                    <small class="text-muted">{{ date('d M Y', strtotime($ast->assessment_date)) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            @if($ast->evaluator && $ast->evaluator->guru && $ast->evaluator->guru->photo)
                                                <img src="{{ asset('storage/photos/'.$ast->evaluator->guru->photo) }}" class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary"><i class="bx bx-user"></i></span>
                                            @endif
                                        </div>
                                        <span>{{ $ast->evaluator->guru->nama ?? $ast->evaluator->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($ast->details as $detail)
                                            <li class="mb-1" style="font-size: 0.85rem">
                                                <span class="fw-semibold d-inline-block" style="width: 150px;">{{ $detail->category->name }}</span>: 
                                                <span class="text-warning">
                                                    @for($i=1; $i<=5; $i++)
                                                        @if($i <= $detail->score) &#9733; @else &#9734; @endif
                                                    @endfor
                                                </span> ({{ $detail->score }})
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="text-wrap" style="max-width: 250px;">
                                    @if($ast->general_notes)
                                        <i class="bx bxs-quote-alt-left text-muted me-1"></i> <span class="fst-italic">{{ $ast->general_notes }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('radarChart').getContext('2d');
        const labels = {!! json_encode($chartLabels) !!};
        const dataValues = {!! json_encode($chartData) !!};

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Rata-Rata Nilai Sikap',
                    data: dataValues,
                    backgroundColor: 'rgba(105, 108, 255, 0.2)', // Primary color with opacity
                    borderColor: 'rgba(105, 108, 255, 1)',
                    pointBackgroundColor: 'rgba(105, 108, 255, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(105, 108, 255, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 5,
                        ticks: {
                            stepSize: 1,
                            backdropColor: 'transparent'
                        },
                        pointLabels: {
                            font: {
                                size: 12,
                                family: "'Public Sans', sans-serif",
                            },
                            color: '#566a7f'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Nilai Rata-Rata: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
