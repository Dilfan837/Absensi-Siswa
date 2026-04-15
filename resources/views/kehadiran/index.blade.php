@extends('layouts.app')

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light">Kehadiran /</span> Daftar Kehadiran
    </h4>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Rekap Kehadiran Siswa</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Sesi Absensi</th>
                <th>Waktu Absen</th>
                <th>Status</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($kehadiran as $index => $item)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td><strong>{{ $item->siswa->nama_siswa ?? 'N/A' }}</strong></td>
                  <td>{{ $item->absensi->nama_absensi ?? 'N/A' }}</td>
                  <td>{{ $item->waktu_absen ? \Carbon\Carbon::parse($item->waktu_absen)->format('d M Y H:i') : '-' }}</td>
                  <td>
                    @php
                      $status = strtolower($item->status_kehadiran);
                      $badgeClass = match($status) {
                        'hadir' => 'bg-label-success',
                        'alpha', 'alpa' => 'bg-label-danger',
                        'izin' => 'bg-label-warning',
                        'sakit' => 'bg-label-info',
                        default => 'bg-label-secondary',
                      };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($item->status_kehadiran) }}</span>
                  </td>
                  <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-4">
                    <i class="bx bx-info-circle text-muted fs-3 d-block mb-2"></i>
                    Belum ada data kehadiran.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
