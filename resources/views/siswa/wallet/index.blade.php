@extends('layouts.app')

@push('css')
<style>
    .wallet-card {
        background: linear-gradient(135deg, #1A2980 0%, #26D0CE 100%);
        border-radius: 20px;
        color: white;
        overflow: hidden;
        position: relative;
    }
    .wallet-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        transform: rotate(45deg);
    }
    .balance-text {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    .level-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(5px);
    }
    .mutation-list {
        max-height: 400px;
        overflow-y: auto;
    }
    .mutation-item {
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }
    .mutation-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    .mutation-item.earn { border-left-color: #4CAF50; }
    .mutation-item.penalty { border-left-color: #F44336; }
    .mutation-item.spend { border-left-color: #FF9800; }
    
    .shop-card {
        transition: transform 0.3s;
        border: 2px solid transparent;
    }
    .shop-card:hover {
        transform: translateY(-5px);
        border-color: #26D0CE;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- HERO: DOMPET -->
    <div class="row mb-4">
        <div class="col-12 col-xl-8 mx-auto">
            <div class="wallet-card p-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-sm font-weight-bold mb-0 opacity-8 text-dark">Saldo Integritas</p>
                        <h1 class="balance-text text-dark mb-0 d-flex align-items-center">
                            {{ $pointBalance }} 
                            <span class="ms-2" style="font-size: 1.5rem">🪙 Pts</span>
                        </h1>
                    </div>
                    <div class="text-end">
                        <span class="level-badge border border-white">
                            {{ $levelInfo['icon'] }} Level: {{ $levelInfo['label'] }}
                        </span>
                        <p class="text-xs opacity-8 mt-2 mb-0">Total Transaksi: {{ $mutations->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white mx-auto col-xl-8" role="alert">
            <strong>Sukses!</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger text-white mx-auto col-xl-8" role="alert">
            <strong>Gagal!</strong> {{ session('error') }}
        </div>
    @endif

    <!-- TABS -->
    <div class="row">
        <div class="col-12 col-xl-8 mx-auto">
    <!-- TABS -->
    <div class="row">
        <div class="col-12 col-xl-8 mx-auto">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills mb-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#mutasi-tab" aria-controls="mutasi-tab" aria-selected="true">
                            <i class="bx bx-receipt me-1"></i> Riwayat
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#market-tab" aria-controls="market-tab" aria-selected="false">
                            <i class="bx bx-store me-1"></i> Marketplace
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#inventory-tab" aria-controls="inventory-tab" aria-selected="false">
                            <i class="bx bx-briefcase-alt me-1"></i> Inventory
                        </button>
                    </li>
                </ul>

            <div class="tab-content mt-3 p-0 bg-transparent shadow-none border-0">
                <!-- TAB 1: RIWAYAT -->
                <div class="tab-pane fade show active" id="mutasi-tab">
                    <div class="card shadow-sm border-0">
                        <div class="card-header border-bottom">
                            <h6 class="m-0 fw-bold">Riwayat Mutasi Poin</h6>
                        </div>
                        <div class="card-body p-3 mutation-list">
                            @forelse($mutations as $m)
                                <div class="mutation-item d-flex justify-content-between align-items-center p-3 mb-2 border rounded 
                                    {{ $m->amount > 0 ? 'earn bg-lighter' : ($m->transaction_type == 'SPEND' ? 'spend bg-lighter' : 'penalty bg-lighter') }}">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3 bg-label-{{ $m->amount > 0 ? 'success' : ($m->transaction_type == 'SPEND' ? 'warning' : 'danger') }} rounded-circle p-2 d-flex align-items-center justify-content-center">
                                            <i class="bx text-lg {{ $m->amount > 0 ? 'bx-trending-up' : ($m->transaction_type == 'SPEND' ? 'bx-cart' : 'bx-trending-down') }}"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-dark text-sm fw-bold">{{ $m->description }}</h6>
                                            <span class="text-xs text-muted"><i class="bx bx-time-five me-1"></i>{{ $m->created_at->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center text-sm font-weight-bold 
                                        {{ $m->amount > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $m->amount > 0 ? '+' : '' }}{{ $m->amount }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted my-4">
                                    <i class="bx bx-ghost fs-1 mb-2 opacity-50"></i>
                                    <p class="mb-0 text-sm">Belum ada riwayat transaksi poin</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- TAB 2: MARKETPLACE -->
                <div class="tab-pane fade" id="market-tab">
                    <div class="row">
                        @foreach($items as $i)
                        <div class="col-md-6 mb-4">
                            <div class="card shop-card h-100 shadow-sm border">
                                <div class="card-body text-center position-relative mt-3">
                                    <div class="mx-auto bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-lg position-absolute" style="width: 50px; height: 50px; top: -40px; left: 50%; transform: translateX(-50%);">
                                        <i class="bx text-white fs-3 {{ $i->item_type == 'BEBAS_ALPHA' ? 'bx-shield-quarter' : ($i->item_type == 'WFH' ? 'bx-home-heart' : 'bx-gift') }}"></i>
                                    </div>
                                    
                                    <h5 class="mt-3 mb-2 fw-bold">{{ $i->item_name }}</h5>
                                    <p class="text-sm text-muted">{{ $i->description }}</p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <h4 class="mb-0 text-warning d-flex align-items-center"><i class="bx bxs-coin-stack me-1"></i>{{ $i->point_cost }}</h4>
                                        
                                        @if($i->available_for_me && $pointBalance >= $i->point_cost)
                                            <form action="{{ route('siswa.wallet.buy', $i->id) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-dark mb-0 px-3 fw-bold shadow-sm" onclick="return confirm('Tukar {{ $i->point_cost }} poin untuk mendapatkan token ini?')">Tukar</button>
                                            </form>
                                        @elseif(!$i->available_for_me)
                                            <button class="btn btn-outline-secondary mb-0 px-3" disabled>Limit Habis</button>
                                        @else
                                            <button class="btn btn-outline-secondary mb-0 px-3" disabled>Saldo Kurang</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- TAB 3: INVENTORY -->
                <div class="tab-pane fade" id="inventory-tab">
                    <div class="card shadow-sm border-0">
                        <div class="card-header border-bottom">
                            <h6 class="m-0 fw-bold">Koleksi Token Milikmu</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                @forelse($inventory as $token)
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-body border p-3 d-flex flex-row align-items-center bg-lighter">
                                            <div class="avatar avatar-md me-3 bg-label-info rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bx text-xl {{ $token->item->item_type == 'BEBAS_ALPHA' ? 'bx-shield-check' : 'bx-purchase-tag' }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $token->item->item_name }}</h6>
                                                <span class="badge bg-label-secondary mb-1">{{ $token->item->item_type_label }}</span>
                                                <div class="clear-fix mt-2"></div>
                                                
                                                @if($token->status == 'USED')
                                                    <span class="badge bg-secondary mb-0"><i class="bx bx-check-circle me-1"></i> Telah Dipakai ({{ $token->used_at->format('d/m/Y') }})</span>
                                                @else
                                                    @if($token->item->requires_active_session)
                                                        <!-- TOMBOL GUNAKAN BEBAS ALPHA -->
                                                        <button class="btn btn-sm btn-primary mb-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#useAlphaModal{{$token->id}}">Gunakan (Sesi Aktif)</button>
                                                        
                                                        <!-- Modal Use Alpha Token -->
                                                        <div class="modal fade" id="useAlphaModal{{$token->id}}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-primary">
                                                                        <h5 class="modal-title text-white">Gunakan Token {{ $token->item->item_name }}</h5>
                                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form action="{{ route('siswa.wallet.use', $token->id) }}" method="POST">
                                                                        @csrf
                                                                        <div class="modal-body">
                                                                            <p class="mb-3 text-center">Silakan pilih opsi sesi absensi aktif untuk dilindungi dari status Alpha:</p>
                                                                            @if($activeAlphaSessions->count() > 0)
                                                                                <select name="id_absensi" class="form-select border border-primary p-2" required>
                                                                                    <option value="" disabled selected>-- Pilih Sesi Berjalan --</option>
                                                                                    @foreach($activeAlphaSessions as $as)
                                                                                        <option value="{{ $as->id_absensi }}">{{ $as->absensi->nama_absensi }} (Berakhir: {{ preg_replace('/:[0-9]{2}$/', '', $as->absensi->jam_selesai) }})</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            @else
                                                                                <div class="alert alert-warning text-dark mb-0 d-flex align-items-center"><i class="bx bx-error-circle fs-4 me-2"></i>  Tidak ada sesi absensi aktif di mana kamu berstatus Alpha saat ini.</div>
                                                                            @endif
                                                                        </div>
                                                                        @if($activeAlphaSessions->count() > 0)
                                                                            <div class="modal-footer">
                                                                                <button type="submit" class="btn btn-success fw-bold"><i class="bx bx-check-shield me-1"></i> Aktifkan Perlindungan</button>
                                                                            </div>
                                                                        @endif
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <!-- TOMBOL GUNAKAN GENERAL -->
                                                        <form action="{{ route('siswa.wallet.use', $token->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success mb-0 shadow-sm" onclick="return confirm('Gunakan token ini sekarang?')">Gunakan Token</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center my-5">
                                        <i class="bx bx-box fs-1 mb-2 opacity-50 text-muted"></i>
                                        <p class="text-muted text-sm mb-0">Kamu belum mengantongi token apapun.<br>Yuk beli di Marketplace!</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
