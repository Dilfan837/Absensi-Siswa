@extends('layouts.app')

@section('title', 'API Sync Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
.sync-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.sync-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #696cff;
}
.last-sync-info {
    font-size: 0.875rem;
    color: #6c757d;
}
.sync-btn {
    width: 100%;
    margin-top: 1rem;
}
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Admin /</span> API Data Synchronization
        </h4>
    </div>
</div>

<!-- Sync Cards -->
<div class="row mb-4">
    <!-- Siswa Card -->
    <div class="col-md-4 mb-4">
        <div class="card sync-card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bx bxs-graduation bx-lg text-primary"></i>
                </div>
                <h5 class="card-title">Data Siswa</h5>
                <div class="stat-value">{{ number_format($stats['total_siswa']) }}</div>
                <small class="text-muted">Total Records</small>
                
                @if($lastSyncSiswa)
                <div class="last-sync-info mt-3">
                    <div><strong>Last Sync:</strong> {{ $lastSyncSiswa->completed_at?->diffForHumans() ?? 'Never' }}</div>
                    <div>
                        <span class="badge bg-{{ $lastSyncSiswa->status === 'success' ? 'success' : ($lastSyncSiswa->status === 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($lastSyncSiswa->status) }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <small>
                            ✅ {{ $lastSyncSiswa->records_created }} created, 
                            🔄 {{ $lastSyncSiswa->records_updated }} updated
                            @if($lastSyncSiswa->records_failed > 0)
                                , ❌ {{ $lastSyncSiswa->records_failed }} failed
                            @endif
                        </small>
                    </div>
                </div>
                @else
                <div class="last-sync-info mt-3">
                    <small class="text-muted">No sync history yet</small>
                </div>
                @endif
                
                <button class="btn btn-primary sync-btn sync-data-btn" data-type="siswa">
                    <span class="btn-text">
                        <i class="bx bx-sync"></i> Sync Now
                    </span>
                    <span class="btn-loading" style="display:none;">
                        <span class="spinner-border spinner-border-sm me-2"></span> Syncing...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Guru Card -->
    <div class="col-md-4 mb-4">
        <div class="card sync-card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bx bxs-user-detail bx-lg text-success"></i>
                </div>
                <h5 class="card-title">Data Guru</h5>
                <div class="stat-value text-success">{{ number_format($stats['total_guru']) }}</div>
                <small class="text-muted">Total Records</small>
                
                @if($lastSyncGuru)
                <div class="last-sync-info mt-3">
                    <div><strong>Last Sync:</strong> {{ $lastSyncGuru->completed_at?->diffForHumans() ?? 'Never' }}</div>
                    <div>
                        <span class="badge bg-{{ $lastSyncGuru->status === 'success' ? 'success' : ($lastSyncGuru->status === 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($lastSyncGuru->status) }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <small>
                            ✅ {{ $lastSyncGuru->records_created }} created, 
                            🔄 {{ $lastSyncGuru->records_updated }} updated
                            @if($lastSyncGuru->records_failed > 0)
                                , ❌ {{ $lastSyncGuru->records_failed }} failed
                            @endif
                        </small>
                    </div>
                </div>
                @else
                <div class="last-sync-info mt-3">
                    <small class="text-muted">No sync history yet</small>
                </div>
                @endif
                
                <button class="btn btn-success sync-btn sync-data-btn" data-type="guru">
                    <span class="btn-text">
                        <i class="bx bx-sync"></i> Sync Now
                    </span>
                    <span class="btn-loading" style="display:none;">
                        <span class="spinner-border spinner-border-sm me-2"></span> Syncing...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Kelas Card -->
    <div class="col-md-4 mb-4">
        <div class="card sync-card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bx bxs-school bx-lg text-info"></i>
                </div>
                <h5 class="card-title">Data Kelas</h5>
                <div class="stat-value text-info">{{ number_format($stats['total_kelas']) }}</div>
                <small class="text-muted">Total Records</small>
                
                @if($lastSyncKelas)
                <div class="last-sync-info mt-3">
                    <div><strong>Last Sync:</strong> {{ $lastSyncKelas->completed_at?->diffForHumans() ?? 'Never' }}</div>
                    <div>
                        <span class="badge bg-{{ $lastSyncKelas->status === 'success' ? 'success' : ($lastSyncKelas->status === 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($lastSyncKelas->status) }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <small>
                            ✅ {{ $lastSyncKelas->records_created }} created, 
                            🔄 {{ $lastSyncKelas->records_updated }} updated
                            @if($lastSyncKelas->records_failed > 0)
                                , ❌ {{ $lastSyncKelas->records_failed }} failed
                            @endif
                        </small>
                    </div>
                </div>
                @else
                <div class="last-sync-info mt-3">
                    <small class="text-muted">No sync history yet</small>
                </div>
                @endif
                
                <button class="btn btn-info sync-btn sync-data-btn" data-type="kelas">
                    <span class="btn-text">
                        <i class="bx bx-sync"></i> Sync Now
                    </span>
                    <span class="btn-loading" style="display:none;">
                        <span class="spinner-border spinner-border-sm me-2"></span> Syncing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sync All Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title mb-3">
                    <i class="bx bx-refresh"></i> Sync All Data
                </h5>
                <p class="text-muted mb-3">Syncronize all data types (Kelas, Siswa, and Guru) in one click</p>
                <button class="btn btn-lg btn-warning sync-data-btn" data-type="all" style="min-width: 200px;">
                    <span class="btn-text">
                        <i class="bx bx-sync"></i> Sync All Now
                    </span>
                    <span class="btn-loading" style="display:none;">
                        <span class="spinner-border spinner-border-sm me-2"></span> Syncing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sync History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Sync History</h5>
                <a href="{{ route('admin.sync.history') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Records</th>
                                <th>Duration</th>
                                <th>Triggered By</th>
                                <th>Completed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogs as $log)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ strtoupper($log->api_type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        ✅ {{ $log->records_created }} 
                                        🔄 {{ $log->records_updated }}
                                        @if($log->records_failed > 0)
                                            ❌ {{ $log->records_failed }}
                                        @endif
                                    </small>
                                </td>
                                <td>{{ $log->duration_seconds }}s</td>
                                <td>{{ $log->triggeredBy?->nama ?? 'System' }}</td>
                                <td>{{ $log->completed_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No sync history yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
    
    // Sync button click handler
    $('.sync-data-btn').click(function() {
        const type = $(this).data('type');
        const btn = $(this);
        
        // Confirm
        Swal.fire({
            title: `Sync ${type} data?`,
            text: "This will fetch and update data from ZieLabs API",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, sync it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3'
        }).then((result) => {
            if (result.isConfirmed) {
                performSync(type, btn);
            }
        });
    });
    
    function performSync(type, btn) {
        // Disable button & show loading
        btn.prop('disabled', true);
        btn.find('.btn-text').hide();
        btn.find('.btn-loading').show();
        
        // AJAX request
        $.ajax({
            url: `/sync/${type}`,
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    displaySuccessResult(type, response.data);
                } else {
                    Swal.fire({
                        title: 'Sync Failed',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Unknown error occurred';
                Swal.fire({
                    title: 'Sync Failed',
                    text: error,
                    icon: 'error',
                    confirmButtonColor: '#696cff'
                });
            },
            complete: function() {
                // Re-enable button
                btn.prop('disabled', false);
                btn.find('.btn-text').show();
                btn.find('.btn-loading').hide();
            }
        });
    }
    
    function displaySuccessResult(type, data) {
        // Build result HTML for single type
        if (type !== 'all') {
            Swal.fire({
                title: 'Sync Completed!',
                html: `
                    <div class="text-start">
                        <p><strong>Type:</strong> ${type}</p>
                        <hr>
                        <p>📥 <strong>Fetched from API:</strong> ${data.fetched}</p>
                        <p>✅ <strong>Created (new):</strong> ${data.created}</p>
                        <p>🔄 <strong>Updated (existing):</strong> ${data.updated}</p>
                        <p>❌ <strong>Failed:</strong> ${data.failed}</p>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#696cff',
                confirmButtonText: 'OK'
            }).then(() => {
                // Reload page to update stats
                window.location.reload();
            });
        } else {
            // Build result HTML for all types
            let html = '<div class="text-start">';
            for (const [dataType, stats] of Object.entries(data)) {
                html += `
                    <h6 class="text-uppercase">${dataType}</h6>
                    <p class="mb-1">📥 Fetched: ${stats.fetched}, ✅ Created: ${stats.created}, 🔄 Updated: ${stats.updated}, ❌ Failed: ${stats.failed}</p>
                    <hr>
                `;
            }
            html += '</div>';
            
            Swal.fire({
                title: 'All Data Synced!',
                html: html,
                icon: 'success',
                confirmButtonColor: '#696cff',
                confirmButtonText: 'OK',
                width: '600px'
            }).then(() => {
                window.location.reload();
            });
        }
    }
});
</script>
@endpush
