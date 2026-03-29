<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityQrSession;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Carbon\Carbon;

class QrScanController extends Controller
{
    /**
     * BUKA SESI QR (Check-in atau Checkout)
     */
    public function openSession(Request $request, $eskul, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) {
        abort(403);
    }
     if ($activity->attendance_mode === 'manual') {
        return redirect()
            ->route('pembina.activity.manual_page', [$eskul, $activity->id])
            ->with('error', 'Aktivitas sedang menggunakan mode manual.');
    }

    // AUTO-SET mode ke 'qr' jika belum dipilih
    // (fallback safety: seharusnya sudah di-set via chooseMode)
    if (is_null($activity->attendance_mode)) {
        $activity->update(['attendance_mode' => 'qr']);
        $activity->refresh();
    }
    // LOCK MODE
    if (!in_array($activity->attendance_mode, [null, 'qr'])) {
        return redirect()
            ->route('pembina.activity.show', [$eskul, $activity->id])
            ->with('error', 'Aktivitas sedang menggunakan mode manual.');
    }

    $request->validate([
        'mode' => 'required|in:checkin,checkout',
        'duration_minutes' => 'required|integer|min:1',
        'late_tolerance_minutes' => 'required|integer|min:0',
    ]);

    if ($request->mode === 'checkout') {

    $existingCheckout = ActivityQrSession::where('activity_id',$activity->id)
        ->where('mode','checkout')
        ->exists();

    if ($existingCheckout) {
        return back()->with(
            'error',
            'Sesi checkout sudah pernah dilakukan. Silakan selesaikan kegiatan.'
        );
    }
}

    // PROTEKSI CHECKOUT
    if (
        $request->mode === 'checkout' &&
        $activity->attendance_phase !== 'checkout'
    ) {
        return back()->with(
            'error',
            'Checkout tidak bisa dibuka sebelum validasi selesai.'
        );
    }

    /*
    ======================================
    AUTO CLOSE SEMUA SESSION LAMA
    ======================================
    */
    ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->lockForUpdate()
        ->update([
            'expires_at' => now(),
            'is_active' => 0
        ]);

    $duration = (int) $request->duration_minutes;
    $late = (int) $request->late_tolerance_minutes;

    $session = ActivityQrSession::create([
        'activity_id' => $activity->id,
        'mode' => $request->mode,
        'duration_minutes' => $duration,
        'late_tolerance_minutes' => $late,
        'opened_at' => now(),
        'expires_at' => now()->addMinutes(
            $duration + ($request->mode === 'checkin' ? $late : 0)
        ),
        'secret_hash' => Str::random(32),
        'created_by' => auth()->id(),
        'is_active' => 1
    ]);

    /*
    ======================================
    UPDATE PHASE (JANGAN OVERWRITE FINISHED)
    ======================================
    */
    if ($activity->attendance_phase !== 'finished') {
        $activity->update([
            'attendance_phase' => $request->mode,
            'started_at' => $activity->started_at ?? now(),
        ]);
    }

