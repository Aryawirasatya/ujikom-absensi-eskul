<?php

namespace App\Services;

use App\Models\{Activity, Attendance, Extracurricular, ExtracurricularMember, User, SchoolYear};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    // ══════════════════════════════════════════════════════════════════
    //  ESKUL REPORT — dipakai Pembina & Admin (per eskul)
    // ══════════════════════════════════════════════════════════════════

    /**
     * Rekap per kegiatan/pertemuan untuk satu eskul
     */
    public function getActivitySummary(int $eskulId, int $schoolYearId, array $filter = []): array
    {
        $query = Activity::where('extracurricular_id', $eskulId)
            ->where('school_year_id', $schoolYearId)
            ->where('attendance_phase', 'finished')
            ->with(['attendances'])
            ->orderBy('activity_date', 'desc');

        if (!empty($filter['date_from'])) {
            $query->whereDate('activity_date', '>=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $query->whereDate('activity_date', '<=', $filter['date_to']);
        }
        if (!empty($filter['type'])) {
            $query->where('type', $filter['type']);
        }

        $activities = $query->get();
        $result = [];

        foreach ($activities as $act) {
            $atts = $act->attendances;
            $total = $atts->count();

            $hadir = $atts->where('final_status', 'hadir')->count();
            $telat = $atts->where('final_status', 'hadir')
                          ->where('checkin_status', 'late')->count();
            $hadirTepat = $hadir - $telat;
            $alpha = $atts->where('final_status', 'alpha')->count();
            $izin  = $atts->where('final_status', 'izin')->count();
            $sakit = $atts->where('final_status', 'sakit')->count();
            $libur = $atts->where('final_status', 'libur')->count();

            $result[] = [
                'id'          => $act->id,
                'tanggal'     => $act->activity_date,
                'judul'       => $act->title,
                'tipe'        => $act->type,
                'mode'        => $act->attendance_mode,
                'total'       => $total,
                'hadir'       => $hadir,
                'hadir_tepat' => $hadirTepat,
                'telat'       => $telat,
                'alpha'       => $alpha,
                'izin'        => $izin,
                'sakit'       => $sakit,
                'libur'       => $libur,
                'pct'         => $total > 0 ? round($hadir / $total * 100, 1) : 0,
            ];
        }

        return $result;
    }

    /**
     * Rekap per siswa untuk satu eskul
     */
    public function getStudentSummary(int $eskulId, int $schoolYearId, array $filter = []): array
    {
        // Ambil semua anggota aktif eskul tahun ini
        $members = ExtracurricularMember::where('extracurricular_id', $eskulId)
            ->where('school_year_id', $schoolYearId)
            ->where('status', 'active')
            ->with(['user.currentAcademic'])
            ->get();

        // Ambil semua activity finished eskul ini
        $actQuery = Activity::where('extracurricular_id', $eskulId)
            ->where('school_year_id', $schoolYearId)
            ->where('attendance_phase', 'finished');

        if (!empty($filter['date_from'])) {
            $actQuery->whereDate('activity_date', '>=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $actQuery->whereDate('activity_date', '<=', $filter['date_to']);
        }

        $activityIds = $actQuery->pluck('id');
        $totalKegiatan = $activityIds->count();

        // Aggregate attendance per user
        $aggr = Attendance::whereIn('activity_id', $activityIds)
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN final_status='hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN final_status='hadir' AND checkin_status='late' THEN 1 ELSE 0 END) as telat"),
                DB::raw("SUM(CASE WHEN final_status='alpha' THEN 1 ELSE 0 END) as alpha"),
                DB::raw("SUM(CASE WHEN final_status='izin'  THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN final_status='sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN final_status='libur' THEN 1 ELSE 0 END) as libur")
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $result = [];
        foreach ($members as $member) {
            $user = $member->user;
            $a    = $aggr[$user->id] ?? null;
            $hadir = $a?->hadir ?? 0;
            $pct   = $totalKegiatan > 0 ? round($hadir / $totalKegiatan * 100, 1) : 0;

            $result[] = [
                'user_id'   => $user->id,
                'nama'      => $user->name,
                'nisn'      => $user->nisn,
                'kelas'     => optional($user->currentAcademic)->grade . ' ' . optional($user->currentAcademic)->class_label,
                'total'     => $a?->total ?? 0,
                'hadir'     => $hadir,
                'telat'     => $a?->telat ?? 0,
                'alpha'     => $a?->alpha ?? 0,
                'izin'      => $a?->izin  ?? 0,
                'sakit'     => $a?->sakit ?? 0,
                'libur'     => $a?->libur ?? 0,
                'pct'       => $pct,
            ];
        }

        // Sort by pct desc
        usort($result, fn($a, $b) => $b['pct'] <=> $a['pct']);

        return $result;
    }

    /**
     * Alpha warning: siswa dengan alpha >= threshold dalam satu eskul
     */
    public function getAlphaWarning(int $eskulId, int $schoolYearId, int $threshold = 3, array $filter = []): array
    {
        $actQuery = Activity::where('extracurricular_id', $eskulId)
            ->where('school_year_id', $schoolYearId)
            ->where('attendance_phase', 'finished');

        if (!empty($filter['date_from'])) {
            $actQuery->whereDate('activity_date', '>=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $actQuery->whereDate('activity_date', '<=', $filter['date_to']);
        }

        $activityIds = $actQuery->pluck('id');

        return Attendance::whereIn('activity_id', $activityIds)
            ->where('final_status', 'alpha')
            ->select('user_id', DB::raw('COUNT(*) as total_alpha'))
            ->groupBy('user_id')
            ->having('total_alpha', '>=', $threshold)
            ->with(['user'])
            ->get()
            ->map(function ($row) use ($eskulId, $activityIds) {
                // Ambil detail tanggal alpha
                $dates = Attendance::whereIn('activity_id', $activityIds)
                    ->where('user_id', $row->user_id)
                    ->where('final_status', 'alpha')
                    ->with(['activity'])
                    ->get()
                    ->map(fn($a) => optional($a->activity)->activity_date?->format('d M Y'))
                    ->filter()
                    ->values();

                return [
                    'user_id'     => $row->user_id,
                    'nama'        => optional($row->user)->name,
                    'nisn'        => optional($row->user)->nisn,
                    'total_alpha' => $row->total_alpha,
                    'tanggal'     => $dates->toArray(),
                ];
            })
            ->sortByDesc('total_alpha')
            ->values()
            ->toArray();
    }

    /**
     * Tren bulanan kehadiran untuk satu eskul (6 bulan terakhir / range filter)
     */
    public function getMonthlyTrend(int $eskulId, int $schoolYearId, int $months = 6): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();

            $actIds = Activity::where('extracurricular_id', $eskulId)
                ->where('school_year_id', $schoolYearId)
                ->where('attendance_phase', 'finished')
                ->whereBetween('activity_date', [$start, $end])
                ->pluck('id');

            $atts = Attendance::whereIn('activity_id', $actIds)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN final_status='hadir' THEN 1 ELSE 0 END) as hadir"),
                    DB::raw("SUM(CASE WHEN final_status='alpha' THEN 1 ELSE 0 END) as alpha"),
                    DB::raw("SUM(CASE WHEN final_status='telat' THEN 1 ELSE 0 END) as telat"),
                    DB::raw("SUM(CASE WHEN final_status='izin'  THEN 1 ELSE 0 END) as izin"),
                    DB::raw("SUM(CASE WHEN final_status='sakit' THEN 1 ELSE 0 END) as sakit")
                )->first();

            $total = $atts?->total ?? 0;
            $hadir = $atts?->hadir ?? 0;

            $result[] = [
                'label'   => $date->locale('id')->translatedFormat('M Y'),
                'total'   => $total,
                'hadir'   => $hadir,
                'alpha'   => $atts?->alpha ?? 0,
                'telat'   => $atts?->telat ?? 0,
                'izin'    => $atts?->izin  ?? 0,
                'sakit'   => $atts?->sakit ?? 0,
                'pct'     => $total > 0 ? round($hadir / $total * 100, 1) : 0,
                'kegiatan'=> $actIds->count(),
            ];
        }

        return $result;
    }

    /**
     * Detail per siswa (drill-down): riwayat lengkap satu siswa di satu eskul
     */
    public function getStudentDetail(int $userId, int $eskulId, int $schoolYearId, array $filter = []): array
    {
        $actQuery = Activity::where('extracurricular_id', $eskulId)
            ->where('school_year_id', $schoolYearId)
            ->where('attendance_phase', 'finished')
            ->orderBy('activity_date', 'desc');

        if (!empty($filter['date_from'])) {
            $actQuery->whereDate('activity_date', '>=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $actQuery->whereDate('activity_date', '<=', $filter['date_to']);
        }

        $activities = $actQuery->get();
        $activityIds = $activities->pluck('id');

        $attendances = Attendance::whereIn('activity_id', $activityIds)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('activity_id');

        $rows = [];
        foreach ($activities as $act) {
            $att = $attendances[$act->id] ?? null;
            $rows[] = [
                'activity_id' => $act->id,
                'tanggal'     => $act->activity_date->format('d M Y'),
                'judul'       => $act->title,
                'tipe'        => $act->type,
                'mode'        => $act->attendance_mode,
                'final_status'   => $att?->final_status ?? 'alpha',
                'checkin_status' => $att?->checkin_status ?? '-',
                'checkin_at'     => $att?->checkin_at?->format('H:i') ?? '-',
                'checkout_at'    => $att?->checkout_at?->format('H:i') ?? '-',
                'sumber'         => $att?->attendance_source ?? '-',
                'catatan'        => $att?->note ?? '-',
            ];
        }

        $total = count($rows);
        $hadir = collect($rows)->where('final_status', 'hadir')->count();
        $alpha = collect($rows)->where('final_status', 'alpha')->count();
        $telat = collect($rows)->where('final_status', 'hadir')->where('checkin_status', 'late')->count();
        $izin  = collect($rows)->where('final_status', 'izin')->count();
        $sakit = collect($rows)->where('final_status', 'sakit')->count();

        return [
            'rows'  => $rows,
            'total' => $total,
            'hadir' => $hadir,
            'alpha' => $alpha,
            'telat' => $telat,
            'izin'  => $izin,
            'sakit' => $sakit,
            'pct'   => $total > 0 ? round($hadir / $total * 100, 1) : 0,
        ];
    }

    // ══════════════════════════════════════════════════════════════════
    //  ADMIN ONLY — Global & Cross-Eskul Reports
    // ══════════════════════════════════════════════════════════════════

    /**
     * Global stats ringkasan seluruh sekolah
     */
    public function getGlobalSummary(int $schoolYearId, array $filter = []): array
    {
        $actQuery = Activity::where('school_year_id', $schoolYearId)
            ->where('attendance_phase', 'finished');

        if (!empty($filter['eskul_id'])) {
            $actQuery->where('extracurricular_id', $filter['eskul_id']);
        }
        if (!empty($filter['date_from'])) {
            $actQuery->whereDate('activity_date', '>=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $actQuery->whereDate('activity_date', '<=', $filter['date_to']);
        }

        $activityIds = $actQuery->pluck('id');
        $totalKegiatan = $activityIds->count();

        $atts = Attendance::whereIn('activity_id', $activityIds)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN final_status='hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN final_status='alpha' THEN 1 ELSE 0 END) as alpha"),
                DB::raw("SUM(CASE WHEN final_status='izin'  THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN final_status='sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN final_status='libur' THEN 1 ELSE 0 END) as libur"),
                DB::raw("SUM(CASE WHEN final_status='hadir' AND checkin_status='late' THEN 1 ELSE 0 END) as telat")
            )->first();

        $total = $atts?->total ?? 0;
        $hadir = $atts?->hadir ?? 0;

        return [
            'total_kegiatan' => $totalKegiatan,
            'total'          => $total,
            'hadir'          => $hadir,
            'alpha'          => $atts?->alpha ?? 0,
            'izin'           => $atts?->izin  ?? 0,
            'sakit'          => $atts?->sakit ?? 0,
            'libur'          => $atts?->libur ?? 0,
            'telat'          => $atts?->telat ?? 0,
            'pct'            => $total > 0 ? round($hadir / $total * 100, 1) : 0,
        ];
    }

    /**
     * Ranking eskul berdasarkan % kehadiran
     */
    public function getEskulRanking(int $schoolYearId, array $filter = []): array
    {
        $eskuls = Extracurricular::where('is_active', true)->get();
        $result = [];

        foreach ($eskuls as $eskul) {
            $actQuery = Activity::where('extracurricular_id', $eskul->id)
                ->where('school_year_id', $schoolYearId)
                ->where('attendance_phase', 'finished');

            if (!empty($filter['date_from'])) {
                $actQuery->whereDate('activity_date', '>=', $filter['date_from']);
            }
            if (!empty($filter['date_to'])) {
                $actQuery->whereDate('activity_date', '<=', $filter['date_to']);
            }

            $activityIds = $actQuery->pluck('id');
            if ($activityIds->isEmpty()) continue;

            $atts = Attendance::whereIn('activity_id', $activityIds)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN final_status='hadir' THEN 1 ELSE 0 END) as hadir"),
                    DB::raw("SUM(CASE WHEN final_status='alpha' THEN 1 ELSE 0 END) as alpha"),
                    DB::raw("SUM(CASE WHEN final_status='izin'  THEN 1 ELSE 0 END) as izin"),
                    DB::raw("SUM(CASE WHEN final_status='sakit' THEN 1 ELSE 0 END) as sakit"),
                    DB::raw("SUM(CASE WHEN final_status='hadir' AND checkin_status='late' THEN 1 ELSE 0 END) as telat")
                )->first();

            $total = $atts?->total ?? 0;
            $hadir = $atts?->hadir ?? 0;

            $result[] = [
                'id'       => $eskul->id,
                'nama'     => $eskul->name,
                'kegiatan' => $activityIds->count(),
                'anggota'  => ExtracurricularMember::where('extracurricular_id', $eskul->id)
                    ->where('school_year_id', $schoolYearId)
                    ->where('status', 'active')->count(),
                'total'    => $total,
                'hadir'    => $hadir,
                'alpha'    => $atts?->alpha ?? 0,
                'izin'     => $atts?->izin  ?? 0,
                'sakit'    => $atts?->sakit ?? 0,
                'telat'    => $atts?->telat ?? 0,
                'pct'      => $total > 0 ? round($hadir / $total * 100, 1) : 0,
            ];
        }

        usort($result, fn($a, $b) => $b['pct'] <=> $a['pct']);
        return $result;
    }

    /**
     * Laporan per pembina (kegiatan dibuat, selesai, cancelled, rata-rata kehadiran)
     */
    public function getPembinaReport(int $schoolYearId, array $filter = []): array
    {
        $pembinas = User::role('pembina')->where('is_active', true)->get();
        $result   = [];

        foreach ($pembinas as $pembina) {
            // Eskul yang diampu
            $eskulIds = \App\Models\ExtracurricularCoach::where('user_id', $pembina->id)
                ->pluck('extracurricular_id');

            $actQuery = Activity::where('school_year_id', $schoolYearId)
                ->whereIn('extracurricular_id', $eskulIds);

            if (!empty($filter['date_from'])) {
                $actQuery->whereDate('activity_date', '>=', $filter['date_from']);
            }
            if (!empty($filter['date_to'])) {
                $actQuery->whereDate('activity_date', '<=', $filter['date_to']);
            }

            $allActs     = $actQuery->get();
            $selesai     = $allActs->where('attendance_phase', 'finished');
            $cancelled   = $allActs->where('status', 'cancelled');
            $selesaiIds  = $selesai->pluck('id');

            $atts = Attendance::whereIn('activity_id', $selesaiIds)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN final_status='hadir' THEN 1 ELSE 0 END) as hadir")
                )->first();

            $total = $atts?->total ?? 0;
            $hadir = $atts?->hadir ?? 0;

            $result[] = [
                'user_id'      => $pembina->id,
                'nama'         => $pembina->name,
                'eskuls'       => Extracurricular::whereIn('id', $eskulIds)->pluck('name')->implode(', '),
                'total_kegiatan' => $allActs->count(),
                'selesai'      => $selesai->count(),
                'cancelled'    => $cancelled->count(),
                'pct'          => $total > 0 ? round($hadir / $total * 100, 1) : 0,
            ];
        }

        return $result;
    }

    /**
     * Laporan per siswa lintas eskul (admin)
     */
    public function getGlobalStudentReport(int $schoolYearId, array $filter = []): array
    {
        $query = User::role('siswa')
            ->where('is_active', true)
            ->with(['currentAcademic']);

        if (!empty($filter['grade'])) {
            $query->whereHas('currentAcademic', fn($q) => $q->where('grade', $filter['grade']));
        }
        if (!empty($filter['search'])) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%'.$filter['search'].'%')
                  ->orWhere('nisn', 'like', '%'.$filter['search'].'%');
            });
        }

        $siswa = $query->get();

        // Semua activities finished tahun ini
        $actQuery = Activity::where('school_year_id', $schoolYearId)
            ->where('attendance_phase', 'finished');

        if (!empty($filter['eskul_id'])) {
            $actQuery->where('extracurricular_id', $filter['eskul_id']);
        }

        $activityIds = $actQuery->pluck('id');

        // Aggregate per user
        $aggr = Attendance::whereIn('activity_id', $activityIds)
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN final_status='hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN final_status='alpha' THEN 1 ELSE 0 END) as alpha"),
                DB::raw("SUM(CASE WHEN final_status='izin'  THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN final_status='sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN final_status='hadir' AND checkin_status='late' THEN 1 ELSE 0 END) as telat")
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $result = [];
        foreach ($siswa as $s) {
            $a     = $aggr[$s->id] ?? null;
            $total = $a?->total ?? 0;
            $hadir = $a?->hadir ?? 0;

            if ($total === 0) continue; // skip siswa tanpa kegiatan

            $result[] = [
                'user_id' => $s->id,
                'nama'    => $s->name,
                'nisn'    => $s->nisn,
                'kelas'   => optional($s->currentAcademic)->grade . ' ' . optional($s->currentAcademic)->class_label,
                'total'   => $total,
                'hadir'   => $hadir,
                'alpha'   => $a?->alpha ?? 0,
                'telat'   => $a?->telat ?? 0,
                'izin'    => $a?->izin  ?? 0,
                'sakit'   => $a?->sakit ?? 0,
                'pct'     => $total > 0 ? round($hadir / $total * 100, 1) : 0,
            ];
        }

        usort($result, fn($a, $b) => $b['pct'] <=> $a['pct']);
        return $result;
    }

    /**
     * Monthly trend global (admin)
     */
    public function getGlobalMonthlyTrend(int $schoolYearId, int $months = 6, array $filter = []): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();

            $actQuery = Activity::where('school_year_id', $schoolYearId)
                ->where('attendance_phase', 'finished')
                ->whereBetween('activity_date', [$start, $end]);

            if (!empty($filter['eskul_id'])) {
                $actQuery->where('extracurricular_id', $filter['eskul_id']);
            }

            $actIds = $actQuery->pluck('id');

            $atts = Attendance::whereIn('activity_id', $actIds)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN final_status='hadir' THEN 1 ELSE 0 END) as hadir"),
                    DB::raw("SUM(CASE WHEN final_status='alpha' THEN 1 ELSE 0 END) as alpha"),
                    DB::raw("SUM(CASE WHEN final_status='telat' THEN 1 ELSE 0 END) as telat"),
                    DB::raw("SUM(CASE WHEN final_status='izin'  THEN 1 ELSE 0 END) as izin"),
                    DB::raw("SUM(CASE WHEN final_status='sakit' THEN 1 ELSE 0 END) as sakit")
                )->first();

            $total = $atts?->total ?? 0;
            $hadir = $atts?->hadir ?? 0;

            $result[] = [
                'label'    => $date->locale('id')->translatedFormat('M Y'),
                'total'    => $total,
                'hadir'    => $hadir,
                'alpha'    => $atts?->alpha ?? 0,
                'telat'    => $atts?->telat ?? 0,
                'izin'     => $atts?->izin  ?? 0,
                'sakit'    => $atts?->sakit ?? 0,
                'pct'      => $total > 0 ? round($hadir / $total * 100, 1) : 0,
                'kegiatan' => $actIds->count(),
            ];
        }
        return $result;
    }
}