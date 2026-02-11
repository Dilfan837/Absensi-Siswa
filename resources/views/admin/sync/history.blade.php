@extends('layouts.app')

@section('title', 'Sync History')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Admin / API Sync /</span> History
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Synchronization History</h5>
                <a href="{{ route('admin.sync.index') }}" class="btn btn-sm btn-primary">
                    <i class="bx bx-arrow-back"></i> Back to Dashboard
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Fetched</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Failed</th>
                                <th>Duration</th>
                                <th>Triggered By</th>
                                <th>Started At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    <span class="badge bg-info">{{ strtoupper($log->api_type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td>{{ $log->records_fetched }}</td>
                                <td><span class="text-success">{{ $log->records_created }}</span></td>
                                <td><span class="text-info">{{ $log->records_updated }}</span></td>
                                <td>
                                    @if($log->records_failed > 0)
                                        <span class="text-danger fw-bold">{{ $log->records_failed }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>{{ $log->duration_seconds }}s</td>
                                <td>
                                    @if($log->triggeredBy)
                                        <span class="badge bg-secondary">{{ $log->triggeredBy->nama }}</span>
                                    @else
                                        <span class="badge bg-dark">System</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $log->started_at?->format('Y-m-d H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($log->error_details && count($log->error_details) > 0)
                                        <button class="btn btn-sm btn-danger view-errors-btn" data-errors='@json($log->error_details)'>
                                            <i class="bx bx-error-circle"></i> View Errors
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle bx-md"></i>
                                    <p class="mt-2">No synchronization history yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $logs->links() }}
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
    // View errors button
    $('.view-errors-btn').click(function() {
        const errors = $(this).data('errors');
        
        // Build error list HTML
        let html = '<div class="text-start"><ul class="list-unstyled">';
        errors.forEach((error, index) => {
            html += `<li class="mb-2">
                <strong>${index + 1}. ${error.nama || 'Unknown'}</strong><br>
                <small class="text-danger">Error: ${error.error || 'Unknown error'}</small>
            </li>`;
        });
        html += '</ul></div>';
        
        Swal.fire({
            title: 'Sync Errors',
            html: html,
            icon: 'error',
            confirmButtonColor: '#696cff',
            confirmButtonText: 'Close',
            width: '600px'
        });
    });
});
</script>
@endpush
