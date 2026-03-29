<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentCategoryController extends Controller
{
    public function index()
    {
        $categories = AssessmentCategory::with(['questions' => function ($q) {
            $q->latest();
        }])->get();

        $stats = [
            'total'    => $categories->count(),
            'active'   => $categories->where('is_active', true)->count(),
            'inactive' => $categories->where('is_active', false)->count(),
        ];

        return view('admin.assessment-categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100|unique:assessment_categories,name',
            'description'     => 'nullable|string|max:500',
            'show_to_student' => 'nullable|boolean',
        ]);

        AssessmentCategory::create([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'show_to_student' => $request->boolean('show_to_student', true),
            'is_active'       => true,
        ]);

        return back()->with('success', 'Indikator penilaian berhasil ditambahkan.');
    }

    public function update(Request $request, AssessmentCategory $category)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100|unique:assessment_categories,name,' . $category->id,
            'description'     => 'nullable|string|max:500',
            'show_to_student' => 'nullable|boolean',
        ]);

        $category->update([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'show_to_student' => $request->boolean('show_to_student', true),
        ]);

        return back()->with('success', 'Indikator berhasil diperbarui.');
    }

    public function toggleActive(AssessmentCategory $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        $label = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Indikator \"{$category->name}\" berhasil {$label}.");
    }

    public function destroy(AssessmentCategory $category)
    {
        if ($category->details()->exists()) {
            return back()->with('error', 'Tidak dapat dihapus — indikator ini sudah memiliki data penilaian. Nonaktifkan saja.');
        }

        $category->delete();

        return back()->with('success', 'Indikator berhasil dihapus.');
    }
}