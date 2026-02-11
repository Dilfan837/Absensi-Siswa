@extends('layouts.app')

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-lg-8 mb-4 order-0">
        <div class="card">
          <div class="d-flex align-items-end row">
            <div class="col-sm-7">
              <div class="card-body">
                <h5 class="card-title text-primary">Selamat Datang, {{ Auth::user()->nama ?? 'Admin' }}! 🎉</h5>
                <p class="mb-4">
                  Hari ini ada <span class="fw-bold">{{ $sesiAktif }}</span> sesi absensi aktif.
                  Tingkat kehadiran siswa terpantau secara real-time.
                </p>
                {{-- Diperbaiki: Link diarahkan ke index --}}
                <a href="{{ route('absensi.index') }}" class="btn btn-sm btn-outline-primary">Lihat Daftar Absensi</a>
              </div>
            </div>
            <div class="col-sm-5 text-center text-sm-left">
              <div class="card-body pb-0 px-0 px-md-4">
                <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140"
                  alt="View Badge User">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-4 order-1">
        <div class="row">
          <div class="col-lg-6 col-md-12 col-6 mb-4">
            <div class="card">
              <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                  <div class="avatar flex-shrink-0">
                    <i class='bx bx-buildings text-info fs-3'></i>
                  </div>
                </div>
                <span class="fw-semibold d-block mb-1">Jurusan</span>
                <h3 class="card-title mb-2">{{ $totalJurusan }}</h3>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 col-6 mb-4">
            <div class="card">
              <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                  <div class="avatar flex-shrink-0">
                    <i class='bx bx-door-open text-warning fs-3'></i>
                  </div>
                </div>
                <span class="fw-semibold d-block mb-1">Kelas</span>
                <h3 class="card-title mb-2">{{ $totalKelas }}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card text-center">
          <div class="card-body">
            <div class="avatar bg-label-primary p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
              <i class='bx bx-group fs-3'></i>
            </div>
            <span class="d-block mb-1">Total Siswa</span>
            <h3 class="card-title text-nowrap mb-2">{{ $totalSiswa }}</h3>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card text-center">
          <div class="card-body">
            <div class="avatar bg-label-success p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
              <i class='bx bx-check-shield fs-3'></i>
            </div>
            <span class="d-block mb-1">Hadir (Hari Ini)</span>
            <h3 class="card-title text-nowrap mb-2 text-success">{{ $hadirHariIni }}</h3>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card text-center">
          <div class="card-body">
            <div class="avatar bg-label-danger p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
              <i class='bx bx-user-x fs-3'></i>
            </div>
            <span class="d-block mb-1">Alpa (Hari Ini)</span>
            <h3 class="card-title text-nowrap mb-2 text-danger">{{ $statAlpa }}</h3>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card text-center">
          <div class="card-body">
            <div class="avatar bg-label-info p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
              <i class='bx bx-broadcast fs-3'></i>
            </div>
            <span class="d-block mb-1">Sesi Aktif</span>
            <h3 class="card-title text-nowrap mb-2 text-info">{{ $sesiAktif }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0">Persentase Kehadiran</h5>
          </div>
          <div class="card-body">
            {{-- Div penampung Chart --}}
            <div id="attendanceChart"></div>
          </div>
        </div>
      </div>

      <div class="col-md-12 col-lg-8 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0">Sesi Absensi Terakhir</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive text-nowrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Nama Absensi</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($sesiTerakhir as $sesi)
                    <tr>
                      <td><strong>{{ $sesi->nama_absensi ?? 'N/A' }}</strong></td>
                      <td>{{ $sesi->kelas->nama_kelas ?? 'N/A' }}</td>
                      <td>
                        <span class="badge {{ $sesi->status == 'aktif' ? 'bg-label-success' : 'bg-label-secondary' }}">
                          {{ strtoupper($sesi->status) }}
                        </span>
                      </td>
                      <td>
                        <a href="{{ route('absensi.show', $sesi->id_absensi) }}" class="btn btn-sm btn-icon btn-outline-primary">
                          <i class='bx bx-show'></i>
                        </a>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center p-3">Belum ada sesi absensi terbaru.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var options = {
        chart: { type: 'donut', height: 300 },
        {{-- Mengirim data dari Controller, default ke 0 jika null --}}
        series: [{{ $hadirHariIni ?? 0 }}, {{ $statAlpa ?? 0 }}], 
        labels: ['Hadir', 'Alpa'],
        colors: ['#71dd37', '#ff3e1d'],
        legend: { position: 'bottom' },
        plotOptions: { 
          pie: { 
            donut: { 
              size: '70%',
              labels: {
                show: true,
                total: {
                    show: true,
                    label: 'Total',
                    formatter: function (w) {
                      return {{ ($hadirHariIni ?? 0) + ($statAlpa ?? 0) }}
                    }
                }
              }
            } 
          } 
        }
      };

      var chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
      chart.render();
    });
  </script>
@endpush