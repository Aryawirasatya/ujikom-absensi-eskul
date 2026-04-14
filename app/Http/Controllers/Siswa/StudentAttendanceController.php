<?php

namespace App\Http\Controllers\Siswa;

use App\Exports\SiswaAttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ExtracurricularMember;
use App\Models\SchoolYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StudentAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user       = auth()->user();
        $schoolYear = SchoolYear::current();

        if (!$schoolYear) {
            return view('siswa.attendance.index', [
                'schoolYear'  => null,
                'attendances' => collect(),
                'summary'     => null,
                'eskulList'   => collect(),
            ]);
        }

        // Eskul list untuk chip filter
        $eskulList = ExtracurricularMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('extracurricular')
            ->get()
            ->pluck('extracurricular')
            ->filter();

        // Filter eskul (opsional)
        $eskulId = $request->get('eskul_id');

        // Overall summary stats
        $summary = DB::selectOne("
            SELECT
                COUNT(att.id) AS total,
                IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                IFNULL(SUM(att.final_status='telat'),0) AS telat,
                IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                IFNULL(SUM(att.final_status='sakit'),0) AS sakit,
                IFNULL(SUM(att.final_status='libur'),0) AS libur
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE att.user_id = ?
              AND act.school_year_id = ?
              AND act.attendance_phase = 'finished'
              " . ($eskulId ? "AND act.extracurricular_id = ?" : "") . "
        ", array_filter([$user->id, $schoolYear->id, $eskulId]));

        // Paginated attendance records ordered by activity date desc
        $attendancesQuery = Attendance::where('user_id', $user->id)
            ->with([
                'activity' => fn($q) => $q->with('extracurricular'),
            ])
            ->whereHas('activity', fn($q) => $q
                ->where('school_year_id', $schoolYear->id)
                ->where('attendance_phase', 'finished')
                ->when($eskulId, fn($q) => $q->where('extracurricular_id', $eskulId))
            )
            ->join('activities', 'activities.id', '=', 'attendances.activity_id')
            ->orderByDesc('activities.activity_date')
            ->orderByDesc('attendances.id')
            ->select('attendances.*');

        $attendances = $attendancesQuery->paginate(20)->withQueryString();

        return view('siswa.attendance.index', compact(
            'schoolYear', 'attendances', 'summary', 'eskulList', 'eskulId'
        ));
    }

    // ══════════════════════════════════════════════
    // EXPORT EXCEL
    // ══════════════════════════════════════════════
    public function exportExcel(Request $request)
    {
        $user       = auth()->user();
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404);

        $eskulId = $request->get('eskul_id');

            $eskulName = null;

            if ($eskulId) {
                $eskulName = \App\Models\Extracurricular::find($eskulId)?->name;
            }

        $attendances = Attendance::where('user_id', $user->id)
            ->with(['activity.extracurricular'])
            ->whereHas('activity', fn($q) => $q
                ->where('school_year_id', $schoolYear->id)
                ->where('attendance_phase', 'finished')
                ->when($eskulId, fn($q) => $q->where('extracurricular_id', $eskulId))
            )
            ->join('activities', 'activities.id', '=', 'attendances.activity_id')
            ->orderByDesc('activities.activity_date')
            ->select('attendances.*')
            ->get();

        $summary = DB::selectOne("
            SELECT
                COUNT(att.id) AS total,
                IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                IFNULL(SUM(att.final_status='telat'),0) AS telat,
                IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                IFNULL(SUM(att.final_status='sakit'),0) AS sakit
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE att.user_id = ?
              AND act.school_year_id = ?
              AND act.attendance_phase = 'finished'
        ", [$user->id, $schoolYear->id]);

        $filename = 'riwayat_kehadiran_' . str($user->name)->slug() . '_' . now()->format('Ymd') . '.xlsx';

        return Excel::download(
            new SiswaAttendanceExport($attendances, (array) $summary, $schoolYear, $user, $eskulName),
            $filename
        );
    }

    // ══════════════════════════════════════════════
    // EXPORT PDF
    // ══════════════════════════════════════════════
    public function exportPdf(Request $request)
    {
        $user       = auth()->user();
        $schoolYear = SchoolYear::current();
        if (!$schoolYear) abort(404);

        $eskulId = $request->get('eskul_id');

        $eskulName = null;

        if ($eskulId) {
            $eskulName = \App\Models\Extracurricular::find($eskulId)?->name;
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->with(['activity.extracurricular'])
            ->whereHas('activity', fn($q) => $q
                ->where('school_year_id', $schoolYear->id)
                ->where('attendance_phase', 'finished')
                ->when($eskulId, fn($q) => $q->where('extracurricular_id', $eskulId))
            )
            ->join('activities', 'activities.id', '=', 'attendances.activity_id')
            ->orderByDesc('activities.activity_date')
            ->select('attendances.*')
            ->get();

        $summary = DB::selectOne("
            SELECT
                COUNT(att.id) AS total,
                IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                IFNULL(SUM(att.final_status='telat'),0) AS telat,
                IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                IFNULL(SUM(att.final_status='sakit'),0) AS sakit
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE att.user_id = ?
              AND act.school_year_id = ?
              AND act.attendance_phase = 'finished'
        ", [$user->id, $schoolYear->id]);

        $total = $summary->total ?? 0;
        $hadir = $summary->hadir ?? 0;
        $pct   = $total > 0 ? round($hadir / $total * 100, 1) : 0;

        $pdf = Pdf::loadView('siswa.attendance.pdf.riwayat', compact(
  'user',
 'schoolYear',
            'attendances',
            'summary',
            'pct',
            'eskulName'
        ));

        return $pdf->download('riwayat_kehadiran_' . str($user->name)->slug() . '_' . now()->format('Ymd') . '.pdf');
    }
}