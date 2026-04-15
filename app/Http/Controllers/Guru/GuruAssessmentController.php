<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GuruAssessmentController extends Controller
{
    public function index(Request $request)
    {
        // For simplicity, a Guru can see all Kelas or we can filter by their specific assignments
        // In this scoped version, we'll allow Guru to select a Kelas
        $kelas = Kelas::all();
        $selectedKelasId = $request->get('kelas_id', $kelas->first()->id_kelas ?? null);
        
        $siswas = collect();
        if ($selectedKelasId) {
            $siswas = Siswa::where('id_kelas', $selectedKelasId)
                            ->where('status_aktif', 1)
                            ->with('user')
                            ->get();
        }

        $currentPeriod = "Bulan " . Carbon::now()->translatedFormat('F Y');

        // Check who has been evaluated by this specific teacher for this period
        $evaluatedUserIds = Assessment::where('evaluator_id', auth()->user()->id_user)
            ->where('period', $currentPeriod)
            ->where('context_type', 'kelas')
            ->where('context_id', $selectedKelasId)
            ->whereHas('details', function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'siswa');
                });
            })
            ->pluck('evaluatee_id')
            ->toArray();

        $categories = AssessmentCategory::where('type', 'siswa')->where('is_active', true)->get();

        return view('guru.assessments.siswa', compact('kelas', 'selectedKelasId', 'siswas', 'categories', 'currentPeriod', 'evaluatedUserIds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'evaluatee_id' => 'required|exists:users,id_user',
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'period' => 'required|string',
            'scores' => 'required|array',
            'scores.*' => 'required|integer|min:1|max:5',
            'general_notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $assessment = Assessment::create([
                'evaluator_id' => auth()->user()->id_user,
                'evaluatee_id' => $request->evaluatee_id,
                'context_type' => 'kelas',
                'context_id' => $request->kelas_id,
                'assessment_date' => now()->toDateString(),
                'period' => $request->period,
                'general_notes' => $request->general_notes,
            ]);

            foreach ($request->scores as $categoryId => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'category_id' => $categoryId,
                    'score' => $score
                ]);
            }

            DB::commit();
            
            // To support "Simpan & Next", we pass a flag to view to auto-open next modal or focus next student
            return redirect()->back()->with('success', 'Penilaian siswa berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage());
        }
    }
}
