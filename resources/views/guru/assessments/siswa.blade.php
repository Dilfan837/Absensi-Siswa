@extends('layouts.app')

@section('title', 'Penilaian Siswa')

@section('content')
<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }
    .star-rating input {
        display: none;
    }
    .star-rating label {
        color: #ccc;
        font-size: 2rem;
        padding: 0 0.1rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #ffc107;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 bg-success text-white">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="mb-3 mb-md-0">
                    <h4 class="text-white mb-1">Evaluasi Sikap Siswa</h4>
                    <p class="mb-0">Periode Penilaian: <strong>{{ $currentPeriod }}</strong></p>
                </div>
                
                <form action="{{ route('guru.assessments.siswa') }}" method="GET" class="d-flex align-items-center" id="filterForm">
                    <label class="me-2 fw-bold text-white mb-0">Pilih Kelas:</label>
                    <select name="kelas_id" class="form-select border-0 shadow-sm" style="min-width: 200px;" onchange="document.getElementById('filterForm').submit()">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}" {{ $selectedKelasId == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} {{ $k->jurusan->nama_jurusan ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary">
                Daftar Siswa 
                <span class="badge bg-primary ms-2">{{ count($siswas) }} Siswa</span>
            </h5>
            <div class="badge bg-label-success px-3 py-2" style="font-size: 0.9rem;">
                Progress: {{ count($evaluatedUserIds) }} / {{ count($siswas) }} Selesai
            </div>
        </div>
        <hr>
    </div>
</div>

<div class="row g-4">
    @foreach($siswas as $siswa)
    @php
        $isEvaluated = in_array($siswa->id_user, $evaluatedUserIds);
    @endphp
    <div class="col-md-6 col-lg-3 col-sm-6">
        <div class="card h-100 shadow-sm {{ $isEvaluated ? 'border-success' : '' }}">
            <div class="card-body text-center p-3">
                <div class="avatar avatar-lg mx-auto mb-2">
                    @if($siswa->foto && file_exists(public_path('storage/photos/'.$siswa->foto)))
                        <img src="{{ asset('storage/photos/'.$siswa->foto) }}" alt="Avatar" class="rounded-circle" style="object-fit: cover; width: 60px; height: 60px;">
                    @else
                        <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle" width="60" height="60">
                    @endif
                </div>
                <h6 class="mb-1 text-truncate" title="{{ $siswa->nama_siswa }}">{{ $siswa->nama_siswa }}</h6>
                <small class="text-muted d-block mb-3">NIS: {{ $siswa->nis }}</small>

                <div>
                    @if($isEvaluated)
                        <button class="btn btn-sm btn-success w-100 disabled">
                            <i class="bx bx-check me-1"></i> Selesai
                        </button>
                    @else
                        @if(count($categories) > 0)
                            <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalNilai{{ $siswa->id_siswa }}">
                                <i class="bx bx-star"></i> Nilai
                            </button>
                        @else
                            <button class="btn btn-sm btn-secondary w-100 disabled" title="Kategori penilaian guru belum diatur">
                                <i class="bx bx-error"></i> Kategori Kosong
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Penilaian Siswa -->
    @if(!$isEvaluated && count($categories) > 0)
    <div class="modal fade" id="modalNilai{{ $siswa->id_siswa }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">Nilai Sikap: {{ $siswa->nama_siswa }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('guru.assessments.siswa.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="evaluatee_id" value="{{ $siswa->id_user }}">
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                    <input type="hidden" name="period" value="{{ $currentPeriod }}">
                    
                    <div class="modal-body p-4">
                        <div class="alert alert-warning py-2 mb-4">
                            <small><i class="bx bx-bulb"></i> Silakan isi <strong>Star Rating (1-5)</strong> berdasarkan pengamatan terhadap perilaku siswa di poin-poin berikut.</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    @foreach($categories as $category)
                                    <tr class="border-bottom">
                                        <td class="align-middle py-3" style="width: 55%">
                                            <h6 class="mb-1 fw-bold">{{ $category->name }}</h6>
                                            <p class="text-muted small mb-0" style="line-height: 1.2;">{{ $category->description }}</p>
                                        </td>
                                        <td class="align-middle text-end py-3" style="width: 45%">
                                            <div class="star-rating d-inline-flex">
                                                <input type="radio" id="s5_{{ $siswa->id_siswa }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="5" required />
                                                <label for="s5_{{ $siswa->id_siswa }}_{{ $category->id }}" title="5 - Sangat Baik">&#9733;</label>
                                                
                                                <input type="radio" id="s4_{{ $siswa->id_siswa }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="4" />
                                                <label for="s4_{{ $siswa->id_siswa }}_{{ $category->id }}" title="4 - Baik">&#9733;</label>
                                                
                                                <input type="radio" id="s3_{{ $siswa->id_siswa }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="3" />
                                                <label for="s3_{{ $siswa->id_siswa }}_{{ $category->id }}" title="3 - Cukup">&#9733;</label>
                                                
                                                <input type="radio" id="s2_{{ $siswa->id_siswa }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="2" />
                                                <label for="s2_{{ $siswa->id_siswa }}_{{ $category->id }}" title="2 - Kurang">&#9733;</label>
                                                
                                                <input type="radio" id="s1_{{ $siswa->id_siswa }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="1" />
                                                <label for="s1_{{ $siswa->id_siswa }}_{{ $category->id }}" title="1 - Sangat Kurang">&#9733;</label>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <label class="form-label fw-bold">Catatan Perilaku Khusus (Opsional)</label>
                            <textarea name="general_notes" class="form-control" rows="2" placeholder="Tuliskan catatan khusus terkait siswa ini (jika ada)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <!-- Fast grading UX: "Simpan" defaults to taking them back to grid to click next, which is optimal for bulk -->
                        <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Simpan Penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    @if(count($siswas) == 0)
    <div class="col-12 text-center py-5">
        <div class="text-muted">
            <i class="bx bx-group" style="font-size: 4rem;"></i>
            <h5 class="mt-3">Belum ada data Siswa di Kelas ini</h5>
        </div>
    </div>
    @endif
</div>
@endsection
