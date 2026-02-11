@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h4 class="mb-4">Konfirmasi Identitas: <strong>{{ $siswa->nama_siswa }}</strong></h4>

                <input type="hidden" id="csrf_token_absen" value="{{ csrf_token() }}">

                <div class="video-container">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="overlay"></canvas>
                    
                    <!-- Scanner Overlay Animation -->
                    <div class="scanner-overlay">
                        <div class="scanner-line"></div>
                        <div class="scanner-frame"></div>
                    </div>
                </div>

                <div id="loader" class="mt-3">
                    <div class="spinner-border text-info" role="status"></div>
                    <p id="loader-text">Menyiapkan Sistem AI...</p>
                </div>

                <div id="status-box" class="alert alert-secondary mt-3 d-none">
                    <span id="status-text">Mencari Wajah...</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/face-api.min.js') }}"></script>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('overlay');
        const statusText = document.getElementById('status-text');
        const loader = document.getElementById('loader');
        const statusBox = document.getElementById('status-box');

        let isSubmitted = false;
        let faceMatcher = null;
        let userLatitude = null;
        let userLongitude = null;

        // Display Size Dinamis
        function getDisplaySize() {
            return { width: video.offsetWidth, height: video.offsetHeight };
        }

        async function init() {
            const MODEL_URL = window.location.origin + '/models';
            const loaderText = document.getElementById('loader-text');
            
            try {
                // 0. Ambil Lokasi GPS (jika fitur aktif)
                loaderText.textContent = 'Mencari lokasi Anda...';
                await getLocation();

                // 1. Start kamera
                loaderText.textContent = 'Mengakses kamera...';
                // Gunakan constraints ideal, browser akan cari yang paling mendekati
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    } 
                });
                video.srcObject = stream;
                
                // 2. Load Models
                loaderText.textContent = 'Memuat model AI...';
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);

                // 3. Setup Face Matcher (Cek apakah ada descriptor tersimpan?)
                loaderText.textContent = 'Menyiapkan data wajah...';
                
                // Ambil descriptor dari database (jika ada)
                const storedDescriptor = {!! json_encode($siswa->face_descriptor) !!};

                if (storedDescriptor) {
                    console.log("Using Stored Descriptor from DB");
                    const descriptorData = typeof storedDescriptor === 'string' ? JSON.parse(storedDescriptor) : storedDescriptor;
                    const descriptorFloat32 = new Float32Array(descriptorData);
                    
                    // Threshold diperketat jadi 0.45
                    faceMatcher = new faceapi.FaceMatcher(new faceapi.LabeledFaceDescriptors("{{ $siswa->nama_siswa }}", [descriptorFloat32]), 0.45);
                    
                    finishSetup();
                } else {
                    console.log("No Stored Descriptor. Fallback to Image Processing.");
                    const imgUrl = `{{ asset('storage/siswa/' . $siswa->foto) }}`;
                    const refImg = await faceapi.fetchImage(imgUrl);
                    await prepareFaceMatcherOriginal(refImg);
                }

            } catch (err) {
                console.error(err);
                statusText.innerHTML = "<span class='text-danger'>Gagal memuat sistem: " + err.message + "</span>";
                loader.classList.add('d-none');
                statusBox.classList.remove('d-none');
            }
        }

        function finishSetup() {
             video.onloadedmetadata = () => {
                video.play();
                const dims = getDisplaySize();
                faceapi.matchDimensions(canvas, dims);
                
                // Handle Resize
                window.addEventListener('resize', () => {
                    const newDims = getDisplaySize();
                    faceapi.matchDimensions(canvas, newDims);
                });
            };
            loader.classList.add('d-none');
            statusBox.classList.remove('d-none');
            startDetection(); // Mulai deteksi real-time
        }

        async function prepareFaceMatcherOriginal(img) {
            try {
                const detections = await faceapi.detectSingleFace(img, new faceapi.SsdMobilenetv1Options({
                    minConfidence: 0.5
                })).withFaceLandmarks().withFaceDescriptor();

                if (!detections) {
                    throw new Error("Wajah di foto profil tidak terdeteksi!");
                }

                faceMatcher = new faceapi.FaceMatcher(new faceapi.LabeledFaceDescriptors("{{ $siswa->nama_siswa }}", [detections.descriptor]), 0.45);
                finishSetup();

            } catch (e) {
                console.error(e);
                statusText.innerHTML = "<span class='text-danger'>Error: " + e.message + "</span>";
                loader.classList.add('d-none');
                statusBox.classList.remove('d-none');
            }
        }

        function startDetection() {
            const intervalId = setInterval(async () => {
                if (isSubmitted) {
                    clearInterval(intervalId);
                    return;
                }
                
                if (!faceMatcher || video.paused || video.ended || video.readyState !== 4) return;

                const detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({
                    minConfidence: 0.5
                })).withFaceLandmarks().withFaceDescriptor();

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detection) {
                    // Resize result sesuai ukuran TAMPILAN video saat ini
                    const currentDims = getDisplaySize();
                    const resized = faceapi.resizeResults(detection, currentDims);
                    
                    // Cek Kecocokan
                    const result = faceMatcher.findBestMatch(detection.descriptor);

                    const similarity = (1 - result.distance) * 100;
                    
                    if (result.label !== 'unknown') {
                        statusText.innerHTML = `<span class='text-success fw-bold'>Wajah Cocok! (${similarity.toFixed(1)}%) <br> Memproses...</span>`;
                        submitAbsensi();
                    } else {
                        statusText.innerHTML = `<span class='text-danger'>Wajah tidak dikenali! <br> (Kemiripan: ${similarity.toFixed(1)}% - Butuh > 55%)</span>`;
                    }
                } else {
                    statusText.innerHTML = "Mencari wajah...";
                }
            }, 100);
        }

        // === FUNGSI AMBIL LOKASI GPS (Keeping unchanged logic) ===
        function getLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    Swal.fire({
                        icon: 'error',
                        title: 'GPS Tidak Didukung',
                        text: 'Browser Anda tidak mendukung Geolocation.',
                        confirmButtonColor: '#d33'
                    });
                    reject('Geolocation not supported');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        userLatitude = position.coords.latitude;
                        userLongitude = position.coords.longitude;
                        console.log('Lokasi ditemukan:', userLatitude, userLongitude);
                        resolve();
                    },
                    (error) => {
                        // Error handling (sama seperti sebelumnya)
                         Swal.fire({ icon: 'error', title: 'Gagal Lokasi', text: 'Pastikan GPS aktif.' });
                        reject(error);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });
        }

        function submitAbsensi() {
            if (isSubmitted) return;
            isSubmitted = true;

            fetch("{{ route('absensi.simpan') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.getElementById('csrf_token_absen').value
                },
                body: JSON.stringify({ 
                    id_siswa: "{{ $siswa->id_siswa }}",
                    id_absensi: "{{ $absensi->id_absensi }}",
                    latitude: userLatitude,
                    longitude: userLongitude
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "{{ route('absensi.sukses') }}";
                    } else {
                        // Error handling logic
                         let icon = 'error';
                         if (data.message.includes('jangkauan')) icon = 'warning';
                         Swal.fire({ icon: icon, title: 'Absen Gagal', text: data.message });
                        isSubmitted = false;
                        statusText.innerHTML = "<span class='text-danger'>" + data.message + "</span>";
                    }
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi gagal.' });
                    isSubmitted = false;
                });
        }

        init();
    </script>
    <style>
        /* Responsive Video Container */
        .video-container {
            width: 100%;
            max-width: 480px;
            aspect-ratio: 4/3;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: #000;
        }

        #video, #overlay {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        #video {
            transform: scaleX(-1);
            -webkit-transform: scaleX(-1);
        }

        /* Scanner Container */
        .scanner-overlay {
            pointer-events: none;
            overflow: hidden;
            box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(0, 255, 255, 0.5);
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
        }

        .scanner-line {
            width: 100%;
            height: 3px;
            background: #00ffff;
            box-shadow: 0 0 10px #00ffff, 0 0 20px #00ffff;
            position: absolute;
            top: 0;
            left: 0;
            animation: scan 2s infinite linear;
        }

        @keyframes scan {
            0% { top: 0; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        .scanner-frame {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
        }
    </style>
@endsection