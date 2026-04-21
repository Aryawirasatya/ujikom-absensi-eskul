<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Extracurricular;
use App\Models\ExtracurricularSchedule;
use App\Models\ScheduleException;
use App\Models\SchoolYear;
use App\Models\User; // Tambahkan ini
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ActivityQrSession;
use App\Models\Attendance;
class ActivityController extends Controller
{
    /**
     * Halaman Utama Absensi (Menampilkan daftar aktivitas & Auto-create rutin)
     */
    public function index(Extracurricular $eskul)
{
    $schoolYear = SchoolYear::current();
    $today = Carbon::today();

    // =============================
    // AUTO CANCEL STALE
    // =============================
    $stalActivities = Activity::where('extracurricular_id', $eskul->id)
        ->where('school_year_id', $schoolYear->id)
        ->where('attendance_phase', 'not_started')
        ->where('status', 'active')
        ->whereDate('activity_date', '<', $today)
        ->get();

    foreach ($stalActivities as $stale) {

        $members = $eskul->members()->where('status', 'active')->get();

        foreach ($members as $member) {
            Attendance::updateOrCreate(
                [
                    'activity_id' => $stale->id,
                    'user_id'     => $member->user_id,
                ],
                [
                    'checkin_at'        => null,
                    'checkout_at'       => null,
                    'checkin_status'    => 'absent',
                    'final_status'      => 'libur',
                    'attendance_source' => 'system',
                    'updated_by'        => null,
                ]
            );
        }

        $stale->update([
            'status'           => 'cancelled',
            'attendance_phase' => 'finished',
            'cancel_reason'    => 'Sesi tidak dibuka oleh pembina (otomatis dibatalkan sistem).',
            'cancelled_at'     => now(),
        ]);

        if ($stale->type === 'routine' && $stale->schedule_id) {
            ScheduleException::firstOrCreate(
                [
                    'schedule_id'    => $stale->schedule_id,
                    'exception_date' => $stale->activity_date,
                ],
                [
                    'status'      => 'cancelled',
                    'reason'      => 'Otomatis dibatalkan sistem karena sesi tidak dibuka.',
                    'reported_by' => $stale->session_owner_id ?? $stale->created_by,
                ]
            );
        }
    }

    // =============================
    // AUTO CREATE ROUTINE (FIXED TIME)
    // =============================
    $schedule = ExtracurricularSchedule::where('extracurricular_id', $eskul->id)
        ->where('day_of_week', $today->dayOfWeekIso)
        ->where('is_active', 1)
        ->first();

    if ($schedule) {
        $isException = ScheduleException::where('schedule_id', $schedule->id)
            ->whereDate('exception_date', $today)
            ->exists();

        if (!$isException) {
            $activityExists = Activity::where('extracurricular_id', $eskul->id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('activity_date', $today)
                ->exists();

            if (!$activityExists) {

                $startDateTime = Carbon::parse(
                    $today->format('Y-m-d') . ' ' . $schedule->start_time
                );

                // 🔥 LANGSUNG PAKAI DARI SCHEDULE
                $checkinOpenAt = $schedule->checkin_open_at
                    ? Carbon::parse($today->format('Y-m-d') . ' ' . $schedule->checkin_open_at)
                    : $startDateTime; // fallback

                Activity::create([
                    'school_year_id'     => $schoolYear->id,
                    'extracurricular_id' => $eskul->id,
                    'schedule_id'        => $schedule->id,
                    'session_owner_id'   => auth()->id(),
                    'type'               => 'routine',
                    'title'              => 'Absensi Rutin ' . $eskul->name,
                    'activity_date'      => $today,
                    'started_at'         => $startDateTime,
                    'checkin_open_at'    => $checkinOpenAt, // ✅ FIX FINAL
                    'status'             => 'active',
                    'attendance_phase'   => 'not_started',
                    'created_by'         => auth()->id(),
                ]);
            }
        }
    }

    $activities = Activity::where('extracurricular_id', $eskul->id)
        ->where('school_year_id', $schoolYear->id)
        ->orderBy('activity_date', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()
        ->view('pembina.activity.index', compact('eskul', 'activities'))
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
}
    /**
     * Membuat Kegiatan Non-Rutin secara manual
     */
    public function storeNonRoutine(Request $request, Extracurricular $eskul)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'activity_date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        // ✅ VALIDASI OTOMATIS: Jam buka harus SEBELUM atau SAMA DENGAN jam mulai
        'checkin_open_time' => 'required|date_format:H:i|before_or_equal:start_time',
    ], [
        // Custom message agar user paham kesalahannya
        'checkin_open_time.before_or_equal' => 'Absensi tidak boleh dibuka setelah jam kegiatan dimulai.',
    ]);

    // Parsing untuk database
    $startDateTime = Carbon::parse($request->activity_date . ' ' . $request->start_time);
    $checkinOpenAt = Carbon::parse($request->activity_date . ' ' . $request->checkin_open_time);

    Activity::create([
        'school_year_id'     => SchoolYear::current()->id,
        'extracurricular_id' => $eskul->id,
        'session_owner_id'   => auth()->id(),
        'type'               => 'non_routine',
        'title'              => $request->title,
        'activity_date'      => $request->activity_date,
        'started_at'         => $startDateTime,
        'checkin_open_at'    => $checkinOpenAt,
        'status'             => 'active',
        'attendance_phase'   => 'not_started',
        'created_by'         => auth()->id(),
    ]);

