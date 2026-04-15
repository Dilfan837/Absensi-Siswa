@extends('layouts.app')

@section('title', 'Penilaian Guru')

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
        <div class="card shadow border-0 bg-primary text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-white mb-1">Evaluasi Kinerja Guru</h4>
                    <p class="mb-0">Periode Penilaian: <strong>{{ $currentPeriod }}</strong></p>
                </div>
                <div class="bg-white text-primary px-3 py-2 rounded shadow-sm fw-bold">
                    {{ count($evaluatedUserIds) }} / {{ count($gurus) }} Dinilai
                </div>
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

<div class="row g-4">
    @foreach($gurus as $guru)
    @php
        $isEvaluated = in_array($guru->id_user, $evaluatedUserIds);
    @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm {{ $isEvaluated ? 'border-success' : 'border-primary' }}">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mx-auto mb-3">
                    @if($guru->photo && file_exists(public_path('storage/photos/'.$guru->photo)))
                        <img src="{{ asset('storage/photos/'.$guru->photo) }}" alt="Avatar" class="rounded-circle" style="object-fit: cover; width: 80px; height: 80px;">
                    @else
                        <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle" width="80" height="80">
                    @endif
                </div>
                <h5 class="mb-1">{{ $guru->nama }}</h5>
                <p class="text-muted mb-2">{{ $guru->mataPelajaran->nama_mapel ?? 'Belum ada Mapel' }}</p>

                <div class="mt-4">
                    @if($isEvaluated)
                        <button class="btn btn-success w-100 disabled">
                            <i class="bx bx-check-circle me-1"></i> Sudah Dinilai
                        </button>
                    @else
                        @if(count($categories) > 0)
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalNilai{{ $guru->id_guru }}">
                                <i class="bx bx-star me-1"></i> Beri Penilaian
                            </button>
                        @else
                            <button class="btn btn-secondary w-100 disabled" title="Kategori penilaian guru belum diatur">
                                <i class="bx bx-error me-1"></i> Kategori Kosong
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Penilaian -->
    @if(!$isEvaluated && count($categories) > 0)
    <div class="modal fade" id="modalNilai{{ $guru->id_guru }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Penilaian: {{ $guru->nama }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.assessments.guru.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="evaluatee_id" value="{{ $guru->id_user }}">
                    <input type="hidden" name="period" value="{{ $currentPeriod }}">
                    
                    <div class="modal-body">
                        <div class="alert alert-info py-2">
                            <small><i class="bx bx-info-circle"></i> Berikan rating 1 (Sangat Kurang) hingga 5 (Sangat Baik) untuk setiap indikator kerja.</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    @foreach($categories as $category)
                                    <tr class="border-bottom">
                                        <td class="align-middle" style="width: 50%">
                                            <h6 class="mb-1">{{ $category->name }}</h6>
                                            <p class="text-muted small mb-0">{{ $category->description }}</p>
                                        </td>
                                        <td class="align-middle text-end" style="width: 50%">
                                            <div class="star-rating d-inline-flex">
                                                <input type="radio" id="star5_{{ $guru->id_guru }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="5" required />
                                                <label for="star5_{{ $guru->id_guru }}_{{ $category->id }}" title="5 - Sangat Baik">&#9733;</label>
                                                
                                                <input type="radio" id="star4_{{ $guru->id_guru }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="4" />
                                                <label for="star4_{{ $guru->id_guru }}_{{ $category->id }}" title="4 - Baik">&#9733;</label>
                                                
                                                <input type="radio" id="star3_{{ $guru->id_guru }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="3" />
                                                <label for="star3_{{ $guru->id_guru }}_{{ $category->id }}" title="3 - Cukup">&#9733;</label>
                                                
                                                <input type="radio" id="star2_{{ $guru->id_guru }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="2" />
                                                <label for="star2_{{ $guru->id_guru }}_{{ $category->id }}" title="2 - Kurang">&#9733;</label>
                                                
                                                <input type="radio" id="star1_{{ $guru->id_guru }}_{{ $category->id }}" name="scores[{{ $category->id }}]" value="1" />
                                                <label for="star1_{{ $guru->id_guru }}_{{ $category->id }}" title="1 - Sangat Kurang">&#9733;</label>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Catatan / Feedback Tambahan (Opsional)</label>
                            <textarea name="general_notes" class="form-control" rows="3" placeholder="Tuliskan apresiasi atau saran perbaikan untuk guru ini..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    @if(count($gurus) == 0)
    <div class="col-12 text-center py-5">
        <div class="text-muted">
            <i class="bx bx-user-x" style="font-size: 4rem;"></i>
            <h5 class="mt-3">Belum ada data Guru Aktif</h5>
        </div>
    </div>
    @endif
</div>
@endsection
