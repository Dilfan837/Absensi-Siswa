@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0 fw-bold text-white">Scanner Absensi Mandiri</h5>
                    <small>Scan QR Code yang diberikan oleh Guru</small>
                </div>
                <div class="card-body mt-3">
                    <!-- Scanner Area -->
                    <div id="wrapper-scanner">
                        <div id="reader"></div>
                        <p class="text-center mt-3 text-muted">Arahkan kamera ke QR Code Guru</p>
                    </div>

                    <!-- Loading State -->
                    <div id="loading-scan" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memproses Absensi...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const scannerSection = document.getElementById('wrapper-scanner');
    const loadingSection = document.getElementById('loading-scan');
    let html5QrcodeScanner;

    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanner scanning
        html5QrcodeScanner.clear();
        
        // Show loading
        scannerSection.style.display = 'none';
        loadingSection.style.display = 'block';

        // Send to backend
        fetch("{{ route('siswa.proses-scan') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ qr_token: decodedText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonText: 'Kembali ke Dashboard',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('siswa.dashboard') }}";
                    }
                });
            }else if (data.status === 'info') {
                 Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: data.message,
                    confirmButtonText: 'Kembali ke Dashboard'
                }).then(() => {
                    window.location.href = "{{ route('siswa.dashboard') }}";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                    confirmButtonText: 'Coba Lagi'
                }).then(() => {
                    // Reload page to restart scanner
                    location.reload();
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan sistem. Silakan coba lagi.',
            }).then(() => {
                location.reload();
            });
        });
    }

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning.
        // console.warn(`Code scan error = ${error}`);
    }

    // Initialize Scanner
    document.addEventListener('DOMContentLoaded', function() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { 
                fps: 15, 
                qrbox: 250,
                disableFlip: true // Sesuai dengan yang jalan sebelumnya
            },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    });
</script>

<style>
    /* Styling for scanner video */
    #reader video {
        border-radius: 8px;
        object-fit: cover;
        /* Hapus transform karena disableFlip: true sudah menangani */
    }
    #reader {
        border: none !important;
    }
</style>
@endsection
