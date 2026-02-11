@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h5 class="mb-0 fw-bold">Presensi Mandiri Siswa</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($userSiswa) && $userSiswa)
                            <div class="alert alert-info mb-3">
                                <i class="bx bx-user me-1"></i> Halo, <strong>{{ $userSiswa->nama_siswa }}</strong>!
                                <br><small>Silakan scan QR Code untuk presensi.</small>
                            </div>
                        @else
                            <div class="mb-3" id="pilih-kelas-container">
                                <label class="form-label fw-bold">1. Pilih Kelas</label>
                                <select id="select-kelas" class="form-select">
                                    <option value="" selected disabled>-- Pilih Kelas --</option>
                                    @foreach($list_kelas as $k)
                                        <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
    
                            <div id="wrapper-siswa" class="mb-4" style="display:none;">
                                <label class="form-label fw-bold">2. Pilih Nama Anda</label>
                                <select id="select-siswa" class="form-select form-select-lg">
                                    <option value="" selected disabled>-- Loading Siswa... --</option>
                                </select>
                            </div>
                        @endif

                        <div id="scanner-section" style="@if(isset($userSiswa)) display:block; @else display: none; @endif">
                            <label class="form-label fw-bold text-danger">Arahkan Kamera ke QR Code</label>
                            <div id="reader"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const selectKelas = document.getElementById('select-kelas');
        const selectSiswa = document.getElementById('select-siswa');
        const wrapperSiswa = document.getElementById('wrapper-siswa');
        const scannerSection = document.getElementById('scanner-section');
        
        // Data Siswa Login (jika ada)
        const userSiswa = @json($userSiswa ?? null);

        let html5QrcodeScanner;

        // Init Scanner
        function initScanner() {
            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5QrcodeScanner("reader", {
                    fps: 15,
                    qrbox: 250,
                    disableFlip: true
                });
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        // Jika Siswa Login -> Langsung Init Scanner
        if (userSiswa) {
            initScanner();
        }

        // Logika Pilih Kelas -> Ambil Siswa (Hanya jika selectKelas ada)
        if (selectKelas) {
            selectKelas.addEventListener('change', function () {
                const idKelas = this.value;
                wrapperSiswa.style.display = 'block';
                scannerSection.style.display = 'none';
    
                fetch(`/get-siswa/${idKelas}`)
                    .then(res => res.json())
                    .then(data => {
                        selectSiswa.innerHTML = '<option value="" selected disabled>-- Pilih Nama --</option>';
                        data.forEach(siswa => {
                            selectSiswa.innerHTML += `<option value="${siswa.id_siswa}">${siswa.nama_siswa}</option>`;
                        });
                    });
            });
        }

        // Logika Pilih Nama -> Buka Kamera (Hanya jika selectSiswa ada)
        if (selectSiswa) {
            selectSiswa.addEventListener('change', function () {
                if (this.value) {
                    scannerSection.style.display = 'block';
                    initScanner();
                }
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Ambil ID Siswa: Prioritas User Login, fallback ke Select Manual
            const idSiswa = userSiswa ? userSiswa.id_siswa : (selectSiswa ? selectSiswa.value : null);

            if (!idSiswa) {
                Swal.fire('Error', 'Identitas siswa tidak ditemukan!', 'error');
                return;
            }

            // Matikan scanner agar tidak bentrok dengan kamera face recognition nanti
            html5QrcodeScanner.clear();

            fetch("{{ route('scan.proses') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ qr_token: decodedText, id_siswa: idSiswa })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // REDIRECT KE HALAMAN VERIFIKASI WAJAH
                        // Gunakan URL yang dikirim dari backend
                        window.location.href = data.redirect_url;
                    }
                    else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            confirmButtonColor: '#ff3e1d'
                        }).then(() => {
                            // Nyalakan kembali scanner jika gagal
                            location.reload();
                        });
                    }
                });
        }
        
        function onScanFailure(error) {
            // console.warn(`Code scan error = ${error}`);
        }
    </script>

    <style>
        /* Mengatasi mirroring dan styling reader */
        #reader video {
            transform: scaleX(-1) !important;
            border-radius: 8px;
            object-fit: cover !important;
        }

        #reader {
            border: none !important;
        }


    </style>
@endsection