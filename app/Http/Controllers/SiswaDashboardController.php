<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetailAbsensi;

class SiswaDashboardController extends Controller
{
    public function index()
    {
        // Get siswa data from logged-in user
        $siswa = auth()->user()->siswa;
        
        if (!$siswa) {
            abort(500, 'Data siswa tidak ditemukan. Hubungi administrator untuk menghubungkan akun Anda dengan data siswa.');
        }
        
        $idSiswa = $siswa->id_siswa;
        
        // Total kehadiran berdasarkan status
        $totalHadir = DetailAbsensi::where('id_siswa', $idSiswa)
            ->where('status', 'hadir')
            ->count();
            
        $totalIzin = DetailAbsensi::where('id_siswa', $idSiswa)
            ->where('status', 'izin')
            ->count();
            
        $totalSakit = DetailAbsensi::where('id_siswa', $idSiswa)
            ->where('status', 'sakit')
            ->count();
            
        $totalAlpha = DetailAbsensi::where('id_siswa', $idSiswa)
            ->where('status', 'alpha')
            ->count();
        
        // Total semua kehadiran
        $totalKehadiran = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;
        
        // Persentase kehadiran
        $persentaseKehadiran = $totalKehadiran > 0 
            ? round(($totalHadir / $totalKehadiran) * 100, 2) 
            : 0;
        
        // Riwayat absensi terbaru (5 terakhir)
        $riwayatAbsensi = DetailAbsensi::with('absensi.kelas')
            ->where('id_siswa', $idSiswa)
            ->latest()
            ->take(5)
            ->get();
        
        // Status absensi hari ini
        $absensiHariIni = DetailAbsensi::whereHas('absensi', function($q) {
            $q->whereDate('tanggal', now());
        })
        ->where('id_siswa', $idSiswa)
        ->first();

        return view('siswa.dashboard', compact(
            'siswa',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpha',
            'totalKehadiran',
            'persentaseKehadiran',
            'riwayatAbsensi',
            'absensiHariIni'
        ));
    }
}
