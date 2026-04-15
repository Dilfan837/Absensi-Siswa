@extends('layouts.app')

@section('title', 'Rekapitulasi Penilaian Cetak')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 bg-dark text-white print-btn-container">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-white mb-1"><i class="bx bx-spreadsheet me-2"></i> Rekapitulasi Data Evaluasi</h4>
                    <p class="mb-0 text-white-50">Tabel kumpulan rangkuman skor rata-rata. Klik tab yang ingin di-*export* lalu tekan Cetak.</p>
                </div>
                <div>
                    <button onclick="printActiveTab()" class="btn btn-primary d-flex align-items-center"><i class="bx bx-printer me-2"></i> Cetak Tab Aktif</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Elemen ini hanya muncul saat di Print (Kop Surat) -->
<div id="printHeader" style="display: none;" class="print-header">
    <h2 class="mb-1">Rekapitulasi Penilaian</h2>
    <p class="mb-0">Diekspor dari Sistem Informasi Q-Absen pada {{ date('d F Y H:i') }}</p>
</div>

<div class="nav-align-top mb-4">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-siswa" aria-controls="navs-siswa" aria-selected="true">
                <i class="bx bx-user me-1"></i> Rekap Sikap Siswa
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-guru" aria-controls="navs-guru" aria-selected="false">
                <i class="bx bx-chalkboard me-1"></i> Rekap Kinerja Guru
            </button>
        </li>
    </ul>
    
    <div class="tab-content shadow-sm">
        <!-- REKAP SISWA -->
        <div class="tab-pane fade show active" id="navs-siswa" role="tabpanel">
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

        <!-- REKAP GURU -->
        <div class="tab-pane fade" id="navs-guru" role="tabpanel">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-bordered datatable-default">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Guru</th>
                            <th>NIP / NUPTK</th>
                            <th>Mata Pelajaran Utama</th>
                            <th>Skor Rata-Rata Akhir</th>
                            <th>Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapGuru as $idx => $g)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="fw-bold">{{ $g->nama }}</td>
                            <td>{{ str_replace('NIP/NUPTK: ', '', $g->identitas) }}</td>
                            <td>{{ $g->info }}</td>
                            <td class="text-center fw-bold">{{ number_format($g->skor_akhir, 2) }}</td>
                            <td>
                                @if($g->skor_akhir >= 4.5) <span class="badge bg-success">Sangat Kompeten</span>
                                @elseif($g->skor_akhir >= 3.5) <span class="badge bg-primary">Kompeten</span>
                                @elseif($g->skor_akhir >= 2.5) <span class="badge bg-warning">Standar</span>
                                @else <span class="badge bg-danger">Kurang Performa</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style type="text/css" media="print">
    /* Hide navigation and buttons when printing */
    .layout-menu, .layout-navbar, .btn-primary, .nav-tabs, .print-btn-container, footer { display: none !important; }
    .layout-page { padding: 0 !important; margin: 0 !important; background-color: white !important; }
    .card { border: none !important; box-shadow: none !important; background: transparent !important; }
    .card-body h4, .card-body p { color: black !important; } /* Fix dark theme card text when printing */
    .bx { display: none !important; } /* Hide icons */
    
    /* Make only the active tab visible during printing */
    .tab-content { padding: 0 !important; box-shadow: none !important; border: none !important; background-color: white !important;}
    .tab-pane { display: none !important; }
    .tab-pane.active { display: block !important; opacity: 1 !important; visibility: visible !important; }
    
    /* Table styling for print */
    table { width: 100% !important; border-collapse: collapse !important; }
    th, td { border: 1px solid #ddd !important; padding: 8px !important; color: black !important; }
    th { background-color: #f5f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    
    /* Print Header (Kop Surat) */
    .print-header { display: block !important; text-align: center; margin-bottom: 2rem; border-bottom: 2px solid black; padding-bottom: 1rem; }
    .print-header h2 { margin: 0; padding: 0; font-size: 24px; font-weight: bold; }
    .print-header p { margin: 5px 0 0 0; padding: 0; font-size: 14px; }
    
    @page { size: landscape; margin: 1cm; }
    body { background-color: white !important; color: black !important; }
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
            pageLength: 25 // Show more rows by default so print gets more data
        });
    });
    
    function printActiveTab() {
        // Find which tab is active
        let activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
        let title = activeTab === '#navs-siswa' ? 'Rekapitulasi Penilaian Sikap Siswa' : 'Rekapitulasi Kinerja Tenaga Pendidik';
        
        // Temporarily change document title for PDF name
        let originalTitle = document.title;
        document.title = title;
        
        // Show the print header
        let printHeader = document.getElementById('printHeader');
        printHeader.querySelector('h2').innerText = title;
        printHeader.style.display = 'block';
        
        window.print();
        
        // Restore title and hide header
        document.title = originalTitle;
        printHeader.style.display = 'none';
    }
</script>
@endpush
