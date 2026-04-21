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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    // Pastikan aktivitas masih aktif
    if ($activity->status !== 'active') {
        return back()->with('error', 'Aktivitas sudah berakhir atau dibatalkan.');
    }

    if ($activity->attendance_mode === 'manual') {
        return redirect()
            ->route('pembina.activity.manual_page', [$eskul, $activity->id])
            ->with('error', 'Aktivitas sedang menggunakan mode manual.');
    }

    if (is_null($activity->attendance_mode)) {
        $activity->update(['attendance_mode' => 'qr']);
    }

    $request->validate([
        'mode' => 'required|in:checkin,checkout',
    ]);

    // Proteksi fase checkout
    if ($request->mode === 'checkout' && $activity->attendance_phase === 'not_started') {
        return back()->with('error', 'Belum bisa membuka sesi checkout sebelum aktivitas dimulai.');
    }

    // Tutup semua sesi aktif sebelumnya milik aktivitas ini
    ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->update([
            'expires_at' => now(),
            'is_active' => 0
        ]);

    // Buat sesi QR baru
    ActivityQrSession::create([
        'activity_id' => $activity->id,
        'mode' => $request->mode,
        'opened_at' => now(),
        'expires_at' => now()->addMinutes(120), // Perpanjang waktu agar tidak cepat mati
        'secret_hash' => Str::random(32),
        'created_by' => auth()->id(),
        'is_active' => 1
    ]);

    // Update fase kehadiran di tabel Activity
    if ($activity->attendance_phase !== 'finished') {
        $activity->update([
            'attendance_phase' => $request->mode
        ]);
    }

    return redirect()->route(
        'pembina.activity.qr.scan_view',
        [$eskul, $activity->id]
    );
}

    /**
     * TUTUP SESI QR SECARA MANUAL
     */
    public function closeSession($eskul, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->update([
            'expires_at' => now(),
            'is_active' => 0
        ]);

    return back()->with('success', 'Sesi QR ditutup.');
}

    /**
     * PROSES PEMINDAIAN SCAN QR (Check-in & Checkout)
     */
    public function scan(Request $request)
{
    $request->validate([
        'activity_id' => 'required|exists:activities,id',
        'qr_data'     => 'required|string',
    ]);

    $activity = Activity::with(['extracurricular'])->findOrFail($request->activity_id);

    if (auth()->id() !== $activity->session_owner_id) {
        return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
    }

    $session = ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->where('expires_at', '>', now())
        ->latest()
        ->first();

    if (!$session) {
        return response()->json(['success' => false, 'message' => 'Sesi QR sudah berakhir.'], 410);
    }

    // Dekripsi Data Siswa
    try {
        $payload = Crypt::decryptString($request->qr_data);
        $data = json_decode($payload, true);
        $userId = $data['uid'] ?? $data['user_id'] ?? null;
        if (!$userId) throw new \Exception();
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'QR Code tidak valid.'], 422);
    }

    $user = User::find($userId);
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
    }

    // Cek Keanggotaan
    if (!$activity->extracurricular->members()->where('user_id', $user->id)->where('status', 'active')->exists()) {
        return response()->json(['success' => false, 'message' => 'Siswa bukan anggota eskul ini.'], 403);
    }

    /* --- PROSES CHECK-IN --- */
    if ($session->mode === 'checkin') {
        $now = now();
        $startTime = Carbon::parse($activity->started_at);

        try {
            $result = DB::transaction(function () use ($activity, $user, $now, $startTime) {
                $attendance = Attendance::where('activity_id', $activity->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($attendance && $attendance->checkin_at) {
                    throw new \Exception('Sudah melakukan absen masuk.');
                }

                $isLate = $now->gt($startTime);
               $lateMinutes = $isLate ? (int) $now->diffInMinutes($startTime) : 0;
                if ($lateMinutes < 0) $lateMinutes = 0; // Safety net

                $earlyMinutes = !$isLate ? (int) $now->diffInMinutes($startTime) : 0;
                if ($earlyMinutes < 0) $earlyMinutes = 0; // Safety net
                $status = $isLate ? 'late' : 'on_time';
                $note = null;
                $tokenUsed = false;

                // LOGIKA TOKEN TERLAMBAT
                if ($isLate) {
                    $token = \App\Models\UserToken::where('user_id', $user->id)
                        ->where('status', 'AVAILABLE') 
                        ->whereHas('item', fn($q) => $q->where('token_type', 'late_forgiveness'))
                        ->lockForUpdate()
                        ->first();

                    if ($token) {
                        $status = 'on_time';
                        $lateMinutes = 0;
                        $tokenUsed = true;
                        $note = 'Terlambat (Diselamatkan Token)';
                    }
                }

                $newAttendance = Attendance::updateOrCreate(
                    ['activity_id' => $activity->id, 'user_id' => $user->id],
                    [
                        'checkin_at'        => $now,
                        'checkin_status'    => $status,
                        'late_minutes'      => $lateMinutes,
                        'early_minutes'     => $earlyMinutes,
                        'final_status'      => 'hadir',
                        'attendance_source' => 'scan',
                        'note'              => $note,
                        'updated_by'        => auth()->id(),
                    ]
                );

                if ($tokenUsed && isset($token)) {
                    $token->update([
                        'status' => 'USED',
                        'used_at_attendance_id' => $newAttendance->id
                    ]);
                }

              return [
                    'name'          => $user->name,
                    'nisn'          => $user->nisn, // Tambahkan ini
                    'class'         => $user->currentAcademic->class_label ?? '-', // Tambahkan ini
                    'checkin_status'        => strtoupper($status),
                    'late_minutes'  => $lateMinutes, // Kirim angka aslinya
                    'early_minutes' => $earlyMinutes, // Kirim angka aslinya
                    'token_used'    => $tokenUsed
                ];
            });

            return response()->json(array_merge(['success' => true, 'mode' => 'checkin'], $result));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 200);
        }
    }

    /* --- PROSES CHECKOUT --- */
    if ($session->mode === 'checkout') {
        $attendance = Attendance::where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Belum absen masuk.'], 200);
        }

        if ($attendance->checkout_at) {
            return response()->json(['success' => false, 'message' => 'Sudah checkout.'], 200);
        }

        $attendance->update([
            'checkout_at' => now(),
            'checkout_status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'mode'    => 'checkout',
            'name'    => $user->name,
            'nisn'    => $user->nisn, // Tambahkan ini
            'class'   => $user->currentAcademic->class_label ?? '-', // Tambahkan ini
            'message' => 'Checkout berhasil.'
        ]);
    }
}

    /**
     * HALAMAN VIEW SCANNER (Kamera Pembina)
     */
    public function scanView($eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    if ($activity->attendance_mode === 'manual') {
        return redirect()->route('pembina.activity.manual_page', [$eskulId, $activity->id]);
    }

    // Bersihkan sesi yang kadaluarsa secara diam-diam
    ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->where('expires_at', '<=', now())
        ->update(['is_active' => 0]);

    $activeQrSession = ActivityQrSession::where('activity_id', $activity->id)
        ->where('is_active', 1)
        ->where('expires_at', '>', now())
        ->latest()
        ->first();

    if (!$activeQrSession) {
        return redirect()->route('pembina.activity.show', [$eskulId, $activity->id])
            ->with('error', 'Tidak ada sesi scan yang aktif.');
    }

    $eskul = $activity->extracurricular;

    return view('pembina.activity.scan', compact('activity', 'eskul', 'activeQrSession'));
}
}