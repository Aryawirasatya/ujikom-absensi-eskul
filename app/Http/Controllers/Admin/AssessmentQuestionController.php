<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;

class AssessmentQuestionController extends Controller
{
    public function index()
    {
        $questions = AssessmentQuestion::with('category')
            ->latest()
            ->get();

        $categories = AssessmentCategory::active()->get();

        $stats = [
            'total' => $questions->count(),
            'active' => $questions->where('is_active', true)->count(),
            'inactive' => $questions->where('is_active', false)->count()
        ];

        return view('admin.assessment-questions.index', compact(
            'questions',
            'categories',
            'stats'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:assessment_categories,id',
            'question' => 'required|max:255'
        ]);

        AssessmentQuestion::create([
            'category_id' => $request->category_id,
            'question' => $request->question,
            'is_active' => true
        ]);

        return back()->with('success', 'Pertanyaan berhasil dibuat.');
    }

    public function update(Request $request, AssessmentQuestion $assessmentQuestion)
    {
        $request->validate([
            'category_id' => 'required|exists:assessment_categories,id',
            'question' => 'required|max:255'
        ]);

        $assessmentQuestion->update([
            'category_id' => $request->category_id,
            'question' => $request->question
        ]);

        return back()->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function toggle(AssessmentQuestion $assessmentQuestion)
    {
        $assessmentQuestion->update([
            'is_active' => !$assessmentQuestion->is_active
        ]);

        return back()->with('success', 'Status pertanyaan diperbarui.');
    }

    public function destroy(AssessmentQuestion $assessmentQuestion)
    {
        $assessmentQuestion->delete();

        return back()->with('success', 'Pertanyaan berhasil dihapus.');
    }
}