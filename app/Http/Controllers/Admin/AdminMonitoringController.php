<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use Illuminate\Support\Facades\DB;

class AdminMonitoringController extends Controller
{
    /**
     * Dashboard Pemantauan Siswa Per Kelas
     */
    public function monitorSiswa(Request $request)
    {
        $kelas = Kelas::with('jurusan')->get();
        // Default ke kelas pertama jika tidak ada pilihan
        $selectedKelasId = $request->get('kelas_id', $kelas->first()->id_kelas ?? null);

        $categories = AssessmentCategory::where('type', 'siswa')->where('is_active', true)->get();
        $siswas = collect();
        
        $chartLabels = $categories->pluck('name')->toArray();
        $classAverages = [];
        $siswaRadarData = [];

        if ($selectedKelasId && $categories->isNotEmpty()) {
            $siswas = Siswa::where('id_kelas', $selectedKelasId)
                            ->where('status_aktif', 1)
                            ->get();

            // 1. Hitung Rata-Rata Agregat 1 Kelas
            foreach ($categories as $cat) {
                $avg = DB::table('assessment_details')
                    ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
                    ->join('siswa', 'siswa.id_user', '=', 'assessments.evaluatee_id')
                    ->where('siswa.id_kelas', $selectedKelasId)
                    ->where('assessment_details.category_id', $cat->id)
                    ->avg('assessment_details.score');
                    
                $classAverages[] = round($avg ?: 0, 2);
            }

            // 2. Hitung Radar masing-masing Siswa
            foreach ($siswas as $siswa) {
                $mData = [];
                $averageTotal = 0;
                
                foreach ($categories as $cat) {
                    $avg = DB::table('assessment_details')
                        ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
                        ->where('assessments.evaluatee_id', $siswa->id_user)
                        ->where('assessment_details.category_id', $cat->id)
                        ->avg('assessment_details.score');
                        
                    $val = round($avg ?: 0, 2);
                    $mData[] = $val;
                    $averageTotal += $val;
                }
                
                $siswaRadarData[$siswa->id_siswa] = [
                    'data' => $mData,
                    'skor_akhir' => $categories->count() > 0 ? round($averageTotal / $categories->count(), 2) : 0
                ];
            }
        }

        return view('admin.monitoring.siswa', compact(
            'kelas', 'selectedKelasId', 'siswas', 
            'categories', 'chartLabels', 'classAverages', 'siswaRadarData'
        ));
    }

    /**
     * Dashboard Pemantauan Guru
     */
    public function monitorGuru()
    {
        $categories = AssessmentCategory::where('type', 'guru')->where('is_active', true)->get();
        $gurus = Guru::where('status_aktif', 1)->with('mataPelajaran')->get();
        
        $chartLabels = $categories->pluck('name')->toArray();
        $guruRadarData = [];

        if ($categories->isNotEmpty()) {
            foreach ($gurus as $guru) {
                $mData = [];
                $averageTotal = 0;
                $hasAssessment = false;
                
                foreach ($categories as $cat) {
                    $avg = DB::table('assessment_details')
                        ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
                        ->where('assessments.evaluatee_id', $guru->id_user)
                        ->where('assessment_details.category_id', $cat->id)
                        ->avg('assessment_details.score');
                        
                    if ($avg !== null) $hasAssessment = true;
                        
                    $val = round($avg ?: 0, 2);
                    $mData[] = $val;
                    $averageTotal += $val;
                }
                
                if($hasAssessment){
                    $guruRadarData[$guru->id_guru] = [
                        'data' => $mData,
                        'skor_akhir' => $categories->count() > 0 ? round($averageTotal / $categories->count(), 2) : 0
                    ];
                }
            }
        }

        return view('admin.monitoring.guru', compact('gurus', 'categories', 'chartLabels', 'guruRadarData'));
    }

    /**
     * Halaman Rekapitulasi (Tabel untuk Print)
     */
    public function recapReport()
    {
        // Data Rekap Siswa (Top 100 as example or all)
        // For performance in big data, usually handled via DataTables AJAX.
        // Doing full load for simplicity here.
        $siswas = Siswa::where('status_aktif', 1)->with('kelas.jurusan')->get();
        $siswaCategories = AssessmentCategory::where('type', 'siswa')->get();
        $rekapSiswa = [];

        foreach ($siswas as $siswa) {
            $totalSkor = 0;
            $hasData = false;
            foreach($siswaCategories as $cat) {
                $avg = DB::table('assessment_details')
                        ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
                        ->where('assessments.evaluatee_id', $siswa->id_user)
                        ->where('assessment_details.category_id', $cat->id)
                        ->avg('assessment_details.score');
                if($avg !== null) $hasData = true;
                $totalSkor += ($avg ?: 0);
            }
            if($hasData) {
                $rekapSiswa[] = (object)[
                    'nama' => $siswa->nama_siswa,
                    'identitas' => 'NIS: ' . $siswa->nis,
                    'info' => ($siswa->kelas->nama_kelas ?? '') . ' ' . ($siswa->kelas->jurusan->nama_jurusan ?? ''),
                    'skor_akhir' => $siswaCategories->count() > 0 ? round($totalSkor / $siswaCategories->count(), 2) : 0
                ];
            }
        }

        // Data Rekap Guru
        $gurus = Guru::where('status_aktif', 1)->with('mataPelajaran')->get();
        $guruCategories = AssessmentCategory::where('type', 'guru')->get();
        $rekapGuru = [];

        foreach ($gurus as $guru) {
            $totalSkor = 0;
            $hasData = false;
            foreach($guruCategories as $cat) {
                $avg = DB::table('assessment_details')
                        ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
                        ->where('assessments.evaluatee_id', $guru->id_user)
                        ->where('assessment_details.category_id', $cat->id)
                        ->avg('assessment_details.score');
                if($avg !== null) $hasData = true;
                $totalSkor += ($avg ?: 0);
            }
            
            if($hasData) {
                $rekapGuru[] = (object)[
                    'nama' => $guru->nama,
                    'identitas' => 'NIP/NUPTK: ' . ($guru->nip ?? $guru->nuptk),
                    'info' => $guru->mataPelajaran->nama_mapel ?? 'Semua Mapel',
                    'skor_akhir' => $guruCategories->count() > 0 ? round($totalSkor / $guruCategories->count(), 2) : 0
                ];
            }
        }
        
        // Urutkan default dari nilai tertinggi
        usort($rekapSiswa, fn($a, $b) => $b->skor_akhir <=> $a->skor_akhir);
        usort($rekapGuru, fn($a, $b) => $b->skor_akhir <=> $a->skor_akhir);

        return view('admin.monitoring.recap', compact('rekapSiswa', 'rekapGuru'));
    }
}