    return back()->with('success', 'Kegiatan non-rutin berhasil dibuat.');
}

    /**
     * Halaman Detail Absensi (Realtime View)
     */
    // Tambahkan di App\Http\Controllers\Pembina\ActivityController

public function show(Request $request, $eskulId, Activity $activity)
{
    $eskul = $activity->extracurricular;

   $mode = $activity->attendance_mode ?? 'qr';

       if (
        $activity->attendance_phase === 'not_started' &&
        is_null($activity->attendance_mode) &&
        $activity->status !== 'cancelled'
    ) {
        return redirect()
            ->route('pembina.activity.index', $eskulId)
            ->with('info', 'Silakan pilih mode absensi terlebih dahulu.');
    }

    $allMembers = $eskul->members()
    ->where('status', 'active')
    ->with('user')
    ->get();

    $activeMembers = $eskul->members()
        ->where('status', 'active')
        ->with('user')
        ->paginate(15);

    $attendances = $activity->attendances()
    ->get()
    ->keyBy('user_id');

    $sudahHadir = [];
    $belumCheckout = [];
    $belumAbsen = [];

    foreach ($allMembers  as $member) {

        $att = $attendances[$member->user_id] ?? null;

       if (!$att) {
            $belumAbsen[] = $member;
            continue;
        }

        if (in_array($att->final_status, ['izin','sakit','libur'])) {
            $sudahHadir[] = $member;
        }
        elseif ($att->checkin_at && $att->checkout_at) {
            $sudahHadir[] = $member;
        }
        elseif ($att->checkin_at && !$att->checkout_at) {
            $belumCheckout[] = $member;
        }
        elseif ($att->final_status === 'alpha') {
            $belumAbsen[] = $member;
        }
        else {
            $belumAbsen[] = $member;
        }
    }

    // AUTO NONAKTIFKAN SESSION EXPIRED
ActivityQrSession::where('activity_id', $activity->id)
    ->where('is_active', 1)
    ->where('expires_at', '<=', now())
    ->update(['is_active' => 0]);

$activeQrSession = $activity->qrSessions()
    ->where('is_active', 1)
    ->where('expires_at', '>', now())
    ->latest()
    ->first();

    return view('pembina.activity.qr', compact(
        'activity',
        'eskul',
        'mode',
        'activeMembers',
        'attendances',
        'sudahHadir',
        'belumCheckout',
        'belumAbsen',
        'activeQrSession',
    ));
}
/**
 * Liburkan / Batalkan Kegiatan
 * Fungsi ini menggabungkan proteksi fase dan pencatatan exception jadwal.
 */
public function cancel(Request $request, $eskulId, Activity $activity)
{
    // 1. LOGIKA PROTEKSI: Jangan biarkan libur jika sudah ada proses (scan/manual)
    if ($activity->attendance_phase !== 'not_started') {
        return back()->with('error', 'Gagal: Kegiatan tidak bisa diliburkan karena proses absensi sudah berjalan.');
    }

    // 2. AUTHENTICATION: Pastikan hanya owner atau pembina utama yang bisa
    if (
        auth()->id() !== $activity->session_owner_id && 
        auth()->id() !== optional($activity->extracurricular->primaryCoach)->user_id
    ) {
        abort(403);
    }

    $request->validate([
        'reason' => 'required|string|max:255'
    ]);

    // 3. TUTUP SEMUA SESI QR (Jika ada yang iseng buka session tapi belum ada yang scan)
    \App\Models\ActivityQrSession::where('activity_id', $activity->id)
        ->where('expires_at', '>', now())
        ->update(['expires_at' => now(), 'is_active' => 0]);

    // 4. SET STATUS SEMUA ANGGOTA MENJADI 'LIBUR'
    $members = $activity->extracurricular
        ->members()
        ->where('status', 'active')
        ->get();

    foreach ($members as $member) {
        Attendance::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $member->user_id],
            [
                'checkin_at' => null,
                'checkout_at' => null,
                'checkin_status' => 'absent',
                'final_status' => 'libur',
                'attendance_source' => 'system',
                'updated_by' => auth()->id()
            ]
        );
    }

    // 5. UPDATE STATUS AKTIVITAS
    $activity->update([
        'status' => 'cancelled',
        'attendance_phase' => 'finished', // Langsung finish karena sudah batal
        'cancel_reason' => $request->reason,
        'cancelled_at' => now(),
    ]);

    // 6. CATAT EXCEPTION JADWAL: Agar sistem auto-create tidak membuat lagi untuk hari ini
    if ($activity->type === 'routine' && $activity->schedule_id) {
        ScheduleException::firstOrCreate(
            [
                'schedule_id' => $activity->schedule_id,
                'exception_date' => $activity->activity_date,
            ],
            [
                'status' => 'cancelled',
                'reason' => $request->reason,
                'reported_by' => auth()->id(),
            ]
        );
    }

    return back()->with('success', 'Kegiatan berhasil diliburkan. Semua status siswa menjadi LIBUR.');
}
 
}