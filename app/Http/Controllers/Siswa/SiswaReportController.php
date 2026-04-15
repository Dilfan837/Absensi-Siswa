<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use Illuminate\Support\Facades\DB;

class SiswaReportController extends Controller
{
    /**
     * Tampilan laporan radar untuk satu siswa
     */
    public function show($id_siswa)
    {
        $siswa = Siswa::with('user', 'kelas.jurusan')->findOrFail($id_siswa);
        
        $categories = AssessmentCategory::where('type', 'siswa')->where('is_active', true)->get();
        if ($categories->isEmpty()) {
            return back()->with('error', 'Kategori penilaian siswa belum ada.');
        }

        // Ambil penilaian untuk siswa ini
        $assessments = Assessment::where('evaluatee_id', $siswa->id_user)
            ->whereHas('details', function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'siswa');
                });
            })
            ->with(['evaluator.guru', 'details.category']) // Load guru yang menilai
            ->orderBy('created_at', 'desc')
            ->get();

        if ($assessments->isEmpty()) {
            return view('siswa.reports.empty', compact('siswa'));
        }

        $chartLabels = [];
        $chartData = [];

        foreach ($categories as $cat) {
            $averageScore = DB::table('assessment_details')
                ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
                ->where('assessments.evaluatee_id', $siswa->id_user)
                ->where('assessment_details.category_id', $cat->id)
                ->avg('assessment_details.score');
                
            $chartLabels[] = $cat->name;
            $chartData[] = round($averageScore, 2);
        }

        return view('siswa.reports.show', compact('siswa', 'assessments', 'chartLabels', 'chartData'));
    }
    
    /**
     * Method untuk Siswa melihat laporannya sendiri (My Report)
     */
    public function myReport()
    {
        $siswa = Siswa::where('id_user', auth()->user()->id_user)->first();
        if(!$siswa) {
            return back()->with('error', 'Profil siswa tidak ditemukan.');
        }
        
        return $this->show($siswa->id_siswa);
    }
}
