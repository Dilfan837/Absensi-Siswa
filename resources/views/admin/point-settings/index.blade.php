@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Pengaturan Poin Integritas</h4>

    <div class="card shadow-sm border-0 mb-4">
        <h5 class="card-header bg-primary text-white"><i class="bx bx-cog me-2"></i> Konfigurasi Poin Sistem</h5>
        <div class="card-body mt-4">
            <form action="{{ route('admin.poin.settings.update') }}" method="POST">
                @csrf
                <div class="row">
                    @foreach($settings as $setting)
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark fs-6">{{ $setting->label }}</label>
                            <div class="input-group input-group-merge">
                                <input type="number" name="settings[{{ $setting->key }}]" class="form-control" value="{{ $setting->value }}" required>
                                <span class="input-group-text border-start-0">
                                    @if(str_contains($setting->key, 'minus') || $setting->value < 0)
                                        <span class="badge bg-label-danger"><i class="bx bx-trending-down me-1"></i> Pengurangan</span>
                                    @elseif($setting->value > 0)
                                        <span class="badge bg-label-success"><i class="bx bx-trending-up me-1"></i> Penambahan</span>
                                    @else
                                        <span class="badge bg-label-secondary">Netral</span>
                                    @endif
                                </span>
                            </div>
                            <div class="form-text mt-2"><i class="bx bx-time-five me-1"></i> Terakhir diubah: {{ $setting->updated_at->format('d M Y H:i') }} oleh {{ $setting->updated_by ? App\Models\User::find($setting->updated_by)?->username : 'Sistem' }}</div>
                        </div>
                    @endforeach
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="bx bx-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
