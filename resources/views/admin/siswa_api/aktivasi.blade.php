@extends('layouts.app')

@section('title', 'Pendaftaran Wajah & Fiksasi Siswa API')

@section('content')
<div class="container-fluid pt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Aktivasi Data & Pendaftaran Wajah</h4>
            <p class="text-muted small">Mensinkronkan draf siswa <strong>{{ $draft->nama }}</strong> dari API menjadi Siswa resmi berakun.</p>
        </div>
        <a href="{{ route('admin.api_siswa.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Draft
        </a>
    </div>

    @if($existingSiswa)
        <div class="alert alert-warning">
            <i class="bx bx-info-circle me-2"></i> <strong>Peringatan Info:</strong> Siswa dengan NIS <b>{{ $draft->no_induk }}</b> sudah ada di Database Siswa manual (Belum punya rekaman wajah). Fiksasi ini akan <b>Mengupdate</b> (menyatukan) data manual dengan data dari API, bukan membuat ganda.
        </div>
    @endif

    <div class="row">
        <!-- DETAIL DATA API -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0 card-title">Biodata dari API (Terkunci)</h5>
                </div>
                <div class="card-body pt-3">
                    <table class="table table-borderless table-sm">
                        <tr><td width="30%" class="text-muted">Nama Lengkap</td><td>: <strong>{{ $draft->nama }}</strong></td></tr>
                        <tr><td class="text-muted">NIS / No Induk</td><td>: {{ $draft->no_induk }}</td></tr>
                        <tr><td class="text-muted">NISN</td><td>: {{ $draft->nisn }}</td></tr>
                        <tr><td class="text-muted">Jenis Kelamin</td><td>: {{ $draft->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }}</td></tr>
                        <tr><td class="text-muted">TTL</td><td>: {{ $draft->tempat_lahir }}, {{ $draft->tanggal_lahir }}</td></tr>
                        <tr><td class="text-muted">Agama ID</td><td>: {{ $draft->agama_id }}</td></tr>
                        <tr><td class="text-muted">Rombel API</td><td>: <span class="badge bg-secondary">{{ $draft->nama_rombel ?? '-' }}</span></td></tr>
                        <tr><td class="text-muted">No Telepon</td><td>: {{ $draft->no_telp ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Alamat</td><td>: {{ $draft->alamat ?? '-' }}, RT {{ $draft->rt ?? '-' }} / RW {{ $draft->rw ?? '-' }}, {{ $draft->desa_kelurahan ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Nama Ayah/Ibu</td><td>: {{ $draft->nama_ayah ?? '-' }} / {{ $draft->nama_ibu ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- FORM FIKSASI -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow border-0 border-top border-primary border-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary"><i class="bx bx-check-shield me-2"></i>Tindakan Fiksasi</h5>
                </div>
                <form action="{{ route('admin.api_siswa.aktivasi.store', $draft->id) }}" method="POST" id="formFiksasi">
                    @csrf
                    <div class="card-body">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Kelas/Rombel Valid di Sistem <span class="text-danger">*</span></label>
                            <select name="id_kelas" class="form-select select2" required>
                                <option value="">-- Tentukan Kelas Sebenarnya --</option>
                                @foreach($list_kelas as $k)
                                    <option value="{{ $k->id_kelas }}" {{ ($existingSiswa && $existingSiswa->id_kelas == $k->id_kelas) ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }} ({{ $k->jurusan->kode_jurusan ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih kelas di database lokal untuk menyatukan relasi diabsen.</small>
                        </div>

                        <label class="form-label fw-bold mt-2">Daftarkan Wajah Siswa <span class="text-danger">*</span></label>
                        <div class="text-center rounded" style="background:#f8f9fa; padding:15px; border:1px dashed #ccc;">
                            {{-- Peringatan Auto-Capture --}}
                            <p class="small text-muted mb-2" id="camera-status-text">Arahkan seluruh wajah ke dalam bingkai putus-putus. Sistem akan memfoto otomatis.</p>

                            {{-- Area Kamera & Overlay Guide --}}
                            <div class="video-container mx-auto mb-2 position-relative" style="width: 250px; height: 250px; background-color: #000; border-radius: 12px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 3px solid #eee;">
                                <video id="webcam" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                                
                                {{-- Bingkai Pemandu Wajah (Oval/Bulat) --}}
                                <div id="face-guide" style="position: absolute; top: 15%; left: 20%; width: 60%; height: 70%; border: 3px dashed #dc3545; border-radius: 50%; box-shadow: 0 0 0 9999px rgba(0,0,0,0.4); pointer-events: none; transition: border-color 0.3s;"></div>
                            </div>

                            <button type="button" class="btn btn-sm btn-secondary text-white mb-2" onclick="takeSnapshot()" id="btnManualSnap">
                                <i class="bx bx-camera me-1"></i> Jepret Manual Darurat
                            </button>

                            {{-- Area Hasil Foto --}}
                            <div id="results" style="display:none;" class="mt-2 text-center">
                                <img id="prev-img" src="" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border:2px solid #28a745;">
                                <p class="text-success small fw-bold mt-1 mb-0"><i class="bx bx-check-circle"></i> Wajah Siap Disimpan!</p>
                            </div>

                            {{-- Form Field Wajah Hidden --}}
                            <input type="hidden" name="image_data" id="image_data" required>
                            <input type="hidden" name="face_descriptor" id="face_descriptor" required>
                            <canvas id="canvas" style="display:none;"></canvas>
                        </div>
                    </div>
                    <div class="card-footer text-end mt-2">
                        <button type="submit" class="btn btn-primary" id="btnSubmit" onclick="return confirm('Wajah & Data akan disimpan menetap ke dalam Database Asli. Apakah Anda yakin?')">
                            <i class="bx bx-save me-1"></i> Simpan Wajah & Aktifkan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Load Face API js --}}
    <script src="{{ asset('assets/js/face-api.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const imageDataInput = document.getElementById('image_data');
        const faceDescriptorInput = document.getElementById('face_descriptor');
        const prevImg = document.getElementById('prev-img');
        const resultsDiv = document.getElementById('results');
        
        let streamActive = null;
        let modelsLoaded = false;

        async function loadModels() {
            const MODEL_URL = window.location.origin + '/models';
            try {
                // Tampilkan loading saat load model
                Swal.fire({
                    title: 'Memuat AI Wajah...',
                    text: 'Harap tunggu.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                modelsLoaded = true;
                Swal.close();
                console.log("Face API Models Loaded");

                // Start Camera Auto
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: true }).then(function (stream) {
                        streamActive = stream;
                        video.srcObject = stream;
                        video.play();
                    }).catch(err => {
                        Swal.fire('Error', 'Gagal akses kamera: ' + err.message, 'error');
                    });
                }

            } catch (err) {
                console.error("Gagal memuat model:", err);
                Swal.fire('Error', 'Gagal memuat model AI. Refresh halaman.', 'error');
            }
        }
        
        // Mulai load saat DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            loadModels();
        });

        // Fitur Auto Capture Wajah
        let autoCaptureInterval = null;
        let scanStatusTxt = document.getElementById('camera-status-text');
        const guideFrame = document.getElementById('face-guide');

        video.addEventListener('playing', () => {
            // Gunakan interval untuk auto scan wajah setiap 500ms
            autoCaptureInterval = setInterval(async () => {
                if (!modelsLoaded || prevImg.src.length > 100) return; // Stop if already snapped

                // Buat canvas sementara untuk scan
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = video.videoWidth;
                tempCanvas.height = video.videoHeight;
                const context = tempCanvas.getContext('2d');
                // Mirror gambar video ke canvas untuk scan
                context.translate(tempCanvas.width, 0);
                context.scale(-1, 1);
                context.drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);

                // Deteksi wajah cepat (tanpa landmark kalau cuma butuh position, 
                // tapi kita butuh descriptor nnti kalau sukses, jadi biar bareng)
                const detection = await faceapi.detectSingleFace(tempCanvas, new faceapi.SsdMobilenetv1Options({
                    minConfidence: 0.6 // Harus lumayan yakin
                }));

                if (detection) {
                    const box = detection.box;
                    // Hitung rasio ukuran kotak wajah terhadap video
                    const faceArea = box.width * box.height;
                    const frameArea = tempCanvas.width * tempCanvas.height;
                    const ratio = faceArea / frameArea;

                    if (ratio > 0.15 && ratio < 0.60) {
                        // Terdeteksi wajah dengan jarak presisi
                        guideFrame.style.borderColor = '#28a745'; // Hijau
                        scanStatusTxt.innerHTML = '<span class="text-success fw-bold">Posisi pas! Mengunci wajah...</span>';
                        
                        // Stop looping
                        clearInterval(autoCaptureInterval);
                        
                        // Bunyikan simulasi shutter
                        // Beri delay dikit agar user sempat senyum
                        setTimeout(() => {
                            takeAutoSnapshot(detection, tempCanvas);
                        }, 500);

                    } else if (ratio < 0.15) {
                        guideFrame.style.borderColor = '#ffc107'; // Kuning
                        scanStatusTxt.innerHTML = '<span class="text-warning">Maju sedikit, wajah terlalu jauh.</span>';
                    } else {
                        guideFrame.style.borderColor = '#ffc107'; // Kuning
                        scanStatusTxt.innerHTML = '<span class="text-warning">Mundur sedikit, wajah terlalu dekat.</span>';
                    }
                } else {
                    guideFrame.style.borderColor = '#dc3545'; // Merah
                    scanStatusTxt.innerHTML = '<span class="text-danger">Wajah belum terdeteksi sempurna. Terangkan cahaya.</span>';
                }
            }, 600); // deteksi setiap 600ms
        });

        // Pastikan interval dimatikan saat pindah halaman
        window.addEventListener("beforeunload", function (e) {
            if (autoCaptureInterval) clearInterval(autoCaptureInterval);
            if (streamActive) {
                streamActive.getTracks().forEach(track => track.stop());
            }
        });

        async function takeAutoSnapshot(detectionData, originalCanvas) {
            // Kita sudah punya canvas gambar asli dan kotak detection box.
            // Kita hitung landmark descriptor sekarang
            Swal.fire({
                title: 'Jepret!',
                text: 'Memproses Biometrik...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Ekstrak data biometrik untuk wajah yg ada di frame (jika belum dilampirkan)
            const fullDetection = await faceapi.detectSingleFace(originalCanvas, new faceapi.SsdMobilenetv1Options())
                                                .withFaceLandmarks().withFaceDescriptor();

            if(!fullDetection){
                Swal.fire('Oops', 'Pergerakan terlalu cepat. Ulangi!', 'warning');
                // Restart autobot
                guideFrame.style.borderColor = '#dc3545';
                loadModels(); // trigger ulang video stream/interval jika butuh, but we can just reload page
                return;
            }

            // Gunting frame menjadi kotak seperti `takeSnapshot` manual
            processAndSaveImage(fullDetection, originalCanvas);
        }

        function processAndSaveImage(fullDetection, baseCanvas) {
            const context = canvas.getContext('2d');
            const size = 400; 
            canvas.width = size;
            canvas.height = size;
            
            // Logika Crop tengah
            const videoWidth = baseCanvas.width;
            const videoHeight = baseCanvas.height;
            const aspectRatio = videoWidth / videoHeight;

            let sourceX, sourceY, sourceWidth, sourceHeight;
            if (aspectRatio > 1) { 
                sourceHeight = videoHeight;
                sourceWidth = videoHeight;
                sourceX = (videoWidth - videoHeight) / 2;
                sourceY = 0;
            } else { 
                sourceWidth = videoWidth;
                sourceHeight = videoWidth;
                sourceX = 0;
                sourceY = (videoHeight - videoWidth) / 2;
            }

            // Draw center cropped image ke final canvas
            context.drawImage(baseCanvas, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, size, size);

            Swal.close();

            // Simpan Data
            const dataURL = canvas.toDataURL('image/jpeg', 0.9);
            imageDataInput.value = dataURL;
            const descriptorArray = Array.from(fullDetection.descriptor);
            faceDescriptorInput.value = JSON.stringify(descriptorArray);

            // Preview Visual
            prevImg.src = dataURL;
            resultsDiv.style.display = 'block';
            document.getElementById('btnManualSnap').style.display = 'none'; // Sembunyikan manual btn

            Swal.fire({
                icon: 'success',
                title: 'Wajah Terkunci Otomatis!',
                text: 'Berhasil. Data wajah siap disimpan!',
                timer: 1500,
                showConfirmButton: false
            });
        }

        async function takeSnapshot() {
            if (autoCaptureInterval) clearInterval(autoCaptureInterval); // Matikan auto saat klik manual
            if (!modelsLoaded) {
                Swal.fire('Sabar...', 'Model AI belum selesai dimuat.', 'warning');
                return;
            }

            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = video.videoWidth;
            tempCanvas.height = video.videoHeight;
            const context1 = tempCanvas.getContext('2d');
            context1.translate(tempCanvas.width, 0);
            context1.scale(-1, 1); 
            context1.drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);

            Swal.fire({
                title: 'Mendeteksi Wajah...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const detection = await faceapi.detectSingleFace(tempCanvas, new faceapi.SsdMobilenetv1Options({
                minConfidence: 0.5
            })).withFaceLandmarks().withFaceDescriptor();

            if (!detection) {
                Swal.close();
                Swal.fire({
                    icon: 'warning',
                    title: 'Wajah Tidak Terdeteksi',
                    text: 'Pastikan wajah berada di tengah bingkai & tidak memakai masker.',
                    confirmButtonColor: '#ff3e1d'
                });
                return; 
            }
            
            processAndSaveImage(detection, tempCanvas);
        }

        // Cek input sebelum submit form fiksasi
        document.getElementById('formFiksasi').addEventListener('submit', function(e) {
            if(!imageDataInput.value || !faceDescriptorInput.value) {
                e.preventDefault();
                Swal.fire('Oops!', 'Anda harus menjepret wajah terlebih dahulu!', 'warning');
            }
        });
    </script>
@endpush
