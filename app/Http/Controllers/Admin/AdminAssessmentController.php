<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAssessmentController extends Controller
{
    public function indexGuru()
    {
        // Get all active teachers
        $gurus = Guru::where('status_aktif', 1)->with(['mataPelajaran', 'user'])->get();
        
        // Let's pass the period, default to current Month
        $currentPeriod = "Bulan " . Carbon::now()->translatedFormat('F Y');

        // Check who has been evaluated for this period
        $evaluatedUserIds = Assessment::where('evaluator_id', auth()->user()->id_user)
            ->where('period', $currentPeriod)
            ->whereHas('details', function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'guru');
                });
            })
            ->pluck('evaluatee_id')
            ->toArray();
            
        // Get active categories for Guru
        $categories = AssessmentCategory::where('type', 'guru')->where('is_active', true)->get();

        return view('admin.assessments.guru', compact('gurus', 'categories', 'currentPeriod', 'evaluatedUserIds'));
    }

    public function storeGuru(Request $request)
    {
        $request->validate([
            'evaluatee_id' => 'required|exists:users,id_user',
            'period' => 'required|string',
            'scores' => 'required|array',
            'scores.*' => 'required|integer|min:1|max:5',
            'general_notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Create main assessment record
            $assessment = Assessment::create([
                'evaluator_id' => auth()->user()->id_user,
                'evaluatee_id' => $request->evaluatee_id,
                'assessment_date' => now()->toDateString(),
                'period' => $request->period,
                'general_notes' => $request->general_notes,
            ]);

            // Create assessment details for each category score
            foreach ($request->scores as $categoryId => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'category_id' => $categoryId,
                    'score' => $score
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Penilaian guru berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan penilaian: ' . $e->getMessage());
        }
    }
}
