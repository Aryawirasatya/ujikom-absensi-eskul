<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\ActivityQrSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * 1. INPUT MANUAL PERORANGAN
     */
    public function markManual(Request $request, $eskulId, Activity $activity)
    {
        if (auth()->id() !== $activity->session_owner_id) abort(403);

        if ($activity->status === 'cancelled') {
            return back()->withErrors('Kegiatan sudah diliburkan.');
        }

        if ($activity->attendance_phase === 'finished') {
            return back()->withErrors('Kegiatan sudah selesai dan terkunci.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status'  => 'required|in:hadir,izin,sakit,alpha',
            'note'    => 'nullable|string'
        ]);

        $attendance = Attendance::where('activity_id', $activity->id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($attendance && $attendance->attendance_source === 'scan') {
            return back()->withErrors('Tidak bisa mengubah siswa yang sudah scan QR.');
        }

        Attendance::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $request->user_id],
            [
                'checkin_at'        => ($request->status === 'hadir') ? now() : null,
                'checkin_status'    => ($request->status === 'hadir') ? 'on_time' : 'absent',
                'final_status'      => $request->status,
                'attendance_source' => 'manual',
                'note'              => $request->note,
                'updated_by'        => auth()->id(),
            ]
        );

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    /**
     * 2. INPUT MANUAL MASSAL (AJAX)
     */
    public function bulkMarkManual(Request $request, $eskulId, Activity $activity)
    {
        if (auth()->id() !== $activity->session_owner_id) abort(403);

        $request->validate([
            'user_ids'       => 'required|array',
            'user_ids.*'     => 'exists:users,id',
            'status'         => 'required|in:hadir,izin,sakit,alpha',
        ]);

        DB::transaction(function () use ($request, $activity) {
            foreach ($request->user_ids as $userId) {
                $attendance = Attendance::where('activity_id', $activity->id)
                    ->where('user_id', $userId)->first();

                if ($attendance && $attendance->attendance_source === 'scan') continue;

                Attendance::updateOrCreate(
                    ['activity_id' => $activity->id, 'user_id' => $userId],
                    [
                        'checkin_at'        => ($request->status === 'hadir') ? now() : null,
                        'checkin_status'    => ($request->status === 'hadir') ? 'on_time' : 'absent',
                        'final_status'      => $request->status,
                        'attendance_source' => 'manual',
                        'updated_by'        => auth()->id(),
                    ]
                );
            }

            if ($activity->attendance_phase === 'not_started' && $activity->status !== 'cancelled') {
                $activity->update(['attendance_phase' => 'checkin', 'started_at' => now()]);
            }

            if ($request->is_full_manual) {
                $activity->update(['attendance_phase' => 'checkout']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($request->user_ids) . ' siswa berhasil diperbarui.'
        ]);
    }

    /**
     * 3. FINALISASI VALIDASI (Checkin → Checkout) — khusus mode QR
     */
    public function finalizeValidation(Request $request, $eskulId, Activity $activity)
    {
        if (auth()->id() !== $activity->session_owner_id) abort(403);

        if ($activity->attendance_phase !== 'checkin') {
            return back()->with('error', 'Checkout tidak bisa dibuka sebelum sesi check-in dimulai.');
        }

        $manualData = $request->manual_data ?? [];
        $members    = $activity->extracurricular->members()->where('status', 'active')->get();

        DB::transaction(function () use ($members, $manualData, $activity) {
            foreach ($members as $member) {
                $att = Attendance::where('activity_id', $activity->id)
                    ->where('user_id', $member->user_id)->first();

                if (!$att || !$att->final_status) {
                    $status = $manualData[$member->user_id] ?? 'alpha';
                    Attendance::updateOrCreate(
                        ['activity_id' => $activity->id, 'user_id' => $member->user_id],
                        [
                            'checkin_at'        => ($status === 'hadir') ? now() : null,
                            'checkin_status'    => ($status === 'hadir') ? 'on_time' : 'absent',
                            'final_status'      => $status,
                            'attendance_source' => ($status === 'alpha') ? 'system' : 'manual',
                            'updated_by'        => auth()->id(),
                        ]
                    );
                }
            }
            $activity->update(['attendance_phase' => 'checkout']);
        });

        return redirect()
            ->route('pembina.activity.show', [$eskulId, $activity->id])
            ->with('success', 'Validasi selesai. Sekarang buka sesi checkout.');
    }

    /**
     * 4. SELESAIKAN AKTIVITAS
     */
    public function finishActivity($eskulId, Activity $activity)
    {
        if (auth()->id() !== $activity->session_owner_id) abort(403);

        // Mode QR: wajib ada sesi checkin & checkout
        if ($activity->attendance_mode === 'qr') {
            $hasCheckin  = $activity->qrSessions()->where('mode', 'checkin')->exists();
            $hasCheckout = $activity->qrSessions()->where('mode', 'checkout')->exists();

            if (!$hasCheckin || !$hasCheckout) {
                return redirect()
                    ->route('pembina.activity.show', [$eskulId, $activity->id])
                    ->with('error', 'Anda harus membuka sesi Check-in dan Checkout minimal satu kali.');
            }
        }

        // Mode manual: cukup ada attendance yang tercatat
        if ($activity->attendance_mode === 'manual') {
            $hasAttendance = Attendance::where('activity_id', $activity->id)->exists();
            if (!$hasAttendance) {
                return redirect()
                    ->route('pembina.activity.manual_page', [$eskulId, $activity->id])
                    ->with('error', 'Belum ada data absensi yang dicatat.');
            }
        }

        $activity->qrSessions()->where('is_active', 1)->update([
            'expires_at' => now(), 'is_active' => 0
        ]);

        $attendances = Attendance::where('activity_id',$activity->id)
            ->whereNotNull('checkin_at')
            ->whereNull('checkout_at')
            ->get();

        foreach($attendances as $att){
            $att->update([
                'note' => 'checkin tanpa checkout'
            ]);
        }

        $activity->update(['attendance_phase' => 'finished', 'ended_at' => now()]);

        return redirect()
            ->route('pembina.activity.index', $activity->extracurricular_id)
            ->with('success', 'Kegiatan selesai dan absensi terkunci.');
    }

    /**
     * 5. PILIH MODE ABSENSI
     * Mode dikunci setelah dipilih pertama kali — tidak bisa diubah.
     */
    public function chooseMode(Request $request, $eskulId, Activity $activity)
    {
        if (auth()->id() !== $activity->session_owner_id) abort(403);

        $request->validate(['mode' => 'required|in:qr,manual']);

        // Jika mode sudah terkunci, arahkan ke halaman yang sesuai
        if (!is_null($activity->attendance_mode)) {
            if ($activity->attendance_mode === 'manual') {
                return redirect()->route('pembina.activity.manual_page', [$eskulId, $activity->id]);
            }
            return redirect()->route('pembina.activity.show', [$eskulId, $activity->id]);
        }

        // Set mode untuk pertama kali
        $activity->update(['attendance_mode' => $request->mode]);
        
        $activity->refresh();
        
        if ($request->mode === 'manual') {
            return redirect()->route('pembina.activity.manual_page', [$eskulId, $activity->id]);
        }

        return redirect()->route('pembina.activity.show', [$eskulId, $activity->id]);
    }
    

    /**
     * 6. HALAMAN ABSENSI MANUAL
     */
    public function manualPage($eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    // Fallback: set mode manual jika belum ada (safety)
    if (is_null($activity->attendance_mode)) {
        $activity->update(['attendance_mode' => 'manual']);
        $activity->refresh();
    }

 

    $eskul         = $activity->extracurricular;
    $activeMembers = $eskul->members()
        ->where('status', 'active')
        ->with('user')
        ->paginate(15);
    $attendances   = $activity->attendances()->get()->keyBy('user_id');

    $summary = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
    foreach ($attendances as $a) {
        if (isset($summary[$a->final_status])) $summary[$a->final_status]++;
    }

    $isCancelled = $activity->status === 'cancelled';
    $isFinished  = $activity->attendance_phase === 'finished';

    return view('pembina.activity.manual', compact(
        'activity', 'eskul', 'activeMembers', 'attendances', 'summary', 'isCancelled', 'isFinished'
    ));
}


    public function manualCheckin(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    if ($activity->attendance_phase === 'finished') {
        return response()->json(['success' => false, 'message' => 'Absensi sudah dikunci.']);
    }

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'status'  => 'required|in:hadir,izin,sakit,alpha',
    ]);
// return response()->json([
//     'success' => true,
//     'debugging' => true,
//     'data' => $request->all()
// ]);

    $checkinAt = ($request->status === 'hadir') ? now() : null;

    Attendance::updateOrCreate(
        ['activity_id' => $activity->id, 'user_id' => $request->user_id],
        [
            'checkin_at'        => $checkinAt,
            'checkin_status'    => ($request->status === 'hadir') ? 'on_time' : 'absent',
            'final_status'      => $request->status,
            'attendance_source' => 'manual',
            'updated_by'        => auth()->id(),
        ]
    );

    // Otomatis mulai fase checkin jika masih not_started
    if ($activity->attendance_phase === 'not_started') {
        $activity->update(['attendance_phase' => 'checkin', 'started_at' => now()]);
    }

    return response()->json([
        'success'      => true,
        'message'      => 'Status berhasil disimpan.',
        'checkin_time' => $checkinAt ? $checkinAt->format('H:i') : null,
    ]);
}

