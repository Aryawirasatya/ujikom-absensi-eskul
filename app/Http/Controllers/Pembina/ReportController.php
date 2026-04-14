<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\{Extracurricular, ExtracurricularCoach, SchoolYear, User};
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    // ═══════════════════════════════════════════
    // DASHBOARD UTAMA — Pilih Eskul
    // (route: GET /pembina/laporan)
    // ═══════════════════════════════════════════
    public function index(Request $request)
    {
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404, 'Tidak ada tahun ajaran aktif.');

        $myEskulIds = ExtracurricularCoach::where('user_id', auth()->id())
            ->pluck('extracurricular_id');

        $myEskuls = Extracurricular::whereIn('id', $myEskulIds)->get();

        if ($myEskuls->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan sebagai pembina di ekstrakurikuler manapun.');
        }

        // Ringkasan cepat per eskul
        $eskulSummaries = [];
        foreach ($myEskuls as $eskul) {
            $actSum = $this->reportService->getActivitySummary($eskul->id, $schoolYear->id, []);
            $stuSum = $this->reportService->getStudentSummary($eskul->id, $schoolYear->id, []);
            $alpha  = $this->reportService->getAlphaWarning($eskul->id, $schoolYear->id, 3, []);
            $col    = collect($actSum);
            $hadir  = $col->sum('hadir');
            $total  = $col->sum('total');
            $eskulSummaries[] = [
                'eskul'          => $eskul,
                'totalKegiatan'  => $col->count(),
                'hadirTotal'     => $hadir,
                'totalAbsensi'   => $total,
                'pctKeseluruhan' => $total > 0 ? round($hadir / $total * 100, 1) : 0,
                'totalAnggota'   => count($stuSum),
                'alphaCount'     => count($alpha),
            ];
        }

        return view('pembina.laporan.dashboard', compact(
            'schoolYear', 'myEskuls', 'eskulSummaries'
        ));
    }

    // ═══════════════════════════════════════════
    // LAPORAN DETAIL PER ESKUL
    // (route: GET /pembina/laporan/{eskul})
    // ═══════════════════════════════════════════
    public function show(Request $request, Extracurricular $eskul)
    {
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404, 'Tidak ada tahun ajaran aktif.');

        $this->authorizeEskul($eskul);

        $myEskulIds = ExtracurricularCoach::where('user_id', auth()->id())
            ->pluck('extracurricular_id');
        $myEskuls = Extracurricular::whereIn('id', $myEskulIds)->get();

        $filter = [
            'date_from' => $request->get('date_from'),
            'date_to'   => $request->get('date_to'),
        ];
        $tab = $request->get('tab', 'ringkasan');

        $activitySummary = $this->reportService->getActivitySummary($eskul->id, $schoolYear->id, $filter);
        $studentSummary  = $this->reportService->getStudentSummary($eskul->id, $schoolYear->id, $filter);
        $monthlyTrend    = $this->reportService->getMonthlyTrend($eskul->id, $schoolYear->id, 6);
        $alphaWarning    = $this->reportService->getAlphaWarning($eskul->id, $schoolYear->id, 3, $filter);

        $col            = collect($activitySummary);
        $totalKegiatan  = $col->count();
        $hadirTotal     = $col->sum('hadir');
        $totalAbsensi   = $col->sum('total');
        $pctKeseluruhan = $totalAbsensi > 0 ? round($hadirTotal / $totalAbsensi * 100, 1) : 0;

        return view('pembina.laporan.index', compact(
            'eskul', 'myEskuls', 'schoolYear', 'tab', 'filter',
            'activitySummary', 'studentSummary', 'monthlyTrend', 'alphaWarning',
            'totalKegiatan', 'hadirTotal', 'totalAbsensi', 'pctKeseluruhan'
        ));
    }

    // ═══════════════════════════════════════════
    // DETAIL DRILL-DOWN PER SISWA
    // ═══════════════════════════════════════════
    public function detailSiswa(Request $request, Extracurricular $eskul, User $user)
    {
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404);

        $this->authorizeEskul($eskul);

        $filter = [
            'date_from' => $request->get('date_from'),
            'date_to'   => $request->get('date_to'),
        ];

        $detail = $this->reportService->getStudentDetail($user->id, $eskul->id, $schoolYear->id, $filter);

        return view('pembina.laporan.detail-siswa', [
            'eskul'      => $eskul,
            'siswa'      => $user,
            'schoolYear' => $schoolYear,
            'detail'     => $detail,
            'filter'     => $filter,
            'generated'  => now()->format('d M Y, H:i'),

        ]);
    }

    // ═══════════════════════════════════════════
    // EXPORT EXCEL
    // ═══════════════════════════════════════════
    public function exportExcel(Request $request, Extracurricular $eskul)
{
    $schoolYear = SchoolYear::current();

    $this->authorizeEskul($eskul);

    $filter = [
        'date_from' => $request->get('date_from'),
        'date_to'   => $request->get('date_to'),
    ];

    $tab = $request->get('tab');

    $activitySummary = $this->reportService
        ->getActivitySummary($eskul->id, $schoolYear->id, $filter);

    $studentSummary = $this->reportService
        ->getStudentSummary($eskul->id, $schoolYear->id, $filter);

    $filename = 'laporan_' . str($eskul->name)->slug() . '_' . now()->format('Ymd') . '.xlsx';

    /*
    | EXPORT SESUAI TAB
    */

    if ($tab === 'siswa') {

        return Excel::download(
            new \App\Exports\Sheets\StudentSheet($studentSummary, $eskul, $schoolYear),
            'laporan_siswa_' . $filename
        );

    }

    if ($tab === 'kegiatan') {

        return Excel::download(
            new \App\Exports\Sheets\ActivitySheet($activitySummary, $eskul, $schoolYear),
            'laporan_kegiatan_' . $filename
        );

    }

    /*
    | DEFAULT (SEMUA)
    */

    return Excel::download(
        new \App\Exports\EskulReportExport($activitySummary, $studentSummary, $eskul, $schoolYear),
        $filename
    );
}

    // ═══════════════════════════════════════════
    // EXPORT PDF
    // ═══════════════════════════════════════════
    public function exportPdf(Request $request, Extracurricular $eskul)
    {
        $schoolYear = SchoolYear::current();
        $this->authorizeEskul($eskul);

        $filter          = ['date_from' => $request->get('date_from'), 'date_to' => $request->get('date_to')];
        $activitySummary = $this->reportService->getActivitySummary($eskul->id, $schoolYear->id, $filter);
        $studentSummary  = $this->reportService->getStudentSummary($eskul->id, $schoolYear->id, $filter);
        $alphaWarning    = $this->reportService->getAlphaWarning($eskul->id, $schoolYear->id, 3, $filter);

        $col            = collect($activitySummary);
        $totalKegiatan  = $col->count();
        $hadirTotal     = $col->sum('hadir');
        $totalAbsensi   = $col->sum('total');
        $pctKeseluruhan = $totalAbsensi > 0 ? round($hadirTotal / $totalAbsensi * 100, 1) : 0;

        $pdf = Pdf::loadView('pembina.laporan.pdf.rekap', compact(
            'eskul', 'schoolYear', 'activitySummary', 'studentSummary',
            'alphaWarning', 'totalKegiatan', 'hadirTotal', 'totalAbsensi', 'pctKeseluruhan'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan_' . str($eskul->name)->slug() . '_' . now()->format('Ymd') . '.pdf');
    }

    // ═══════════════════════════════════════════
    // EXPORT PDF — Detail Siswa
    // ═══════════════════════════════════════════
    public function exportPdfSiswa(Request $request, Extracurricular $eskul, User $user)
    {
        $schoolYear = SchoolYear::current();
        $this->authorizeEskul($eskul);

        $filter = ['date_from' => $request->get('date_from'), 'date_to' => $request->get('date_to')];
        $detail = $this->reportService->getStudentDetail($user->id, $eskul->id, $schoolYear->id, $filter);

        $pdf = Pdf::loadView('pembina.laporan.pdf.detail-siswa', [
            'siswa'      => $user,
            'eskul'      => $eskul,
            'schoolYear' => $schoolYear,
            'detail'     => $detail,
            'generated'  => now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('detail_' . str($user->name)->slug() . '_' . now()->format('Ymd') . '.pdf');
    }

    // ═══════════════════════════════════════════
    // HELPER
    // ═══════════════════════════════════════════
    private function authorizeEskul(Extracurricular $eskul): void
    {
        $myEskulIds = ExtracurricularCoach::where('user_id', auth()->id())
            ->pluck('extracurricular_id');
        if (!$myEskulIds->contains($eskul->id)) {
            abort(403, 'Anda tidak memiliki akses ke ekstrakurikuler ini.');
        }
    }
}