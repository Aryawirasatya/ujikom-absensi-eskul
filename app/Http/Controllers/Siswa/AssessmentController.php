<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\Extracurricular;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    /**
     * Rapor penilaian siswa: radar chart + history
     * Hanya tampil jika eskul mengizinkan (show_assessment_to_student = true)
     * Route: GET /siswa/penilaian
     */
    public function index()
    {
        $user = Auth::user();
        // Debugging: Cek ID eskul yang memperbolehkan tampil
    $allowedEskulIds = Extracurricular::where('show_assessment_to_student', true)
        ->whereHas('members', fn($q) => $q->where('user_id', $user->id)->where('status', 'active'))
        ->pluck('id');

    // Debugging: Cek semua eskul yang diikuti siswa (tanpa filter visibilitas)
    $allJoinedEskul = Extracurricular::whereHas('members', fn($q) => $q->where('user_id', $user->id)->where('status', 'active'))
        ->pluck('name', 'id');
        // Eskul yang diikuti siswa DAN mengizinkan siswa melihat penilaian
        $allowedEskulIds = Extracurricular::where('show_assessment_to_student', true)
            ->whereHas('members', fn($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->pluck('id');

        // Cek apakah ada eskul yang siswa ikuti tapi belum diizinkan tampil
        $joinedEskulIds = Extracurricular::whereHas('members', fn($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->pluck('id');

        $hiddenEskulCount = $joinedEskulIds->diff($allowedEskulIds)->count();

        // Semua assessment yang boleh dilihat
      $assessments = Assessment::where('evaluatee_id', $user->id)
    ->whereIn('extracurricular_id', $allowedEskulIds)
    ->with([
        'details.question.category',
        'extracurricular',
        'evaluator'
    ])
    ->orderByDesc('assessment_date')
    ->get();

        $categories = AssessmentCategory::active()->where('show_to_student', true)->get();
        $radarData  = $this->buildRadarData($assessments, $categories);
        $history    = $assessments->groupBy('period_label');

        // Statistik ringkas
        $stats = [
            'total_penilaian' => $assessments->count(),
            'avg_score'       => $assessments->flatMap->details->avg('score') ?? 0,
            'total_periode'   => $history->count(),
            'total_eskul'     => $assessments->unique('extracurricular_id')->count(),
        ];

        return view('siswa.penilaian.index', compact(
            'assessments', 'categories', 'radarData',
            'history', 'stats', 'hiddenEskulCount'
        ));
    }

    private function buildRadarData($assessments, $categories): array
    {
        $labels = [];
        $scores = [];
        foreach ($categories as $cat) {
            $avg = $assessments->flatMap->details->where('category_id', $cat->id)->avg('score');
            if ($avg !== null) {
                $labels[] = $cat->name;
                $scores[] = round($avg, 2);
            }
        }
        return ['labels' => $labels, 'scores' => $scores];
    }
}