/**
 * 8. BULK CHECKIN MANUAL (AJAX)
 * Tandai semua / pilihan anggota sekaligus saat fase masuk
 */
public function bulkManualCheckin(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    $request->validate([
        'user_ids'   => 'required|array',
        'user_ids.*' => 'exists:users,id',
        'status'     => 'required|in:hadir,izin,sakit,alpha',
    ]);

    $checkinAt = ($request->status === 'hadir') ? now() : null;

    DB::transaction(function () use ($request, $activity, $checkinAt) {
        foreach ($request->user_ids as $userId) {
            Attendance::updateOrCreate(
                ['activity_id' => $activity->id, 'user_id' => $userId],
                [
                    'checkin_at'        => $checkinAt,
                    'checkin_status'    => ($request->status === 'hadir') ? 'on_time' : 'absent',
                    'final_status'      => $request->status,
                    'attendance_source' => 'manual',
                    'updated_by'        => auth()->id(),
                ]
            );
        }

        if ($activity->attendance_phase === 'not_started') {
            $activity->update(['attendance_phase' => 'checkin', 'started_at' => now()]);
        }
    });

    return response()->json([
        'success'      => true,
        'message'      => count($request->user_ids) . ' anggota berhasil ditandai.',
        'checkin_time' => $checkinAt ? $checkinAt->format('H:i') : null,
    ]);
}

