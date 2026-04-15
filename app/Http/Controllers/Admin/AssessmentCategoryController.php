<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssessmentCategory;

class AssessmentCategoryController extends Controller
{
    public function index()
    {
        $categories = AssessmentCategory::latest()->get();
        return view('admin.assessment_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.assessment_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:siswa,guru',
            'is_active' => 'boolean',
        ]);

        AssessmentCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('assessment-categories.index')->with('success', 'Kategori Penilaian berhasil ditambahkan.');
    }

    public function edit(AssessmentCategory $assessmentCategory)
    {
        return view('admin.assessment_categories.edit', compact('assessmentCategory'));
    }

    public function update(Request $request, AssessmentCategory $assessmentCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:siswa,guru',
            'is_active' => 'boolean',
        ]);

        $assessmentCategory->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('assessment-categories.index')->with('success', 'Kategori Penilaian berhasil diperbarui.');
    }

    public function destroy(AssessmentCategory $assessmentCategory)
    {
        $assessmentCategory->delete();
        return redirect()->route('assessment-categories.index')->with('success', 'Kategori Penilaian berhasil dihapus.');
    }
}
