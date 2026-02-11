@extends('layouts.app')

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-lg-8 mb-4 order-0">
        <div class="card">
          <div class="d-flex align-items-end row">
            <div class="col-sm-7">
              <div class="card-body">
                <h5 class="card-title text-primary">Selamat Datang, Guru! 🎓</h5>
                <p class="mb-4">
                  Anda memiliki <span class="fw-bold">{{ $sesiAktif }}</span> sesi absensi aktif saat ini.
                </p>
                <a href="{{ route('absensi.index') }}" class="btn btn-sm btn-outline-primary">Kelola Absensi</a>
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
                    <i class='bx bx-list-check text-info fs-3'></i>
                  </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Absensi</span>
                <h3 class="card-title mb-2">{{ $totalAbsensi }}</h3>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 col-6 mb-4">
            <div class="card">
              <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                  <div class="avatar flex-shrink-0">
                    <i class='bx bx-broadcast text-warning fs-3'></i>
                  </div>
                </div>
                <span class="fw-semibold d-block mb-1">Sesi Aktif</span>
                <h3 class="card-title mb-2">{{ $sesiAktif }}</h3>
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
            <h3 class="card-title text-nowrap mb-2 text-danger">{{ $alpaHariIni }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0">Riwayat Absensi Saya</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive text-nowrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Nama Absensi</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($sesiTerakhir as $sesi)
                    <tr>
                      <td><strong>{{ $sesi->nama_absensi ?? 'N/A' }}</strong></td>
                      <td>{{ $sesi->kelas->nama_kelas ?? 'N/A' }}</td>
                      <td>{{ $sesi->tanggal ? \Carbon\Carbon::parse($sesi->tanggal)->format('d M Y') : 'N/A' }}</td>
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
                      <td colspan="5" class="text-center p-3">Belum ada riwayat absensi.</td>
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