public function manualStartCheckin($eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    if ($activity->attendance_phase !== 'not_started') {
        return redirect()
            ->route('pembina.activity.manual_page', [$eskulId, $activity->id])
            ->with('error', 'Sesi sudah dimulai.');
    }

    $activity->update([
        'attendance_phase' => 'checkin',
        'started_at'       => now(),
    ]);

    return redirect()
        ->route('pembina.activity.manual_page', [$eskulId, $activity->id])
        ->with('success', 'Sesi masuk dibuka. Silakan isi kehadiran anggota.');
}

/**
 * 9. BUKA SESI PULANG MANUAL
 * Transisi phase: checkin → checkout
 * Anggota yang masih PENDING otomatis jadi ALPHA
 */
public function manualOpenCheckout(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    if ($activity->attendance_phase !== 'checkin') {
        return back()->with('error', 'Sesi masuk belum aktif.');
    }

    $members = $activity->extracurricular->members()->where('status', 'active')->get();

    DB::transaction(function () use ($members, $activity) {
        // Auto-alpha anggota yang pending
        foreach ($members as $member) {
            $att = Attendance::where('activity_id', $activity->id)
                ->where('user_id', $member->user_id)->first();

            if (!$att || is_null($att->final_status)) {
                Attendance::updateOrCreate(
                    ['activity_id' => $activity->id, 'user_id' => $member->user_id],
                    [
                        'checkin_at'        => null,
                        'checkin_status'    => 'absent',
                        'final_status'      => 'alpha',
                        'attendance_source' => 'system',
                        'updated_by'        => auth()->id(),
                    ]
                );
            }
        }

        $activity->update(['attendance_phase' => 'checkout']);
    });

    return redirect()
        ->route('pembina.activity.manual_page', [$eskulId, $activity->id])
        ->with('success', 'Sesi pulang dibuka. Silakan lakukan checkout untuk anggota yang hadir.');
}

