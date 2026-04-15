<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\Jurusan;
use App\Models\DetailAbsensi;
use App\Exports\AdminAbsensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Dasar
        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();
        $totalJurusan = Jurusan::count();
        $totalGuru = \App\Models\Guru::count();
        $totalMapel = \App\Models\MataPelajaran::count();
        
        // Cek sesi aktif dari tabel absensi
        $sesiAktif = Absensi::where('status', 'aktif')->count();
        
        // Statistik Kehadiran HARI INI (Menggunakan DetailAbsensi yang benar)
        // Kita harus filter berdasarkan tanggal di tabel Absensi (parent)
        $hadirHariIni = DetailAbsensi::where('status', 'hadir')
            ->whereHas('absensi', function($q) {
                $q->whereDate('tanggal', now());
            })
            ->count();

        $statAlpa = DetailAbsensi::where('status', 'alpha') // Atau 'tidak hadir' sesuai enum kamu
            ->whereHas('absensi', function($q) {
                $q->whereDate('tanggal', now());
            })
            ->count();
        
        // Ambil 5 sesi absensi terbaru
        $sesiTerakhir = Absensi::with('kelas')->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalSiswa', 
            'totalKelas', 
            'totalJurusan', 
            'totalGuru',
            'totalMapel',
            'sesiAktif', 
            'hadirHariIni',
            'statAlpa',
            'sesiTerakhir'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $exportType = $request->input('export_type', 'excel');

        $absensis = Absensi::with(['guru', 'mataPelajaran', 'kelas', 'detailAbsensis'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($exportType === 'excel') {
            return Excel::download(new AdminAbsensiExport($absensis, $startDate, $endDate), 'Laporan_Global_' . $startDate . '_to_' . $endDate . '.xlsx');
        } elseif ($exportType === 'pdf') {
            $pdf = Pdf::loadView('exports.admin_report', compact('absensis', 'startDate', 'endDate'));
            return $pdf->download('Laporan_Global_' . $startDate . '_to_' . $endDate . '.pdf');
        }

        return back();
    }
}