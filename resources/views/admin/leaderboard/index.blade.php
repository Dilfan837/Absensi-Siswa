@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
<div class="card shadow border-0">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-white"><i class="bx bx-trophy me-2"></i> Leaderboard Integritas Sekolah</h5>
    </div>

    <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="row mb-4">
                        <div class="col-md-4">
                            <select name="id_kelas" class="form-control border px-2" onchange="this.form.submit()">
                                <option value="">🏆 Semua Kelas (Se-Sekolah)</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                        🎯 Top Kelas {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <div class="row">
                        <!-- KOLOM KIRI: LEADERBOARD -->
                        <div class="col-lg-8">
                            <h6 class="mb-3 text-uppercase fw-bold text-muted">Peringkat {{ request('id_kelas') ? 'Kelas' : 'Global' }}</h6>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover table-striped border mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="10%">Rank</th>
                                            <th>Siswa</th>
                                            <th class="text-center">Saldo Poin</th>
                                            <th class="text-center">Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leaderboard->take(10) as $index => $s)
                                            @php $lvl = $s->getIntegrityLevel(); @endphp
                                            <tr>
                                                <td class="align-middle text-center">
                                                    @if($index == 0) <span class="fs-4">🥇</span>
                                                    @elseif($index == 1) <span class="fs-4">🥈</span>
                                                    @elseif($index == 2) <span class="fs-4">🥉</span>
                                                    @else <span class="text-sm font-weight-bold">{{ $index + 1 }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $s->nama_siswa }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $s->kelas->nama_kelas ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="badge {{ $s->point_balance > 0 ? 'bg-success' : 'bg-danger' }}">{{ $s->point_balance }} 🪙 Pts</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="fw-bold" style="color: {{ $lvl['color'] }}">
                                                        {{ $lvl['icon'] }} {{ $lvl['label'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: LIVE MUTASI -->
                        <div class="col-lg-4 mt-lg-0 mt-4">
                            <h6 class="mb-3 text-uppercase fw-bold text-muted">Live Feed Transaksi</h6>
                            <div class="card shadow-none border max-height-400 overflow-auto" style="height: 450px;">
                                <div class="card-body p-3">
                                    @forelse($latestMutations as $m)
                                        <div class="d-flex mb-3 align-items-start border-bottom pb-2">
                                            <div class="me-3 p-2 rounded-circle bg-label-{{ $m->amount > 0 ? 'success' : 'danger' }}">
                                                <i class="bx bx-{{ $m->amount > 0 ? 'trending-up' : 'trending-down' }} fs-4"></i>
                                            </div>
                                            <div class="d-flex flex-column w-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="text-sm fw-bold mb-0 text-dark">{{ $m->siswa->nama_siswa }}</h6>
                                                    <span class="badge bg-{{ $m->amount > 0 ? 'success' : 'danger' }} ms-2">
                                                        {{ $m->amount > 0 ? '+' : '' }}{{ $m->amount }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-muted mb-1">{{ $m->description }}</p>
                                                <small class="text-xs text-secondary"><i class="bx bx-time-five me-1"></i>{{ $m->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-muted text-center">Belum ada transaksi</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