/**
 * 10. CHECKOUT MANUAL PERORANGAN (AJAX)
 * Mencatat checkout_at untuk anggota yang hadir
 */
public function manualCheckout(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    if ($activity->attendance_phase !== 'checkout') {
        return response()->json(['success' => false, 'message' => 'Sesi pulang belum dibuka.']);
    }

    $request->validate(['user_id' => 'required|exists:users,id']);

    $attendance = Attendance::where('activity_id', $activity->id)
        ->where('user_id', $request->user_id)
        ->first();

    if (!$attendance || $attendance->final_status !== 'hadir') {
        return response()->json(['success' => false, 'message' => 'Anggota ini tidak hadir.']);
    }

    if ($attendance->checkout_at) {
        return response()->json(['success' => false, 'message' => 'Sudah checkout.']);
    }

    $checkoutAt = now();
    $attendance->update(['checkout_at' => $checkoutAt]);

    return response()->json([
        'success'       => true,
        'message'       => 'Checkout berhasil.',
        'checkout_time' => $checkoutAt->format('H:i'),
    ]);
}

/**
 * 11. BULK CHECKOUT MANUAL (AJAX)
 * Checkout semua anggota yang hadir sekaligus
 */
public function bulkManualCheckout(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    if ($activity->attendance_phase !== 'checkout') {
        return response()->json(['success' => false, 'message' => 'Sesi pulang belum dibuka.']);
    }

    $checkoutAt = now();
    $count = 0;

    DB::transaction(function () use ($activity, $checkoutAt, &$count) {
        $attendances = Attendance::where('activity_id', $activity->id)
            ->where('final_status', 'hadir')
            ->whereNull('checkout_at')
            ->get();

        foreach ($attendances as $att) {
            $att->update(['checkout_at' => $checkoutAt]);
            $count++;
        }
    });

    return response()->json([
        'success' => true,
        'message' => $count . ' anggota berhasil di-checkout.',
    ]);
}

public function closeCheckout(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) {
        abort(403);
    }

    if ($activity->attendance_phase !== 'checkout') {
        return back()->with('error', 'Sesi checkout belum aktif.');
    }

    $ids = json_decode($request->manual_checkout_ids, true) ?? [];

    foreach ($ids as $userId) {

        Attendance::where('activity_id', $activity->id)
            ->where('user_id', $userId)
            ->whereNull('checkout_at')
            ->update([
                'checkout_at' => now()
            ]);

    }

    // Tutup semua sesi checkout aktif
    $activity->qrSessions()
        ->where('mode', 'checkout')
        ->where('is_active', 1)
        ->update([
            'is_active' => 0,
            'expires_at' => now()
        ]);

    return redirect()
        ->route('pembina.activity.show', [$eskulId, $activity->id])
        ->with('success', 'Sesi checkout berhasil ditutup.');
}

}