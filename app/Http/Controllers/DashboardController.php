<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $schoolYear = SchoolYear::current();

        if (!$schoolYear) {
            return view('dashboard', compact('user', 'schoolYear'));
        }

        $data = compact('user', 'schoolYear');

        if ($user->hasRole('admin')) {
            $data = array_merge($data, $this->adminData($schoolYear));
        } elseif ($user->hasRole('pembina')) {
            $data = array_merge($data, $this->pembinaData($user, $schoolYear));
        } else {
            $data = array_merge($data, $this->siswaData($user, $schoolYear));
        }

        return view('dashboard', $data);
    }

    /* =====================================================
       ADMIN
    ===================================================== */
    private function adminData(SchoolYear $schoolYear): array
    {
        // ── 1. SYSTEM OVERVIEW ─────────────────────────────
        $totalEskul         = Extracurricular::where('is_active', 1)->count();
        $totalEskulNonaktif = Extracurricular::where('is_active', 0)->count();
        $totalPembina = User::whereHas('roles', fn($q) => $q->where('name', 'pembina'))->where('is_active', 1)->count();
        $totalSiswa   = User::whereHas('roles', fn($q) => $q->where('name', 'siswa'))->where('is_active', 1)->count();
        $totalAnggotaAktif    = ExtracurricularMember::where('school_year_id', $schoolYear->id)->where('status', 'active')->count();
        $totalKegiatan        = Activity::where('school_year_id', $schoolYear->id)->count();
        $totalKegiatanSelesai = Activity::where('school_year_id', $schoolYear->id)->where('attendance_phase', 'finished')->count();
        $totalKegiatanBerjalan= Activity::where('school_year_id', $schoolYear->id)->whereIn('attendance_phase', ['checkin', 'checkout'])->count();

        // ── 2. KEGIATAN HARI INI ───────────────────────────
        $kegiatanToday = Activity::with('extracurricular')
            ->where('school_year_id', $schoolYear->id)
            ->whereDate('activity_date', today())
            ->orderBy('started_at')
            ->get();

        $kegiatanHariIni  = $kegiatanToday->count();
        $kegiatanCheckin  = $kegiatanToday->where('attendance_phase', 'checkin')->count();
        $kegiatanCheckout = $kegiatanToday->where('attendance_phase', 'checkout')->count();
        $kegiatanSelesaiHariIni = $kegiatanToday->where('attendance_phase', 'finished')->count();

        // ── 3. OVERALL ATTENDANCE RATE ─────────────────────
        $overallRate = DB::selectOne("
            SELECT COUNT(att.id) AS total,
                IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                IFNULL(SUM(att.final_status='telat'),0) AS telat,
                IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                IFNULL(SUM(att.final_status='sakit'),0) AS sakit,
                IFNULL(SUM(att.final_status='libur'),0) AS libur
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE act.school_year_id = ? AND act.attendance_phase = 'finished'
        ", [$schoolYear->id]);

        $schoolAttendanceRate = 0;
        if ($overallRate && $overallRate->total > 0) {
            $schoolAttendanceRate = round($overallRate->hadir / $overallRate->total * 100, 1);
        }

        // ── 4. STATUS DISTRIBUSI (Doughnut) ────────────────
        $statusDistribusi = DB::select("
            SELECT final_status AS label, COUNT(*) AS total
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE act.school_year_id = ?
            GROUP BY final_status ORDER BY total DESC
        ", [$schoolYear->id]);

        // ── 5. CHECKIN STATUS (Pie) ────────────────────────
        $checkinDistribusi = DB::select("
            SELECT checkin_status AS label, COUNT(*) AS total
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE act.school_year_id = ?
            GROUP BY checkin_status ORDER BY total DESC
        ", [$schoolYear->id]);

        // ── 6. SUMBER ABSENSI (Pie) ────────────────────────
        $sumberAbsensi = DB::select("
            SELECT attendance_source AS label, COUNT(*) AS total
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE act.school_year_id = ?
            GROUP BY attendance_source
        ", [$schoolYear->id]);

        // ── 7. MODE KEGIATAN (QR vs Manual) ───────────────
        $modeAbsensi = DB::select("
            SELECT attendance_mode AS label, COUNT(*) AS total
            FROM activities WHERE school_year_id = ? AND attendance_mode IS NOT NULL
            GROUP BY attendance_mode
        ", [$schoolYear->id]);

        // ── 8. MONTHLY TREND 6 BULAN (Line + Stacked Bar) ─
        $chartBulanan = DB::select("
            SELECT DATE_FORMAT(act.activity_date,'%Y-%m') AS bulan,
                DATE_FORMAT(act.activity_date,'%b %Y') AS label,
                COUNT(att.id) AS total,
                IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                IFNULL(SUM(att.final_status='telat'),0) AS telat,
                IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                IFNULL(SUM(att.final_status='sakit'),0) AS sakit
            FROM attendances att
            JOIN activities act ON act.id = att.activity_id
            WHERE act.school_year_id = ?
              AND act.activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
              AND act.attendance_phase = 'finished'
            GROUP BY bulan, label ORDER BY bulan ASC
        ", [$schoolYear->id]);

        // ── 9. KEGIATAN PER BULAN (Bar) ────────────────────
        $kegiatanPerBulan = DB::select("
            SELECT DATE_FORMAT(activity_date,'%Y-%m') AS bulan,
                DATE_FORMAT(activity_date,'%b %Y') AS label,
                COUNT(*) AS total,
                SUM(attendance_phase='finished') AS selesai
            FROM activities WHERE school_year_id = ?
              AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY bulan, label ORDER BY bulan ASC
        ", [$schoolYear->id]);

        // ── 10. GENDER SISWA (Doughnut) ────────────────────
        $genderDistribusi = DB::select("
            SELECT IFNULL(u.gender,'?') AS label, COUNT(*) AS total
            FROM users u
            JOIN model_has_roles mhr ON mhr.model_id = u.id
            JOIN roles r ON r.id = mhr.role_id
            WHERE r.name = 'siswa' AND u.is_active = 1
            GROUP BY u.gender
        ");

        // ── 11. HARI PALING AKTIF (Bar) ────────────────────
        $hariAktif = DB::select("
            SELECT DAYOFWEEK(activity_date) AS dow,
                ELT(DAYOFWEEK(activity_date),'Min','Sen','Sel','Rab','Kam','Jum','Sab') AS hari,
                COUNT(*) AS total
            FROM activities WHERE school_year_id = ?
            GROUP BY dow, hari ORDER BY dow
        ", [$schoolYear->id]);

        // ── 12. ESKUL RANKING ──────────────────────────────
        $eskulRanking = DB::select("
            SELECT e.id, e.name,
                COUNT(DISTINCT em.user_id) AS anggota,
                COUNT(DISTINCT act.id)     AS kegiatan,
                COUNT(att.id)              AS total_att,
                IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                IFNULL(SUM(att.final_status='telat'),0) AS telat,
                IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                IFNULL(SUM(att.final_status='sakit'),0) AS sakit,
                ROUND(CASE WHEN COUNT(att.id)>0 THEN SUM(att.final_status='hadir')/COUNT(att.id)*100 ELSE 0 END,1) AS pct
            FROM extracurriculars e
            LEFT JOIN extracurricular_members em
                ON em.extracurricular_id=e.id AND em.school_year_id=? AND em.status='active'
            LEFT JOIN activities act
                ON act.extracurricular_id=e.id AND act.school_year_id=?
            LEFT JOIN attendances att ON att.activity_id=act.id
            WHERE e.is_active=1
            GROUP BY e.id, e.name ORDER BY pct DESC
        ", [$schoolYear->id, $schoolYear->id]);

        // ── 13. ALPHA WARNING ──────────────────────────────
        $alphaWarning = DB::select("
            SELECT u.id, u.name, u.nisn, e.name AS eskul_name, COUNT(att.id) AS total_alpha
            FROM attendances att
            JOIN users u ON u.id=att.user_id
            JOIN activities act ON act.id=att.activity_id
            JOIN extracurriculars e ON e.id=act.extracurricular_id
            WHERE att.final_status='alpha' AND act.school_year_id=?
            GROUP BY u.id, u.name, u.nisn, e.name
            HAVING total_alpha >= 3 ORDER BY total_alpha DESC LIMIT 15
        ", [$schoolYear->id]);

        // ── 14. DELTA BULAN INI vs BULAN LALU ──────────────
        $thisMonth = DB::selectOne("
            SELECT COUNT(att.id) AS total, IFNULL(SUM(att.final_status='hadir'),0) AS hadir
            FROM attendances att JOIN activities act ON act.id=att.activity_id
            WHERE act.school_year_id=? AND act.attendance_phase='finished'
              AND MONTH(act.activity_date)=MONTH(CURDATE()) AND YEAR(act.activity_date)=YEAR(CURDATE())
        ", [$schoolYear->id]);

        $lastMonth = DB::selectOne("
            SELECT COUNT(att.id) AS total, IFNULL(SUM(att.final_status='hadir'),0) AS hadir
            FROM attendances att JOIN activities act ON act.id=att.activity_id
            WHERE act.school_year_id=? AND act.attendance_phase='finished'
              AND MONTH(act.activity_date)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))
              AND YEAR(act.activity_date)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))
        ", [$schoolYear->id]);

        $thisMonthPct   = ($thisMonth && $thisMonth->total > 0) ? round($thisMonth->hadir / $thisMonth->total * 100, 1) : 0;
        $lastMonthPct   = ($lastMonth && $lastMonth->total > 0) ? round($lastMonth->hadir / $lastMonth->total * 100, 1) : 0;
        $attendanceDelta= round($thisMonthPct - $lastMonthPct, 1);

        $eskulBarData = array_map(fn($e) => [
            'name'  => $e->name,
            'pct'   => (float) $e->pct,
            'hadir' => (int)   $e->hadir,
            'alpha' => (int)   $e->alpha,
        ], $eskulRanking);

        return compact(
            'totalEskul', 'totalEskulNonaktif', 'totalPembina', 'totalSiswa',
            'totalAnggotaAktif', 'totalKegiatan', 'totalKegiatanSelesai', 'totalKegiatanBerjalan',
            'kegiatanToday', 'kegiatanHariIni', 'kegiatanCheckin', 'kegiatanCheckout', 'kegiatanSelesaiHariIni',
            'overallRate', 'schoolAttendanceRate', 'thisMonthPct', 'attendanceDelta',
            'statusDistribusi', 'checkinDistribusi', 'sumberAbsensi', 'modeAbsensi',
            'chartBulanan', 'kegiatanPerBulan', 'genderDistribusi', 'hariAktif',
            'eskulRanking', 'eskulBarData', 'alphaWarning'
        );
    }

    /* =====================================================
       PEMBINA
    ===================================================== */
    private function pembinaData(User $user, SchoolYear $schoolYear): array
    {
        $eskulList = Extracurricular::whereHas('coaches', fn($q) => $q->where('user_id', $user->id))
            ->where('is_active', 1)->get();

        $eskulIds = $eskulList->pluck('id')->toArray();

        if (empty($eskulIds)) {
            return [
                'eskulList'               => $eskulList,
                'eskulStats'              => [],
                'eskulStatusChart'        => [],
                'eskulCheckinChart'       => [],
                'eskulTrendChart'         => [],
                'eskulMonthlyChart'       => [],
                'eskulAlphaWarning'       => [],
                'kegiatanTodayPembina'    => collect(),
                'totalKegiatanPembina'    => 0,
                'totalAnggotaPembina'     => 0,
                'recentActivitiesPembina' => collect(),
            ];
        }

        $totalKegiatanPembina = Activity::whereIn('extracurricular_id', $eskulIds)
            ->where('school_year_id', $schoolYear->id)->count();

        $totalAnggotaPembina = ExtracurricularMember::whereIn('extracurricular_id', $eskulIds)
            ->where('school_year_id', $schoolYear->id)->where('status', 'active')->count();

        $kegiatanTodayPembina = Activity::with('extracurricular')
            ->whereIn('extracurricular_id', $eskulIds)
            ->where('school_year_id', $schoolYear->id)
            ->whereDate('activity_date', today())->get();

        $recentActivitiesPembina = Activity::with('extracurricular')
            ->whereIn('extracurricular_id', $eskulIds)
            ->where('school_year_id', $schoolYear->id)
            ->where('attendance_phase', 'finished')
            ->orderByDesc('activity_date')->limit(8)->get();

        $eskulStats = $eskulStatusChart = $eskulCheckinChart = [];
        $eskulTrendChart = $eskulMonthlyChart = $eskulAlphaWarning = [];

        foreach ($eskulList as $eskul) {
            $eid = $eskul->id;

            $stat = DB::selectOne("
                SELECT COUNT(att.id) AS total,
                    IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                    IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                    IFNULL(SUM(att.final_status='telat'),0) AS telat,
                    IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                    IFNULL(SUM(att.final_status='sakit'),0) AS sakit,
                    IFNULL(SUM(att.checkin_status='on_time'),0) AS on_time,
                    IFNULL(SUM(att.checkin_status='late'),0)    AS late_ct
                FROM attendances att
                JOIN activities act ON act.id=att.activity_id
                WHERE act.extracurricular_id=? AND act.school_year_id=?
            ", [$eid, $schoolYear->id]);

            $pct = ($stat && $stat->total > 0) ? round($stat->hadir / $stat->total * 100, 1) : 0;

            $eskulStats[$eid] = [
                'total'    => (int)($stat->total ?? 0),
                'hadir'    => (int)($stat->hadir ?? 0),
                'alpha'    => (int)($stat->alpha ?? 0),
                'telat'    => (int)($stat->telat ?? 0),
                'izin'     => (int)($stat->izin ?? 0),
                'sakit'    => (int)($stat->sakit ?? 0),
                'on_time'  => (int)($stat->on_time ?? 0),
                'late_ct'  => (int)($stat->late_ct ?? 0),
                'pct'      => $pct,
            ];

            $eskulStatusChart[$eid] = [
                'hadir' => (int)($stat->hadir ?? 0),
                'alpha' => (int)($stat->alpha ?? 0),
                'telat' => (int)($stat->telat ?? 0),
                'izin'  => (int)($stat->izin ?? 0),
                'sakit' => (int)($stat->sakit ?? 0),
            ];

            $checkinRows = DB::select("
                SELECT DATE_FORMAT(act.activity_date,'%d/%m') AS tgl,
                    IFNULL(SUM(att.checkin_status='on_time'),0) AS on_time,
                    IFNULL(SUM(att.checkin_status='late'),0)    AS late
                FROM activities act
                LEFT JOIN attendances att ON att.activity_id=act.id
                WHERE act.extracurricular_id=? AND act.school_year_id=?
                  AND act.attendance_phase='finished'
                GROUP BY act.id, act.activity_date
                ORDER BY act.activity_date DESC LIMIT 10
            ", [$eid, $schoolYear->id]);
            $eskulCheckinChart[$eid] = array_reverse($checkinRows);

            $trendRows = DB::select("
                SELECT YEARWEEK(act.activity_date,3) AS week_key,
                    CONCAT('W',WEEK(MIN(act.activity_date),3)) AS label,
                    COUNT(att.id) AS total,
                    IFNULL(SUM(att.final_status='hadir'),0) AS hadir
                FROM activities act
                LEFT JOIN attendances att ON att.activity_id=act.id
                WHERE act.extracurricular_id=? AND act.school_year_id=?
                  AND act.activity_date >= DATE_SUB(CURDATE(), INTERVAL 56 DAY)
                  AND act.attendance_phase='finished'
                GROUP BY YEARWEEK(act.activity_date,3) ORDER BY week_key ASC
            ", [$eid, $schoolYear->id]);
            $eskulTrendChart[$eid] = array_map(fn($r) => [
                'label' => $r->label,
                'total' => (int)$r->total,
                'hadir' => (int)$r->hadir,
                'pct'   => $r->total > 0 ? round($r->hadir / $r->total * 100, 1) : 0,
            ], $trendRows);

            $monthRows = DB::select("
                SELECT DATE_FORMAT(act.activity_date,'%Y-%m') AS bulan,
                    DATE_FORMAT(act.activity_date,'%b') AS label,
                    COUNT(att.id) AS total,
                    IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                    IFNULL(SUM(att.final_status='alpha'),0) AS alpha
                FROM activities act
                LEFT JOIN attendances att ON att.activity_id=act.id
                WHERE act.extracurricular_id=? AND act.school_year_id=?
                  AND act.activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                  AND act.attendance_phase='finished'
                GROUP BY bulan, label ORDER BY bulan ASC
            ", [$eid, $schoolYear->id]);
            $eskulMonthlyChart[$eid] = array_map(fn($r) => [
                'label' => $r->label,
                'total' => (int)$r->total,
                'hadir' => (int)$r->hadir,
                'alpha' => (int)$r->alpha,
                'pct'   => $r->total > 0 ? round($r->hadir / $r->total * 100, 1) : 0,
            ], $monthRows);

            $eskulAlphaWarning[$eid] = DB::select("
                SELECT u.id, u.name, u.nisn, COUNT(att.id) AS total_alpha
                FROM attendances att
                JOIN users u ON u.id=att.user_id
                JOIN activities act ON act.id=att.activity_id
                WHERE att.final_status='alpha' AND act.extracurricular_id=? AND act.school_year_id=?
                GROUP BY u.id, u.name, u.nisn HAVING total_alpha >= 3
                ORDER BY total_alpha DESC LIMIT 10
            ", [$eid, $schoolYear->id]);
        }

        return compact(
            'eskulList', 'eskulStats', 'eskulStatusChart',
            'eskulCheckinChart', 'eskulTrendChart', 'eskulMonthlyChart', 'eskulAlphaWarning',
            'kegiatanTodayPembina', 'totalKegiatanPembina', 'totalAnggotaPembina',
            'recentActivitiesPembina'
        );
    }

    /* =====================================================
       SISWA
    ===================================================== */
    private function siswaData(User $user, SchoolYear $schoolYear): array
    {
        $memberships = ExtracurricularMember::where('user_id', $user->id)
            ->where('status', 'active')->with('extracurricular')->get();

        $eskulRiwayat  = [];
        $siswaChartData= [];

        foreach ($memberships as $member) {
            $eskul = $member->extracurricular;
            if (!$eskul) continue;

            $stat = DB::selectOne("
                SELECT COUNT(att.id) AS total,
                    IFNULL(SUM(att.final_status='hadir'),0) AS hadir,
                    IFNULL(SUM(att.final_status='alpha'),0) AS alpha,
                    IFNULL(SUM(att.final_status='telat'),0) AS telat,
                    IFNULL(SUM(att.final_status='izin'),0)  AS izin,
                    IFNULL(SUM(att.final_status='sakit'),0) AS sakit,
                    IFNULL(SUM(att.checkin_status='on_time'),0) AS on_time,
                    IFNULL(SUM(att.checkin_status='late'),0)    AS late_ct
                FROM attendances att
                JOIN activities act ON act.id=att.activity_id
                WHERE att.user_id=? AND act.extracurricular_id=? AND act.school_year_id=?
            ", [$user->id, $eskul->id, $schoolYear->id]);

            $pct = ($stat && $stat->total > 0) ? round($stat->hadir / $stat->total * 100, 1) : 0;

            $eskulRiwayat[] = (object)[
                'eskul'   => $eskul,
                'total'   => (int)($stat->total ?? 0),
                'hadir'   => (int)($stat->hadir ?? 0),
                'alpha'   => (int)($stat->alpha ?? 0),
                'telat'   => (int)($stat->telat ?? 0),
                'izin'    => (int)($stat->izin ?? 0),
                'sakit'   => (int)($stat->sakit ?? 0),
                'on_time' => (int)($stat->on_time ?? 0),
                'late_ct' => (int)($stat->late_ct ?? 0),
                'pct'     => $pct,
            ];

            $monthTrend = DB::select("
                SELECT DATE_FORMAT(act.activity_date,'%b') AS label,
                    COUNT(att.id) AS total,
                    IFNULL(SUM(att.final_status='hadir'),0) AS hadir
                FROM attendances att
                JOIN activities act ON act.id=att.activity_id
                WHERE att.user_id=? AND act.extracurricular_id=? AND act.school_year_id=?
                  AND act.activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                  AND act.attendance_phase='finished'
                GROUP BY DATE_FORMAT(act.activity_date,'%Y-%m'), label
                ORDER BY DATE_FORMAT(act.activity_date,'%Y-%m') ASC
            ", [$user->id, $eskul->id, $schoolYear->id]);

            $siswaChartData[$eskul->id] = [
                'status' => [
                    'hadir' => (int)($stat->hadir ?? 0),
                    'alpha' => (int)($stat->alpha ?? 0),
                    'telat' => (int)($stat->telat ?? 0),
                    'izin'  => (int)($stat->izin ?? 0),
                    'sakit' => (int)($stat->sakit ?? 0),
                ],
                'trend' => array_map(fn($r) => [
                    'label' => $r->label,
                    'total' => (int)$r->total,
                    'hadir' => (int)$r->hadir,
                    'pct'   => $r->total > 0 ? round($r->hadir / $r->total * 100, 1) : 0,
                ], $monthTrend),
            ];
        }

        $riwayatTerbaru = Attendance::where('user_id', $user->id)
            ->with(['activity' => fn($q) => $q->with('extracurricular')])
            ->orderByDesc('updated_at')->limit(10)->get();

        return compact('memberships', 'eskulRiwayat', 'siswaChartData', 'riwayatTerbaru');
    }
}