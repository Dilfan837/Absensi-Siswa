@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
<div class="card shadow border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-white"><i class="bx bx-store me-2"></i> Marketplace Kelonggaran</h5>
        <button type="button" class="btn btn-light btn-sm fw-bold text-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bx bx-plus-circle"></i> Tambah Item
        </button>
    </div>
    <div class="card-body">
                    <div class="table-responsive p-0">
                        <table class="table table-hover table-striped border mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Token</th>
                                    <th>Sifat Penggunaan</th>
                                    <th class="text-center">Harga Poin</th>
                                    <th class="text-center">Batas /Bulan</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $i)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-receipt fs-3 me-3 text-warning"></i>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 text-sm fw-bold">{{ $i->item_name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ Str::limit($i->description, 40) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs fw-bold mb-0">{{ $i->item_type_label }}</p>
                                        @if($i->requires_active_session)
                                            <span class="badge bg-danger mt-1">Sesi Aktif</span>
                                        @else
                                            <span class="badge bg-info mt-1">Bisa Kapan Saja</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center fw-bold text-warning">
                                        {{ $i->point_cost }} 🪙
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        {{ $i->stock_limit ? $i->stock_limit . 'x' : 'Tak Terbatas' }}
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($i->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{$i->id}}"><i class="bx bx-edit text-sm me-1"></i> Edit</button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal{{$i->id}}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title text-white">Edit Item {{ $i->item_name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.poin.marketplace.update', $i->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Nama Token <span class="text-danger">*</span></label>
                                                        <input type="text" name="item_name" class="form-control border px-3" value="{{ $i->item_name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Deskripsi</label>
                                                        <textarea name="description" class="form-control border px-3" rows="2">{{ $i->description }}</textarea>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Harga Poin / Koin <span class="text-danger">*</span></label>
                                                                <input type="number" name="point_cost" class="form-control border px-3" value="{{ $i->point_cost }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Limit Beli /Bulan</label>
                                                                <input type="number" name="stock_limit" class="form-control border px-3" value="{{ $i->stock_limit }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Status</label>
                                                        <select name="is_active" class="form-control border px-3 w-100">
                                                            <option value="1" {{ $i->is_active ? 'selected' : '' }}>Aktif</option>
                                                            <option value="0" {{ !$i->is_active ? 'selected' : '' }}>Nonaktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning text-dark fw-bold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr><td colspan="6" class="text-center py-4">Belum ada item di marketplace.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">Tambah Kelonggaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.poin.marketplace.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Token / Hadiah <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control border px-3" placeholder="Contoh: Bebas Alpha 1x atau WFH" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Token <span class="text-danger">*</span></label>
                        <select name="item_type" class="form-control border px-3 w-100" required>
                            <option value="" disabled selected>-- Pilih Tipe --</option>
                            <option value="BEBAS_ALPHA">Bebas Alpha (Langsung pakai di sesi aktif)</option>
                            <option value="WFH">WFH / Remote</option>
                            <option value="IZIN_MENDADAK">Izin Mendadak</option>
                            <option value="TOLERANSI_TELAT">Toleransi Telat</option>
                            <option value="CUSTOM">Custom / Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi/Syarat</label>
                        <textarea name="description" class="form-control border px-3" rows="2" placeholder="Jelaskan kegunaan token..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Harga Poin / Koin <span class="text-danger">*</span></label>
                                <input type="number" name="point_cost" class="form-control border px-3" placeholder="cth: 30" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Limit per Siswa /Bulan</label>
                                <input type="number" name="stock_limit" class="form-control border px-3" placeholder="Kosong = bebas">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Buat Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
