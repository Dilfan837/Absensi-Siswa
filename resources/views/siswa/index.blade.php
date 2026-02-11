@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
    {{-- Notifikasi Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white">Data Siswa</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
                <i class="bx bx-user-plus me-1"></i> Tambah Siswa
            </button>
        </div>

        <div class="card-body">
            {{-- Form Pencarian --}}
            <form action="{{ route('siswa.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cari Siswa</label>
                        <input type="text" name="q" class="form-control" placeholder="Cari NIS atau Nama..." value="{{ request('q') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Filter Kelas</label>
                        <select name="kelas" class="form-select">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($list_kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('kelas') == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Filter Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">-- Semua Gender --</option>
                            <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 me-2"><i class="bx bx-search"></i> Cari</button>
                        <a href="{{ route('siswa.index') }}" class="btn btn-secondary w-100"><i class="bx bx-refresh"></i> Reset</a>
                    </div>
                </div>
            </form>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>L/P</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($siswas as $s)
                            <tr>
                                <td>
                                    @if($s->foto)
                                        <img src="{{ asset('storage/siswa/' . $s->foto) }}" width="40" class="rounded-circle">
                                    @else
                                        <div class="avatar avatar-sm bg-secondary rounded-circle"></div>
                                    @endif
                                </td>
                                <td>{{ $s->nis }}</td>
                                <td><strong>{{ $s->nama_siswa }}</strong></td>
                                <td>{{ $s->kelas->nama_kelas ?? 'N/A' }}</td>
                                <td>{{ $s->jenis_kelamin }}</td>
                                <td>
                                    <form action="{{ route('siswa.destroy', $s->id_siswa) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Data siswa belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Siswa --}}
    <div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Siswa Baru & Pendaftaran Wajah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('siswa.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            {{-- SISI KIRI: Input Data Diri --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">NIS</label>
                                    <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" name="nama_siswa" class="form-control" placeholder="Nama Lengkap"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kelas</label>
                                    <select name="id_kelas" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($list_kelas as $k)
                                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-select" required>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>

                            {{-- SISI KANAN: Input Kamera Kotak --}}
                            <div class="col-md-6 text-center border-start">
                                <label class="form-label fw-bold d-block">Ambil Dataset Wajah</label>

                                {{-- Container Video Kotak --}}
                                <div class="video-container mx-auto">
                                    <video id="webcam" autoplay playsinline></video>
                                </div>

                                <button type="button" class="btn btn-sm btn-info text-white mb-3" onclick="takeSnapshot()">
                                    <i class="bx bx-camera me-1"></i> Capture Wajah
                                </button>

                                {{-- Area Hasil Foto --}}
                                <div id="results" style="display:none;" class="mt-2">
                                    <p class="small fw-bold mb-1">Hasil Jepretan:</p>
                                    <img id="prev-img" src="" class="img-thumbnail mb-1">
                                    <p class="text-success small fw-bold mb-0"><i class="bx bx-check-circle"></i> Wajah
                                        Siap!</p>
                                </div>

                                {{-- Data Hidden --}}
                                <input type="hidden" name="image_data" id="image_data" required>
                                <input type="hidden" name="face_descriptor" id="face_descriptor">
                                <canvas id="canvas" style="display:none;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Siswa & Wajah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Mengatur tampilan kamera agar kotak sempurna di tengah */
        .video-container {
            width: 220px;
            height: 220px;
            background-color: #000;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 3px solid #eee;
            margin-bottom: 10px;
        }

        #webcam {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Crop otomatis sisi landscape agar jadi kotak */
            transform: scaleX(-1);
            /* Efek Cermin saat live agar tidak bingung */
        }

        #prev-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
@endsection

{{-- Script ditaruh di dalam @push atau di bawah @endsection asalkan layout.app memanggilnya --}}
@push('scripts')
    {{-- Load Library --}}
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

        // Load Models saat halaman dibuka (agar siap saat modal muncul)
        async function loadModels() {
            const MODEL_URL = window.location.origin + '/models';
            try {
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                modelsLoaded = true;
                console.log("Face API Models Loaded");
            } catch (err) {
                console.error("Gagal memuat model:", err);
                Swal.fire('Error', 'Gagal memuat model AI. Refresh halaman.', 'error');
            }
        }
        loadModels();

        // Aktifkan Kamera saat Modal Terbuka
        const modalTambah = document.getElementById('modalTambahSiswa');
        modalTambah.addEventListener('shown.bs.modal', function () {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true }).then(function (stream) {
                    streamActive = stream;
                    video.srcObject = stream;
                    video.play();
                }).catch(err => {
                    Swal.fire('Error', 'Gagal akses kamera: ' + err.message, 'error');
                });
            }
        });

        // Matikan Kamera saat Modal Tertutup
        modalTambah.addEventListener('hidden.bs.modal', function () {
            if (streamActive) {
                streamActive.getTracks().forEach(track => track.stop());
            }
            // Reset form/preview jika perlu
            resultsDiv.style.display = 'none';
            prevImg.src = '';
            imageDataInput.value = '';
            faceDescriptorInput.value = '';
        });

        async function takeSnapshot() {
            if (!modelsLoaded) {
                Swal.fire('Loading...', 'Model AI sedang dimuat, tunggu sebentar...', 'info');
                return;
            }

            const context = canvas.getContext('2d');
            const size = 400; // Ukuran canvas (resolusi foto)
            canvas.width = size;
            canvas.height = size;

            // --- LOGIKA COPY & CROP VIDEO KE CANVAS ---
            context.translate(size, 0);
            context.scale(-1, 1); // Mirroring

            const videoWidth = video.videoWidth;
            const videoHeight = video.videoHeight;
            const aspectRatio = videoWidth / videoHeight;

            let sourceX, sourceY, sourceWidth, sourceHeight;

            if (aspectRatio > 1) { // Landscape
                sourceHeight = videoHeight;
                sourceWidth = videoHeight;
                sourceX = (videoWidth - videoHeight) / 2;
                sourceY = 0;
            } else { // Portrait
                sourceWidth = videoWidth;
                sourceHeight = videoWidth;
                sourceX = 0;
                sourceY = (videoHeight - videoWidth) / 2;
            }

            // Gambar frame video ke canvas
            context.drawImage(video, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, size, size);

            // --- DETEKSI WAJAH & HITUNG DESCRIPTOR ---
            // Kita deteksi dari element HTMLCanvas
            const detection = await faceapi.detectSingleFace(canvas, new faceapi.SsdMobilenetv1Options({
                minConfidence: 0.5
            })).withFaceLandmarks().withFaceDescriptor();

            if (!detection) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Wajah Tidak Terdeteksi',
                    text: 'Pastikan wajah terlihat jelas, pencahayaan cukup, dan tidak memakai masker.',
                    confirmButtonColor: '#ff3e1d'
                });
                return; // Jangan simpan kalau wajah tidak ketemu
            }

            // Jika wajah ketemu:
            // 1. Simpan Foto Base64
            const dataURL = canvas.toDataURL('image/jpeg', 0.9);
            imageDataInput.value = dataURL;

            // 2. Simpan Descriptor (Array Float32 -> JSON String)
            // detection.descriptor adalah Float32Array
            const descriptorArray = Array.from(detection.descriptor);
            faceDescriptorInput.value = JSON.stringify(descriptorArray);

            // 3. Tampilkan Preview
            prevImg.src = dataURL;
            resultsDiv.style.display = 'block';

            // Beri feedback sukses
            Swal.fire({
                icon: 'success',
                title: 'Wajah Terdeteksi!',
                text: 'Data wajah berhasil diambil. Silakan isi data lain dan simpan.',
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
@endpush