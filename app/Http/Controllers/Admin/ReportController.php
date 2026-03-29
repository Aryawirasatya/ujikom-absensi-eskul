<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Extracurricular, SchoolYear, User};
use App\Services\ReportService;
use App\Exports\AdminReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    // ═══════════════════════════════════════════
    // HALAMAN UTAMA — Global Dashboard
    // ═══════════════════════════════════════════
    public function index(Request $request)
    {
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404, 'Tidak ada tahun ajaran aktif.');

        $eskuls = Extracurricular::where('is_active', true)->get();
        $filter = $this->getFilter($request, ['date_from','date_to','eskul_id']);

        $globalSummary  = $this->reportService->getGlobalSummary($schoolYear->id, $filter);
        $eskulRanking   = $this->reportService->getEskulRanking($schoolYear->id, $filter);
        $monthlyTrend   = $this->reportService->getGlobalMonthlyTrend($schoolYear->id, 6, $filter);
        $alphaWarning   = $this->buildGlobalAlphaWarning($eskuls, $schoolYear->id, $filter);

        return view('admin.laporan.index', compact(
            'schoolYear', 'eskuls', 'filter',
            'globalSummary', 'eskulRanking', 'monthlyTrend', 'alphaWarning'
        ));
    }

    // ═══════════════════════════════════════════
    // PER ESKUL
    // ═══════════════════════════════════════════
    public function perEskul(Request $request, $eskulId)
    {
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404);

        $eskul  = Extracurricular::findOrFail($eskulId);
        $eskuls = Extracurricular::where('is_active', true)->get();
        $filter = $this->getFilter($request, ['date_from','date_to','type']);
        $tab    = $request->get('tab', 'kegiatan');

        $activitySummary = $this->reportService->getActivitySummary($eskul->id, $schoolYear->id, $filter);
        $studentSummary  = $this->reportService->getStudentSummary($eskul->id, $schoolYear->id, $filter);
        $monthlyTrend    = $this->reportService->getMonthlyTrend($eskul->id, $schoolYear->id, 6);
        $alphaWarning    = $this->reportService->getAlphaWarning($eskul->id, $schoolYear->id, 3, $filter);

        $stats = $this->calcStats($activitySummary);

        return view('admin.laporan.per-eskul', compact(
            'schoolYear', 'eskul', 'eskuls', 'tab', 'filter',
            'activitySummary', 'studentSummary', 'monthlyTrend', 'alphaWarning',
            'stats'
        ));
    }

    // ═══════════════════════════════════════════
    // PER PEMBINA
    // ═══════════════════════════════════════════
    public function perPembina(Request $request)
    {
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404);

        $eskuls       = Extracurricular::where('is_active', true)->get();
        $filter       = $this->getFilter($request, ['date_from','date_to','eskul_id']);
        $pembinaReport = $this->reportService->getPembinaReport($schoolYear->id, $filter);

        return view('admin.laporan.per-pembina', compact(
            'schoolYear', 'eskuls', 'filter', 'pembinaReport'
        ));
    }

    // ═══════════════════════════════════════════
    // PER SISWA (Global)
    // ═══════════════════════════════════════════
    public function perSiswa(Request $request)
    {
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404);

        $eskuls = Extracurricular::where('is_active', true)->get();
        $filter = $this->getFilter($request, ['date_from','date_to','eskul_id','grade','search']);
        $studentReport = $this->reportService->getGlobalStudentReport($schoolYear->id, $filter);

        $grades = User::role('siswa')
            ->where('is_active', true)
            ->whereHas('currentAcademic')
            ->with('currentAcademic')
            ->get()
            ->map(fn($u) => optional($u->currentAcademic)->grade)
            ->filter()->unique()->sort()->values();

        return view('admin.laporan.per-siswa', compact(
            'schoolYear', 'eskuls', 'studentReport', 'filter', 'grades'
        ));
    }

    // ═══════════════════════════════════════════
    // EXPORT EXCEL — Global
    // ═══════════════════════════════════════════
    public function exportExcelGlobal(Request $request)
    {
        $schoolYear = SchoolYear::current();
        $filter     = $this->getFilter($request, ['date_from','date_to','eskul_id']);

        $globalSummary = $this->reportService->getGlobalSummary($schoolYear->id, $filter);
        $eskulRanking  = $this->reportService->getEskulRanking($schoolYear->id, $filter);

        $filename = 'laporan_global_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new AdminReportExport($globalSummary, $eskulRanking, $schoolYear),
            $filename
        );
    }

    // ★ BARU: EXPORT PDF — Global
    public function exportPdfGlobal(Request $request)
    {
        $schoolYear    = SchoolYear::current();
        $filter        = $this->getFilter($request, ['date_from','date_to','eskul_id']);
        $eskuls        = Extracurricular::where('is_active', true)->get();

        $globalSummary = $this->reportService->getGlobalSummary($schoolYear->id, $filter);
        $eskulRanking  = $this->reportService->getEskulRanking($schoolYear->id, $filter);
        $alphaWarning  = $this->buildGlobalAlphaWarning($eskuls, $schoolYear->id, $filter);

        $pdf = Pdf::loadView('admin.laporan.pdf.global', compact(
            'schoolYear', 'globalSummary', 'eskulRanking', 'alphaWarning', 'filter'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan_global_' . now()->format('Ymd') . '.pdf');
    }

    // ═══════════════════════════════════════════
    // EXPORT EXCEL — Per Eskul
    // ═══════════════════════════════════════════
    public function exportExcelEskul(Request $request, $eskulId)
    {
        $schoolYear = SchoolYear::current();
        $eskul      = Extracurricular::findOrFail($eskulId);
        $filter     = $this->getFilter($request, ['date_from','date_to','type']);

        $activitySummary = $this->reportService->getActivitySummary($eskul->id, $schoolYear->id, $filter);
        $studentSummary  = $this->reportService->getStudentSummary($eskul->id, $schoolYear->id, $filter);

        $filename = 'laporan_' . str($eskul->name)->slug() . '_' . now()->format('Ymd') . '.xlsx';
        return Excel::download(
            new \App\Exports\EskulReportExport($activitySummary, $studentSummary, $eskul, $schoolYear),
            $filename
        );
    }

    // ═══════════════════════════════════════════
    // EXPORT EXCEL — Per Siswa
    // ═══════════════════════════════════════════
    public function exportExcelSiswa(Request $request)
    {
        $schoolYear    = SchoolYear::current();
        $filter        = $this->getFilter($request, ['date_from','date_to','eskul_id','grade','search']);
        $studentReport = $this->reportService->getGlobalStudentReport($schoolYear->id, $filter);

        $filename = 'laporan_siswa_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new \App\Exports\StudentReportExport($studentReport, $schoolYear),
            $filename
        );
    }

    // ═══════════════════════════════════════════
    // EXPORT EXCEL — Per Pembina
    // ═══════════════════════════════════════════
    public function exportExcelPembina(Request $request)
    {
        $schoolYear    = SchoolYear::current();
        $filter        = $this->getFilter($request, ['date_from','date_to','eskul_id']);
        $pembinaReport = $this->reportService->getPembinaReport($schoolYear->id, $filter);

        $filename = 'laporan_pembina_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new \App\Exports\PembinaReportExport($pembinaReport, $schoolYear),
            $filename
        );
    }

    // ═══════════════════════════════════════════
    // EXPORT PDF — Per Eskul
    // ═══════════════════════════════════════════
    public function exportPdfEskul(Request $request, $eskulId)
    {
        $schoolYear      = SchoolYear::current();
        $eskul           = Extracurricular::findOrFail($eskulId);
        $filter          = $this->getFilter($request, ['date_from','date_to','type']);
        $activitySummary = $this->reportService->getActivitySummary($eskul->id, $schoolYear->id, $filter);
        $studentSummary  = $this->reportService->getStudentSummary($eskul->id, $schoolYear->id, $filter);
        $stats           = $this->calcStats($activitySummary);

        $pdf = Pdf::loadView('admin.laporan.pdf.per-eskul', compact(
            'eskul', 'schoolYear', 'activitySummary', 'studentSummary', 'stats', 'filter'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan_' . str($eskul->name)->slug() . '_' . now()->format('Ymd') . '.pdf');
    }

    // ═══════════════════════════════════════════
    // EXPORT PDF — Per Pembina
    // ═══════════════════════════════════════════
    public function exportPdfPembina(Request $request)
    {
        $schoolYear    = SchoolYear::current();
        $filter        = $this->getFilter($request, ['date_from','date_to','eskul_id']);
        $pembinaReport = $this->reportService->getPembinaReport($schoolYear->id, $filter);

        $pdf = Pdf::loadView('admin.laporan.pdf.per-pembina', compact(
            'schoolYear', 'pembinaReport', 'filter'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan_pembina_' . now()->format('Ymd') . '.pdf');
    }

    // ═══════════════════════════════════════════
    // EXPORT PDF — Per Siswa Global
    // ═══════════════════════════════════════════
    public function exportPdfSiswa(Request $request)
    {
        $schoolYear    = SchoolYear::current();
        $filter        = $this->getFilter($request, ['date_from','date_to','eskul_id','grade','search']);
        $studentReport = $this->reportService->getGlobalStudentReport($schoolYear->id, $filter);

        $pdf = Pdf::loadView('admin.laporan.pdf.per-siswa', compact(
            'schoolYear', 'studentReport', 'filter'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan_siswa_' . now()->format('Ymd') . '.pdf');
    }

    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════
    private function getFilter(Request $request, array $keys): array
    {
        return collect($keys)->mapWithKeys(fn($k) => [$k => $request->get($k)])->toArray();
    }

    private function buildGlobalAlphaWarning($eskuls, int $schoolYearId, array $filter): array
    {
        $result = [];
        foreach ($eskuls as $eskul) {
            $wa = $this->reportService->getAlphaWarning($eskul->id, $schoolYearId, 3, $filter);
            foreach ($wa as $w) {
                $w['eskul'] = $eskul->name;
                $result[]   = $w;
            }
        }
        usort($result, fn($a, $b) => $b['total_alpha'] <=> $a['total_alpha']);
        return $result;
    }

    private function calcStats(array $activitySummary): array
    {
        $col = collect($activitySummary);
        $totalKegiatan  = $col->count();
        $hadirTotal     = $col->sum('hadir');
        $totalAbsensi   = $col->sum('total');
        $pctKeseluruhan = $totalAbsensi > 0 ? round($hadirTotal / $totalAbsensi * 100, 1) : 0;
        return compact('totalKegiatan','hadirTotal','totalAbsensi','pctKeseluruhan');
    }
}