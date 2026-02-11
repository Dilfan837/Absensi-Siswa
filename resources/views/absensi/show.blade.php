@extends('layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-5 mb-4">
                <div class="card shadow-none border h-100">
                    <div class="card-header bg-primary text-center py-3">
                        <h5 class="mb-0 text-white fw-bold">SCAN QR CODE</h5>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold mb-1 text-uppercase">{{ $absensi->nama_absensi }}</h3>
                            <span class="badge bg-label-secondary mb-2">Sesi Sedang Berlangsung</span>
                            <div id="countdown-timer" class="fw-bold text-danger fs-4">--:--:--</div>
                        </div>

                        <!-- Form Hidden untuk Auto-Close -->
                        <form id="form-tutup-sesi" action="{{ route('absensi.tutup', $absensi->id_absensi) }}" method="POST" class="d-none">
                            @csrf
                        </form>

                        <div class="bg-white p-3 border rounded shadow-sm d-inline-block mb-4">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ $absensi->qr_token }}"
                                 alt="QR Code Absensi" 
                                 style="width: 250px; height: 250px; object-fit: contain; display: block;">
                        </div>

                        <div class="w-100 px-3">
                            <div class="alert alert-dark border-0 bg-lighter text-center mb-0">
                                <small class="d-block text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem;">Token Sesi</small>
                                <code class="fw-bold fs-5 text-primary" style="word-break: break-all;">{{ $absensi->qr_token }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7 mb-4">
                <div class="card shadow-none border h-100">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold"><i class='bx bx-list-check me-2'></i>Daftar Kehadiran Siswa</h5>
                        <span class="badge bg-info">{{ $absensi->kelas->nama_kelas }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-3">No</th>
                                        <th class="py-3">NIS</th>
                                        <th class="py-3">Nama Siswa</th>
                                        <th class="py-3">Waktu Scan</th>
                                        <th class="py-3 text-center">Status</th>
                                        <th class="py-3">Keterangan</th>
                                        <th class="py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($absensi->details as $index => $detail)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $detail->siswa->nis }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs me-2">
                                                         <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($detail->siswa->nama_siswa, 0, 1) }}</span>
                                                    </div>
                                                    <span class="fw-semibold text-dark">{{ $detail->siswa->nama_siswa }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $detail->waktu_scan ? $detail->waktu_scan->format('H:i:s') : '-' }}</td>
                                            <td class="text-center">
                                                @if($detail->status == 'hadir')
                                                    <span class="badge bg-success">Hadir</span>
                                                @elseif($detail->status == 'sakit')
                                                    <span class="badge bg-warning">Sakit</span>
                                                @elseif($detail->status == 'izin')
                                                    <span class="badge bg-info">Izin</span>
                                                @elseif($detail->status == 'dispen')
                                                    <span class="badge bg-primary">Dispen</span>
                                                @else
                                                    <span class="badge bg-danger">Alpha</span>
                                                @endif
                                            </td>
                                            <td>{{ $detail->keterangan ?? '-' }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $detail->id_detail }}">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                
                                                {{-- Modal Edit --}}
                                                <div class="modal fade" id="modalEdit{{ $detail->id_detail }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('absensi.detail.update', $detail->id_detail) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <div class="modal-content text-start">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Kehadiran: {{ $detail->siswa->nama_siswa }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                     <div class="mb-3">
                                                                        <label class="form-label">Status Kehadiran</label>
                                                                        <select name="status" class="form-select">
                                                                            <option value="hadir" {{ $detail->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                                            <option value="sakit" {{ $detail->status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                                            <option value="izin" {{ $detail->status == 'izin' ? 'selected' : '' }}>Izin</option>
                                                                            <option value="dispen" {{ $detail->status == 'dispen' ? 'selected' : '' }}>Dispensasi</option>
                                                                            <option value="alpha" {{ $detail->status == 'alpha' ? 'selected' : '' }}>Alpha</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Keterangan (Opsional)</label>
                                                                        <textarea name="keterangan" class="form-control" rows="2">{{ $detail->keterangan }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <img src="{{ asset('assets/img/illustrations/empty-state.png') }}" alt="empty" width="100" class="mb-2 d-block mx-auto opacity-50">
                                                <span class="text-muted italic">Belum ada data siswa di kelas ini.</span>
                                            </td>
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
    // Data Waktu dari Server (Convert ke timestamp JS)
    const serverTime = new Date("{{ now() }}").getTime();
    const endTime = new Date("{{ $absensi->tanggal }} {{ $absensi->jam_selesai }}").getTime();
    let clientTimeOffset = new Date().getTime() - serverTime;

    function updateCountdown() {
        const now = new Date().getTime() - clientTimeOffset; // Waktu 'sekarang' yang disinkronkan dgn server
        const distance = endTime - now;

        if (distance < 0) {
            // Waktu habis: Auto-submit form tutup sesi
            clearInterval(timerInterval);
            document.getElementById('countdown-timer').innerHTML = "WAKTU HABIS";
            document.getElementById('form-tutup-sesi').submit();
            return;
        }

        // Hitung jam, menit, detik
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Tampilkan
        document.getElementById('countdown-timer').innerHTML = 
            (hours < 10 ? "0" + hours : hours) + ":" + 
            (minutes < 10 ? "0" + minutes : minutes) + ":" + 
            (seconds < 10 ? "0" + seconds : seconds);
    }

    const timerInterval = setInterval(updateCountdown, 1000);
    updateCountdown(); // Jalankan langsung sekali agar tidak delay 1 detik

    // Refresh halaman setiap 10 detik untk update data siswa (opsional, biar tidak terlalu sering reload)
    setInterval(() => {
        // Cek dulu apakah user sedang scroll atau interaksi, kalau tidak baru reload?
        // Untuk sekarang reload simple aja tapi interval diperlama
        if(navigator.onLine) {
           // window.location.reload(); 
           // Opsional: disable reload full page jika ingin countdown mulus, 
           // ganti dengan fetch partial refresh jika mau lebih canggih.
           // Tapi sesuai request awal: "refresh otomatis" dipertahankan tapi timer jalan terus?
           // Kalo reload full page, timer akan reset.
           // SOLUSI: Reload halaman akan mereset timer visual, TAPI logika waktu tetap valid karena base-nya jam server.
        }
    }, 10000);

    // Agar data kehadiran siswa tetap update tanpa ganggu timer, 
    // idealnya pakai AJAX. Tapi untuk "mvp", reload page setiap 10-15 detik oke lah.
    // Timer akan "kedip" setiap reload, tapi fungsinya aman.
    setInterval(function() {
         window.location.reload();
    }, 15000); // Perlama jadi 15 detik biar timer kelihatan jalan
</script>
@endpush