    return redirect()->route(
        'pembina.activity.qr.scan_view',
        [$eskul, $activity->id]
    );
}
    public function closeSession($eskul, Activity $activity)
    {
        if (auth()->id() !== $activity->session_owner_id) abort(403);

        ActivityQrSession::where('activity_id', $activity->id)
            ->where('expires_at', '>', now())
            ->update([
                'expires_at' => now(),
                'is_active' => 0
            ]);

        return back()->with('success', 'Sesi QR berhasil ditutup.');
    }

    /**
     * PROSES SCAN QR (Check-in & Checkout)
     */
    public function scan(Request $request)
{
    $request->validate([
        'activity_id' => 'required|exists:activities,id',
        'qr_data' => 'required|string',
    ]);

    $activity = Activity::with('extracurricular')
        ->findOrFail($request->activity_id);

    if (auth()->id() !== $activity->session_owner_id) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak memiliki wewenang.'
        ], 403);
    }

    if ($activity->attendance_mode === 'manual') {
        return response()->json([
            'success' => false,
            'message' => 'Mode absensi bukan QR.'
        ]);
    }

    if ($activity->status === 'cancelled') {
        return response()->json([
            'success' => false,
            'message' => 'Kegiatan sudah diliburkan.'
        ]);
    }

    $session = ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->where('expires_at', '>', now())
        ->latest()
        ->first();

    if (!$session) {
        return response()->json([
            'success' => false,
            'message' => 'Sesi scan tidak aktif.'
        ]);
    }

    /*
    =========================
    HARD STOP SESSION EXPIRED
    =========================
    */
    if (now()->greaterThanOrEqualTo($session->expires_at)) {

        $session->update([
            'is_active' => 0
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Sesi scan telah berakhir.'
        ]);
    }

    /*
    =========================
    DECRYPT QR
    =========================
    */
    try {

        $payload = Crypt::decryptString($request->qr_data);
        $data = json_decode($payload, true);

        // FIX: dukung dua format
        $userId = $data['uid'] ?? $data['user_id'] ?? null;

        if (!$userId) {
            throw new \Exception();
        }

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'QR tidak valid.'
        ]);
    }

    $user = User::find($userId);
    $isMember = $activity->extracurricular
    ->members()
    ->where('user_id', $user->id)
    ->where('status', 'active')
    ->exists();

    if (!$isMember) {
        return response()->json([
            'success' => false,
            'message' => 'User bukan anggota ekstrakurikuler ini.'
        ]);
    }

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan.'
        ]);
    }

    /*
    =========================
    CHECKIN MODE
    =========================
    */
    if ($session->mode === 'checkin') {

       if ($activity->attendance_phase !== 'checkin') {
        return response()->json([
            'success' => false,
            'message' => 'Sesi check-in sudah ditutup.'
        ]);
    }

        $exists = Attendance::where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->first();

        if ($exists) {

    if ($exists->attendance_source === 'scan') {
        return response()->json([
            'success' => false,
            'message' => 'Sudah melakukan scan.'
        ]);
    }

    if (in_array($exists->final_status, ['izin','sakit','alpha'])) {
        return response()->json([
            'success' => false,
            'message' => 'Status sudah ditentukan oleh pembina.'
        ]);
        }
    }

        $status = now()->greaterThan(
            Carbon::parse($session->opened_at)
                ->addMinutes($session->duration_minutes)
        ) ? 'late' : 'on_time';

        Attendance::updateOrCreate(
            [
                'activity_id' => $activity->id,
                'user_id' => $user->id
            ],
            [
                'checkin_at' => now(),
                'checkin_status' => $status,
                'final_status' => 'hadir',
                'attendance_source' => 'scan',
                'updated_by' => auth()->id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $user->name . ' berhasil check-in.'
        ]);
    }

    /*
    =========================
    CHECKOUT MODE
    =========================
    */
    if ($activity->attendance_phase !== 'checkout') {
    return response()->json([
        'success' => false,
        'message' => 'Sesi checkout belum dibuka.'
    ]);
}
    $attendance = Attendance::where('activity_id', $activity->id)
        ->where('user_id', $user->id)
        ->first();

    if (!$attendance || !$attendance->checkin_at) {
        return response()->json([
            'success' => false,
            'message' => 'Belum check-in.'
        ]);
    }

    if ($attendance->checkout_at) {
        return response()->json([
            'success' => false,
            'message' => 'Sudah checkout.'
        ]);
    }

    $attendance->update([
        'checkout_at' => now()
    ]);

    return response()->json([
        'success' => true,
        'message' => $user->name . ' berhasil checkout.'
    ]);
}

public function scanView($eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) {
        abort(403);
    }

    if (!in_array($activity->attendance_mode, [null, 'qr'])) {
        return redirect()
            ->route('pembina.activity.manual_page', [$eskulId, $activity->id]);
    }

    if ($activity->status === 'cancelled') {
        return redirect()
            ->route('pembina.activity.show', [$eskulId, $activity->id])
            ->with('error', 'Kegiatan diliburkan.');
    }

    /*
    ======================================
    AUTO CLEAN SESSION EXPIRED
    ======================================
    */
    ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->where('expires_at', '<=', now())
        ->update([
            'is_active' => 0
        ]);

    $activeQrSession = ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->where('expires_at', '>', now())
        ->latest()
        ->first();

    if (!$activeQrSession) {

        return redirect()
            ->route('pembina.activity.show', [$eskulId, $activity->id])
            ->with('error', 'Sesi scan telah berakhir.');
    }

    $eskul = $activity->extracurricular;

    return view('pembina.activity.scan', compact(
        'activity',
        'eskul',
        'activeQrSession'
    ));
}
}