<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AssessmentCategory;
use Illuminate\Support\Facades\DB;

class GuruMonitoringController extends Controller
{
    /**
     * Dashboard Pemantauan Siswa Per Kelas (Khusus kelas yang bisa diakses guru)
     */
    public function monitorSiswa(Request $request)
    {
        // For simplicity, showing all Kelas here, but in real app this should only fetch Kelas 
        // that belongs to this Guru based on Jadwal/Absensi.
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
                // We show the global average for the class (including other teachers' ratings)
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

        return view('guru.monitoring.siswa', compact(
            'kelas', 'selectedKelasId', 'siswas', 
            'categories', 'chartLabels', 'classAverages', 'siswaRadarData'
        ));
    }

    /**
     * Halaman Rekapitulasi untuk Guru (Mencetak daftar siswa)
     */
    public function recapReport()
    {
        // Dalam implementasi nyata, ini difilter berdasarkan kelas yang diajar guru tersebut.
        // Untuk saat ini kita tampilkan semua siswa aktif agar bisa di-print.
        $siswas = Siswa::where('status_aktif', 1)->with('kelas.jurusan')->get();
        $siswaCategories = AssessmentCategory::where('type', 'siswa')->get();
        $rekapSiswa = [];

        foreach ($siswas as $siswa) {
            $totalSkor = 0;
            $hasData = false;
            foreach($siswaCategories as $cat) {
                // Di sini ditarik semua penilaian yang pernah diberikan ke siswa tsb
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
        
        // Urutkan default dari nilai tertinggi
        usort($rekapSiswa, fn($a, $b) => $b->skor_akhir <=> $a->skor_akhir);

        return view('guru.monitoring.recap', compact('rekapSiswa'));
    }
}
