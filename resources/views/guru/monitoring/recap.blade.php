@extends('layouts.app')

@section('title', 'Rekapitulasi Penilaian Siswa')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 bg-success text-white print-btn-container">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-white mb-1"><i class="bx bx-spreadsheet me-2"></i> Rekapitulasi Data Evaluasi Siswa</h4>
                    <p class="mb-0 text-white-50">Tabel kumpulan rangkuman skor rata-rata karakter siswa. Siap untuk dicetak (Export).</p>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-light text-success d-flex align-items-center fw-bold"><i class="bx bx-printer me-2"></i> Cetak Dokumen</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Elemen ini hanya muncul saat di Print (Kop Surat) -->
<div id="printHeader" class="print-header">
    <h2 class="mb-1">Rekapitulasi Penilaian Karakter Siswa</h2>
    <p class="mb-0">Dicetak oleh Guru: {{ auth()->user()->guru->nama ?? 'Guru' }} pada {{ date('d F Y H:i') }}</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover table-bordered datatable-default">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>NIS</th>
                        <th>Kelas & Jurusan</th>
                        <th>Skor Rata-Rata Akhir</th>
                        <th>Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapSiswa as $idx => $s)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="fw-bold">{{ $s->nama }}</td>
                        <td>{{ str_replace('NIS: ', '', $s->identitas) }}</td>
                        <td>{{ $s->info }}</td>
                        <td class="text-center fw-bold">{{ number_format($s->skor_akhir, 2) }}</td>
                        <td>
                            @if($s->skor_akhir >= 4.5) <span class="badge bg-success">Sangat Baik</span>
                            @elseif($s->skor_akhir >= 3.5) <span class="badge bg-primary">Baik</span>
                            @elseif($s->skor_akhir >= 2.5) <span class="badge bg-warning">Cukup</span>
                            @else <span class="badge bg-danger">Kurang</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style type="text/css" media="print">
    /* Hide navigation and buttons when printing */
    .layout-menu, .layout-navbar, .print-btn-container, footer { display: none !important; }
    .layout-page { padding: 0 !important; margin: 0 !important; background-color: white !important; }
    .card { border: none !important; box-shadow: none !important; background: transparent !important; }
    .card-body { padding: 0 !important; }
    
    /* Table styling for print */
    table { width: 100% !important; border-collapse: collapse !important; }
    th, td { border: 1px solid #ddd !important; padding: 8px !important; color: black !important; }
    th { background-color: #f5f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    
    /* Print Header (Kop Surat) */
    .print-header { display: block !important; text-align: center; margin-bottom: 2rem; border-bottom: 2px solid black; padding-bottom: 1rem; }
    .print-header h2 { margin: 0; padding: 0; font-size: 24px; font-weight: bold; color: black !important; }
    .print-header p { margin: 5px 0 0 0; padding: 0; font-size: 14px; color: black !important; }
    
    @page { size: landscape; margin: 1cm; }
    body { background-color: white !important; color: black !important; }
</style>
<style type="text/css" media="screen">
    .print-header { display: none; } /* Sembunyikan header cetak di layar biasa */
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('.datatable-default').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            pageLength: 25 
        });
    });
</script>
@endpush
