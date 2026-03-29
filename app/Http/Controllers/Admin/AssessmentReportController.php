<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\Extracurricular;
use App\Models\User;
use Illuminate\Http\Request;

class AssessmentReportController extends Controller
{

    public function index(Request $request)
    {
        $extracurriculars = Extracurricular::where('is_active', true)->get();

        // Rekap per eskul: jumlah penilaian, rata-rata skor
        $eskulStats = $extracurriculars->map(function ($eskul) {
            $assessments = Assessment::where('extracurricular_id', $eskul->id)
                ->with('details')
                ->get();

            return [
                'eskul'           => $eskul,
                'total_assessments' => $assessments->count(),
                'avg_score'       => $assessments->flatMap->details->avg('score') ?? 0,
                'total_students'  => $assessments->unique('evaluatee_id')->count(),
                'last_period'     => $assessments->sortByDesc('assessment_date')->first()?->period_label ?? '-',
            ];
        });

        // Filter periode untuk dropdown
        $selectedEskul  = $request->input('eskul_id');
        $selectedPeriod = $request->input('period_label');

        $assessmentsQuery = Assessment::with([
        'evaluatee',
        'evaluator',
        'extracurricular',
        'details.question.category'
    ])
    ->when($selectedEskul, fn($q) =>
        $q->where('extracurricular_id', $selectedEskul)
    )
    ->when($selectedPeriod, fn($q) =>
        $q->where('period_label', $selectedPeriod)
    )
    ->latest('assessment_date');


        $assessments = $assessmentsQuery->paginate(20)->withQueryString();

      $periods = Assessment::when($selectedEskul, fn($q) =>
        $q->where('extracurricular_id', $selectedEskul)
    )
    ->select('period_label', 'assessment_date')
    ->orderByDesc('assessment_date')
    ->distinct()
    ->pluck('period_label');

        return view('admin.penilaian.index', compact(
            'extracurriculars', 'eskulStats', 'assessments',
            'periods', 'selectedEskul', 'selectedPeriod'
        ));
    }

    public function perEskul(Request $request, Extracurricular $eskul)
    {
        $selectedPeriod = $request->input('period_label');

        $assessments = Assessment::where('extracurricular_id', $eskul->id)
            ->with(['evaluatee', 'evaluator', 'details.question.category'])
            ->when($selectedPeriod, fn($q) => $q->where('period_label', $selectedPeriod))
            ->latest('assessment_date')
            ->paginate(20)->withQueryString();
       
            $periods = Assessment::where('extracurricular_id', $eskul->id)
            ->select('period_label')
            ->groupBy('period_label')
            ->orderByRaw('MAX(assessment_date) DESC')
            ->pluck('period_label');
        $categories = AssessmentCategory::active()->get();

        return view('admin.penilaian.per-eskul', compact('eskul', 'assessments', 'periods', 'selectedPeriod', 'categories'));
    }

 
   
    public function perSiswa(User $user)
    {
        $assessments = Assessment::where('evaluatee_id', $user->id)
    ->with([
        'details.question.category',
        'extracurricular',
        'evaluator'
    ])
    ->orderByDesc('assessment_date')
    ->get();

        $categories  = AssessmentCategory::active()->get();
        $radarData   = $this->buildRadarData($assessments, $categories);
        $history     = $assessments->groupBy('period_label');
        $eskulList   = Extracurricular::whereHas('members', fn($q) => $q->where('user_id', $user->id))->get();

        return view('admin.penilaian.per-siswa', compact('user', 'assessments', 'radarData', 'history', 'eskulList', 'categories'));
    }

 
   
  public function toggleVisibility(Request $request, Extracurricular $eskul)
{
    // Mengambil nilai boolean dari request
    $isVisible = $request->boolean('show_assessment_to_student');

    $eskul->update([
        'show_assessment_to_student' => $isVisible
    ]);

    $label = $isVisible ? 'ditampilkan' : 'disembunyikan';

    return back()->with(
        'success',
        "Penilaian eskul \"{$eskul->name}\" berhasil {$label} ke siswa."
    );
}

    private function buildRadarData($assessments, $categories): array
    {
        $labels = [];
        $scores = [];

        foreach ($categories as $cat) {
            $avg = $assessments
            ->flatMap->details
            ->filter(fn($d) => $d->question->category_id == $cat->id)
            ->avg('score');
            if ($avg !== null) {
                $labels[] = $cat->name;
                $scores[] = round($avg, 2);
            }
        }

        return ['labels' => $labels, 'scores' => $scores];
    }
}