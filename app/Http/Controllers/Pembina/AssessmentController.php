<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use App\Models\AssessmentQuestion; // Tambahan import
use App\Models\AssessmentPeriod;
use App\Models\Extracurricular;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssessmentController extends Controller
{
 
    public function index(Request $request, Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);

        $schoolYear = SchoolYear::current();

        // Semua periode eskul ini, terbaru di atas
        $periods = AssessmentPeriod::where('extracurricular_id', $eskul->id)
            ->orderByDesc('period_label')
            ->get();

        // Periode yang sedang dilihat (dari query string atau default ke yang OPEN)
        $selectedPeriodLabel = $request->input('period');

        if (!$selectedPeriodLabel) {
            // Default: cari yang statusnya open
            $selectedPeriodLabel = $periods->where('status', 'open')->first()?->period_label;
        }

        if (!$selectedPeriodLabel) {
            // Tidak ada periode open → tidak ada periode sama sekali yang bisa dipilih
            return view('pembina.penilaian.index', [
                'eskul'         => $eskul,
                'periods'       => $periods,
                'activePeriod'  => null,
                'periodLabel'   => null,
                'periodStatus'  => null,
                'members'       => collect(),
                'categories'    => AssessmentCategory::active()->get(),
                'totalMembers'  => 0,
                'assessedCount' => 0,
                'pct'           => 0,
                'noPeriod'      => true, 
            ]);
        }

        // Cari object periode yang dipilih
        $activePeriod = $periods->firstWhere('period_label', $selectedPeriodLabel);

        if (!$activePeriod) {
            abort(404, 'Periode tidak ditemukan.');
        }

        $periodLabel  = $activePeriod->period_label;
        $periodStatus = $activePeriod->status;

        // Ambil anggota aktif
        $members = $this->getActiveMembers($eskul, $schoolYear);

        // Preload semua assessment periode ini (hindari N+1)
        $assessments = Assessment::where('extracurricular_id', $eskul->id)
            ->where('period_label', $periodLabel)
            ->with('details.question')
            ->get()
            ->keyBy('evaluatee_id');

        // Attach data penilaian ke setiap member
        $members = $members->map(function ($member) use ($assessments) {
            $assessment = $assessments->get($member->id);

            $member->already_assessed   = $assessment !== null;
            $member->current_assessment = $assessment;
            $member->avg_score          = $assessment?->details->avg('score');

            return $member;
        });

        $categories = AssessmentCategory::active()
        ->with(['questions' => function ($q) {
            $q->where('is_active', true);
        }])
        ->get();

        $totalMembers  = $members->count();
        $assessedCount = $members->where('already_assessed', true)->count();
        $pct           = $totalMembers > 0 ? round(($assessedCount / $totalMembers) * 100) : 0;

        return view('pembina.penilaian.index', compact(
            'eskul',
            'periods',
            'activePeriod',
            'periodLabel',
            'periodStatus',
            'members',
            'categories',
            'totalMembers',
            'assessedCount',
            'pct',
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  STORE — simpan/update penilaian satu siswa
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request, Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);

        $validated = $request->validate([
            'evaluatee_id'  => 'required|exists:users,id',
            'general_notes' => 'nullable|string|max:1000',
            'scores'        => 'required|array|min:1',
            'scores.*'      => 'required|integer|min:1|max:5',
            'period_label'  => 'required|string',
        ]);

        $periodLabel = $validated['period_label'];

        $period = AssessmentPeriod::where('extracurricular_id', $eskul->id)
            ->where('period_label', $periodLabel)
            ->first();

        if (!$period) {
            abort(404, 'Periode tidak ditemukan.');
        }

        if ($period->status === 'closed') {
            return back()->with('error', 'Periode sudah ditutup. Penilaian tidak dapat disimpan.');
        }

        DB::transaction(function () use ($validated, $eskul, $periodLabel, $request) {
            $assessment = Assessment::updateOrCreate(
                [
                    'evaluator_id'       => $request->user()->id,
                    'evaluatee_id'       => $validated['evaluatee_id'],
                    'extracurricular_id' => $eskul->id,
                    'period_label'       => $periodLabel,
                ],
                [
                    'assessment_date' => now()->toDateString(),
                    'period_type'     => 'monthly',
                    'general_notes'   => $validated['general_notes'] ?? null,
                ]
            );

            foreach ($validated['scores'] as $questionId => $score) {
                if ($score === null) continue;

                // FIX: Ambil category_id dari pertanyaan terkait agar tidak error database
                $question = AssessmentQuestion::findOrFail($questionId);

                AssessmentDetail::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'question_id'   => $questionId
                    ],
                    [
                        'category_id'   => $question->category_id, // Kolom ini sekarang terisi otomatis
                        'score'         => $score
                    ]
                );
            }
        });

        if ($request->filled('next_evaluatee_id')) {
            return back()
                ->with('success', 'Penilaian berhasil disimpan.')
                ->with('open_modal', $request->next_evaluatee_id);
        }

        return back()->with('success', 'Penilaian berhasil disimpan.');
    }

    public function createPeriod(Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);

        $openPeriod = AssessmentPeriod::where('extracurricular_id', $eskul->id)
            ->where('status', 'open')
            ->first();

        if ($openPeriod) {
            return back()->with('error',
                "Masih ada periode open ({$openPeriod->period_label}). Tutup dulu sebelum membuat periode baru."
            );
        }

        $last = AssessmentPeriod::where('extracurricular_id', $eskul->id)
            ->orderByDesc('period_label')
            ->first();

        $nextLabel = $last
            ? Carbon::createFromFormat('Y-m', $last->period_label)->addMonth()->format('Y-m')
            : now()->format('Y-m');

        if (AssessmentPeriod::where('extracurricular_id', $eskul->id)->where('period_label', $nextLabel)->exists()) {
            return back()->with('error', "Periode {$nextLabel} sudah ada.");
        }

        AssessmentPeriod::create([
            'extracurricular_id' => $eskul->id,
            'period_label'       => $nextLabel,
            'period_type'        => 'monthly',
            'status'             => 'open',
        ]);

        return redirect()
            ->route('pembina.penilaian.index', $eskul)
            ->with('success', "Periode {$nextLabel} berhasil dibuat. Mulai menilai!");
    }

    public function closePeriod(Extracurricular $eskul, AssessmentPeriod $period)
    {
        $this->authorizeCoach($eskul);

        if ($period->extracurricular_id !== $eskul->id) abort(403);

        if ($period->status === 'closed') {
            return back()->with('error', 'Periode sudah ditutup.');
        }

        $schoolYear   = SchoolYear::current();
        $totalMembers = $this->getActiveMembers($eskul, $schoolYear)->count();

        $assessed = Assessment::where('extracurricular_id', $eskul->id)
            ->where('period_label', $period->period_label)
            ->distinct('evaluatee_id')
            ->count('evaluatee_id');

        if ($assessed < $totalMembers) {
            $sisa = $totalMembers - $assessed;
            return back()->with('error',
                "Masih ada {$sisa} siswa yang belum dinilai. Selesaikan dulu sebelum menutup periode."
            );
        }

        $period->update([
            'status'    => 'closed',
            'closed_at' => now(),
            'closed_by' => Auth::id(),
        ]);

        return back()->with('success', "Periode {$period->period_label} berhasil ditutup.");
    }

    public function reopenPeriod(Extracurricular $eskul, AssessmentPeriod $period)
    {
        $this->authorizeCoach($eskul);

        if ($period->extracurricular_id !== $eskul->id) abort(403);

        $otherOpen = AssessmentPeriod::where('extracurricular_id', $eskul->id)
            ->where('status', 'open')
            ->where('id', '!=', $period->id)
            ->first();

        if ($otherOpen) {
            return back()->with('error', "Tidak bisa membuka kembali — periode {$otherOpen->period_label} masih open.");
        }

        $period->update([
            'status'    => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);

        return back()->with('success', "Periode {$period->period_label} dibuka kembali.");
    }

    public function laporan(Request $request, Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);
        $schoolYear = SchoolYear::current();

        $periods = AssessmentPeriod::where('extracurricular_id', $eskul->id)
            ->orderByDesc('period_label')
            ->get();

        $selectedPeriod = $request->input('period_label')
            ?? $periods->where('status', 'open')->first()?->period_label
            ?? $periods->first()?->period_label;

        if (!$selectedPeriod) abort(404, 'Belum ada periode penilaian.');

        $members = $this->getActiveMembers($eskul, $schoolYear);

        $assessments = Assessment::where('extracurricular_id', $eskul->id)
            ->where('period_label', $selectedPeriod)
            ->with('details')
            ->get()
            ->keyBy('evaluatee_id');

        $members = $members->map(function ($member) use ($assessments) {
            $assessment = $assessments->get($member->id);
            $member->has_assessment = $assessment !== null;
            $member->avg_score      = $assessment?->details->avg('score');
            return $member;
        })->sortByDesc('has_assessment');

        $totalMembers  = $members->count();
        $assessedCount = $members->where('has_assessment', true)->count();
        $pct           = $totalMembers > 0 ? round(($assessedCount / $totalMembers) * 100) : 0;
        $globalAvg     = $members->where('has_assessment', true)->avg('avg_score') ?? 0;

        $categories = AssessmentCategory::active()->get();

        $radarData = ['labels' => [], 'scores' => []];
        foreach ($categories as $cat) {
            // FIX: Query langsung ke category_id di tabel details agar efisien
            $avg = AssessmentDetail::where('category_id', $cat->id)
                ->whereHas('assessment', function ($q) use ($eskul, $selectedPeriod) {
                    $q->where('extracurricular_id', $eskul->id)
                      ->where('period_label', $selectedPeriod);
                })
                ->avg('score');

            $radarData['labels'][] = $cat->name;
            $radarData['scores'][] = round($avg ?? 0, 2);
        }

        return view('pembina.penilaian.laporan', compact(
            'eskul', 'members', 'periods', 'selectedPeriod',
            'totalMembers', 'assessedCount', 'pct', 'globalAvg', 'radarData'
        ));
    }

    public function showSiswa(Extracurricular $eskul, User $user)
    {
        $this->authorizeCoach($eskul);

        $assessments = Assessment::where('evaluatee_id', $user->id)
            ->where('extracurricular_id', $eskul->id)
            ->with(['details.question.category', 'evaluator'])
            ->orderByDesc('assessment_date')
            ->get();

        $categories = AssessmentCategory::active()
        ->with(['questions' => function ($q) {
            $q->where('is_active', true);
        }])
        ->get();
        
        $radarData  = $this->buildRadarData($assessments, $categories);
        $history    = $assessments->groupBy('period_label');

        return view('pembina.penilaian.detail-siswa', compact(
            'eskul', 'user', 'assessments', 'radarData', 'history', 'categories'
        ));
    }

    private function getActiveMembers(Extracurricular $eskul, $schoolYear)
    {
        return User::whereHas('extracurricularMembers', function ($q) use ($eskul, $schoolYear) {
            $q->where('extracurricular_id', $eskul->id)
              ->where('status', 'active')
              ->when($schoolYear, fn($sq) => $sq->where('school_year_id', $schoolYear->id));
        })->get();
    }

    private function authorizeCoach(Extracurricular $eskul): void
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) return;
        abort_unless(
            $eskul->coaches()->where('user_id', $user->id)->exists(),
            403,
            'Anda bukan pembina eskul ini.'
        );
    }

    private function buildRadarData($assessments, $categories): array
    {
        return [
            'labels' => $categories->map(fn($cat) => $cat->name)->values()->toArray(),
            'scores' => $categories->map(function ($cat) use ($assessments) {
                $avg = $assessments
                ->flatMap->details
                ->filter(function ($detail) use ($cat) {
                    return (int)$detail->category_id === (int)$cat->id;
                })
                ->avg('score');
                return round($avg ?? 0, 2);
            })->values()->toArray(),
        ];
    }
 public function indexLaporan()
{
    $schoolYear = SchoolYear::current();
    
    // Ambil eskul yang dibina user login
    $eskuls = Extracurricular::whereHas('coaches', function($q) {
        $q->where('user_id', auth()->id());
    })->withCount(['members as active_members_count' => function($q) use ($schoolYear) {
        $q->where('status', 'active')->where('school_year_id', $schoolYear->id);
    }])->get();

    $eskulSummaries = [];

    foreach ($eskuls as $eskul) {
        // Ambil periode terbaru eskul ini
        $lastPeriod = AssessmentPeriod::where('extracurricular_id', $eskul->id)
            ->orderByDesc('period_label')
            ->first();

        $assessedCount = 0;
        $avgScore = 0;

        if ($lastPeriod) {
            // Hitung berapa siswa yang sudah dinilai di periode ini
            $assessedCount = Assessment::where('extracurricular_id', $eskul->id)
                ->where('period_label', $lastPeriod->period_label)
                ->count();

            // Hitung rata-rata nilai eskul
            $avgScore = AssessmentDetail::whereHas('assessment', function($q) use ($eskul, $lastPeriod) {
                $q->where('extracurricular_id', $eskul->id)
                  ->where('period_label', $lastPeriod->period_label);
            })->avg('score') ?? 0;
        }

        $totalMembers = $eskul->active_members_count;
        $completionPct = $totalMembers > 0 ? round(($assessedCount / $totalMembers) * 100) : 0;

        $eskulSummaries[] = [
            'eskul' => $eskul,
            'total_members' => $totalMembers,
            'assessed_count' => $assessedCount,
            'completion_pct' => $completionPct,
            'avg_score' => $avgScore
        ];
    }

    return view('pembina.penilaian.laporan_index', compact('eskulSummaries', 'schoolYear'));
}
}