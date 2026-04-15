<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GuruReportController extends Controller
{
    /**
     * Tampilan laporan radar untuk satu guru (dilihat oleh admin atau guru tersebut).
     */
    public function show($id_guru)
    {
        $guru = Guru::with('user', 'mataPelajaran')->findOrFail($id_guru);
        
        // Ambil kategori penilian guru
        $categories = AssessmentCategory::where('type', 'guru')->where('is_active', true)->get();
        if ($categories->isEmpty()) {
            return back()->with('error', 'Kategori penilaian guru belum dikonfigurasi.');
        }

        // Ambil semua penilaian yang pernah diberikan ke guru ini
        $assessments = Assessment::where('evaluatee_id', $guru->id_user)
            ->whereHas('details', function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'guru');
                });
            })
            ->with(['evaluator', 'details.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($assessments->isEmpty()) {
            return view('guru.reports.empty', compact('guru'));
        }

        // Hitung rata-rata per kategori (untuk chart radar "Overall Score")
        // Misal dalam 3 periode terakhir, kita rata-ratakan.
        $chartLabels = $categories->pluck('name')->toArray();
        $chartData = [];

        foreach ($categories as $cat) {
            // Rata-rata nilai untuk kategori ini dari semua assessment yang ada
            $averageScore = DB::table('assessment_details')
                ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
                ->where('assessments.evaluatee_id', $guru->id_user)
                ->where('assessment_details.category_id', $cat->id)
                ->avg('assessment_details.score');
                
            $chartData[] = round($averageScore, 2);
        }

        return view('guru.reports.show', compact('guru', 'assessments', 'chartLabels', 'chartData'));
    }
    
    /**
     * Method untuk Guru melihat laporannya sendiri (My Report)
     */
    public function myReport()
    {
        $guru = Guru::where('id_user', auth()->user()->id_user)->first();
        if(!$guru) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }
        
        return $this->show($guru->id_guru);
    }
}
