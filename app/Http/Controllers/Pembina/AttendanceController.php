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

    try {

        DB::transaction(function () use ($request, $activity) {

            $attendance = Attendance::where('activity_id', $activity->id)
                ->where('user_id', $request->user_id)
                ->lockForUpdate()
                ->first();

            // ❌ jangan timpa hasil scan
            if ($attendance && $attendance->attendance_source === 'scan') {
                throw new \Exception('Tidak bisa mengubah siswa yang sudah scan QR.');
            }

            $checkinAt = ($request->status === 'hadir') ? now() : null;

            $result = $this->resolveCheckinStatus($activity, $checkinAt);

            Attendance::updateOrCreate(
                [
                    'activity_id' => $activity->id,
                    'user_id'     => $request->user_id
                ],
                [
                    'checkin_at'        => $checkinAt,
                    'checkin_status'    => $result['status'],
                    'late_minutes'      => $result['late_minutes'],
                    'early_minutes'     => $result['early_minutes'],
                    'final_status'      => $request->status,
                    'attendance_source' => 'manual',
                    'updated_by'        => auth()->id(),
                ]
            );
        });

    } catch (\Throwable $e) {

        return back()->withErrors($e->getMessage());
    }

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

            $checkinAt = ($request->status === 'hadir') ? now() : null;

            $result = $this->resolveCheckinStatus($activity, $checkinAt);

            Attendance::updateOrCreate(
    ['activity_id' => $activity->id, 'user_id' => $userId],
    [
        'checkin_at'        => $checkinAt,
        'checkin_status'    => $result['status'],
        'late_minutes'      => $result['late_minutes'],
        'early_minutes'     => $result['early_minutes'],
        'final_status'      => $request->status,
        'attendance_source' => 'manual',
        'updated_by'        => auth()->id(),
    ]
);
        }

        if ($activity->attendance_phase === 'not_started') {
            $activity->update(['attendance_phase' => 'checkin']);
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
    /**
 * Perbaikan Poin 3: Jembatan Check-in ke Checkout
 */
public function finalizeValidation(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    $members = $activity->extracurricular->members()->where('status', 'active')->get();

    DB::transaction(function () use ($members, $activity) {
        foreach ($members as $member) {
            $existing = Attendance::where('activity_id', $activity->id)
                ->where('user_id', $member->user_id)
                ->first();

            // Jika belum ada data sama sekali (belum scan/manual/izin), set ke Alpha Sistem
            if (!$existing) {
                Attendance::create([
                    'activity_id'       => $activity->id,
                    'user_id'           => $member->user_id,
                    'checkin_status'    => 'absent',
                    'final_status'      => 'alpha',
                    'attendance_source' => 'system',
                ]);
            }
            // Siswa yang sudah berstatus 'hadir' tapi belum punya jam pulang
            // tidak diutak-atik di sini agar bisa ikut fase checkout.
        }
        
        $activity->update(['attendance_phase' => 'checkout']);
    });

    return redirect()
        ->route('pembina.activity.show', [$eskulId, $activity->id])
        ->with('success', 'Validasi selesai. Sesi masuk ditutup, silakan buka sesi pulang.');
}

    /**
     * 4. SELESAIKAN AKTIVITAS
     */
   

    public function finishActivity($eskulId, Activity $activity)
    {
        // 1. Keamanan: Pastikan hanya pemilik sesi yang bisa menutup kegiatan
        if (auth()->id() !== $activity->session_owner_id) abort(403);

        // 2. Validasi Kelengkapan Absensi berdasarkan Mode
        if ($activity->attendance_mode === 'qr') {
            $hasCheckin  = $activity->qrSessions()->where('mode', 'checkin')->exists();
            $hasCheckout = $activity->qrSessions()->where('mode', 'checkout')->exists();

            if (!$hasCheckin || !$hasCheckout) {
                return redirect()->route('pembina.activity.show', [$eskulId, $activity->id])
                    ->with('error', 'Sesi Check-in dan Checkout harus pernah dibuka sebelum mengakhiri kegiatan.');
            }
        }

        if ($activity->attendance_mode === 'manual') {
            if (!\App\Models\Attendance::where('activity_id', $activity->id)->exists()) {
                return redirect()->route('pembina.activity.manual_page', [$eskulId, $activity->id])
                    ->with('error', 'Belum ada data absensi yang diinput.');
            }
        }

        // 3. Tutup semua sesi QR yang masih aktif
        $activity->qrSessions()
            ->where('is_active', 1)
            ->update([
                'expires_at' => now(),
                'is_active' => 0
            ]);
            
            // 4. Ambil data pendukung (Optimasi agar tidak N+1 query)
            $rules = \App\Models\PointRule::all();
            $members = $activity->extracurricular
            ->members()
            ->where('status', 'active')
            ->with('user') // Eager loading user
            ->get();
            
            $activity->update([
                'attendance_phase' => 'finished',
                'ended_at' => now()
            ]);
        // 5. Eksekusi Logika dalam Database Transaction
        DB::transaction(function () use ($members, $activity, $rules) {

            foreach ($members as $member) {
                $user = $member->user;

                // Kunci baris absensi agar tidak diubah proses lain saat penghitungan poin
                $att = \App\Models\Attendance::where('activity_id', $activity->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                // A. Pastikan record absensi ada (Jika belum ada, otomatis jadi Alpha)
                if (!$att) {
                    $att = \App\Models\Attendance::create([
                        'activity_id' => $activity->id,
                        'user_id' => $user->id,
                        'checkin_status' => 'absent',
                        'final_status' => 'alpha',
                        'attendance_source' => 'system',
                    ]);
                }

                // B. Pemrosesan Token Otomatis (Alpha -> Izin)
                if ($att->final_status === 'alpha') {
                    $token = \App\Models\UserToken::where('user_id', $user->id)
                    ->where('status', 'AVAILABLE') // Sesuai ENUM DB
                    ->whereHas('item', fn($q) => $q->where('token_type', 'free_alpha'))
                    ->lockForUpdate()
                    ->first();

                    if ($token) {
                        $att->update([
                            'final_status' => 'izin',
                            'note' => 'Alpha → jadi izin (token)'
                        ]);

                        $token->update([
                            'status' => 'USED',
                            'used_at_attendance_id' => $att->id
                        ]);
                    }
                }

                // D. Pemrosesan Lupa Checkout
              $checkoutStatus = null;
                // Cek jika siswa sudah melakukan check-in tapi lupa scan pulang (checkout_at kosong)
                if ($att->checkin_at && !$att->checkout_at && $att->final_status === 'hadir') {
                    
                    // Cari token forget_checkout milik siswa yang masih tersedia
                    $token = \App\Models\UserToken::where('user_id', $user->id)
                        ->where('status', 'AVAILABLE')
                        ->whereHas('item', fn($q) => $q->where('token_type', 'forget_checkout'))
                        ->lockForUpdate()
                        ->first();

                    if ($token) {
                        // --- LOGIKA AUTO-INJECT ---
                        // Kita ambil jam berakhir sesi atau jam berakhir eskul terjadwal
                        $autoTime = $activity->ended_at ?? now();

                        $att->update([
                            'checkout_at' => $autoTime, 
                            'note' => 'Checkout otomatis diselamatkan voucher'
                        ]);

                        $token->update([
                            'status' => 'USED', 
                            'used_at_attendance_id' => $att->id
                        ]);
                        
                        $checkoutStatus = 'completed'; // Dianggap checkout lengkap oleh Rule Engine
                    } else {
                        // Jika tidak punya token, biarkan kosong dan beri catatan denda
                        $att->update(['note' => 'Lupa checkout (Sistem)']);
                        $checkoutStatus = 'not_completed';
                    }
                }

                // Jika memang dari awal sudah ada data checkout_at (scan manual/QR normal)
                if ($att->checkout_at) {
                    $checkoutStatus = 'completed';
                }

                // E. Rule Engine (Penghitungan Poin Akhir)
                // E. Rule Engine (Penghitungan Poin Akhir)
            // -----------------------------------------------------------------
            
            // 1. Ambil User dengan Lock agar saldo tidak berubah selama proses ini
            $userData = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

            $isPresent = in_array($att->final_status, ['hadir','telat']);

            $context = [
                'checkin_status'  => $att->checkin_status,
                'final_status'    => ($att->checkin_status === 'late') ? 'telat' : $att->final_status,
                'checkout_status' => $checkoutStatus,
                'late_minutes'    => $isPresent ? ($att->late_minutes ?? 0) : null,
                'checkin_time'    => $att->checkin_at ? $att->checkin_at->format('H:i') : null,
                'checkout_time'   => $att->checkout_at ? $att->checkout_at->format('H:i') : null,
            ];

            foreach ($rules as $rule) {
                    // 1. Skip rule berbasis waktu jika siswa tidak hadir fisik (Alpha/Izin/Sakit)
                    $timeRelatedFields = ['late_minutes', 'checkin_time', 'checkout_time'];
                    if (in_array($rule->condition_field, $timeRelatedFields) && !$isPresent) {
                        continue;
                    }

                    // 2. Evaluasi apakah kondisi pada Rule Engine terpenuhi
                    if (!$this->evaluateRule($rule, $context)) {
                        continue;
                    }

                    // 3. Safety Check: Hindari duplikasi poin untuk rule yang sama di satu absensi
                    $alreadyRegistered = \App\Models\PointLedger::where('user_id', $user->id)
                        ->where('attendance_id', $att->id)
                        ->where('description', 'like', $rule->rule_name . '%')
                        ->exists();

                    if ($alreadyRegistered) {
                        continue;
                    }

                    // 4. Update Saldo di Tabel Users secara ATOMIC
                    // Menjalankan SQL: UPDATE users SET point_balance = point_balance + X
                    $userData->increment('point_balance', $rule->point_modifier);
                    
                    // 5. REFRESH: Sinkronisasi ulang data di memori PHP dengan database setelah increment
                    // Ini krusial agar current_balance di Ledger mencatatkan angka yang akurat 100%
                    $userData->refresh();

                    // 6. Catat di Ledger (Audit Trail / Riwayat)
                    \App\Models\PointLedger::create([
                        'user_id'          => $user->id,
                        'attendance_id'    => $att->id,
                        'transaction_type' => $rule->point_modifier >= 0 ? 'EARN' : 'PENALTY',
                        'amount'           => abs($rule->point_modifier),
                        'current_balance'  => $userData->point_balance, // Saldo snapshot yang sudah sinkron
                        'description'      => $rule->rule_name . " (" . $activity->title . ")"
                    ]);
                }
            }
        });

        // 6. Finalisasi Status Aktivitas

        return redirect()
            ->route('pembina.activity.index', $activity->extracurricular_id)
            ->with('success', 'Kegiatan berhasil diselesaikan dan poin seluruh anggota telah dihitung.');
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

    $userId = $request->user_id;
    $checkinAt = ($request->status === 'hadir') ? now() : null;
    $result = $this->resolveCheckinStatus($activity, $checkinAt);
    
    $note = null;
    $tokenUsedId = null;

    // 1. INTEGRASI TOKEN: Cek Voucher Bebas Telat jika statusnya Late
    if ($request->status === 'hadir' && $result['status'] === 'late') {
        $token = \App\Models\UserToken::where('user_id', $userId)
            ->where('status', 'AVAILABLE')
            ->whereHas('item', fn($q) => $q->where('token_type', 'late_forgiveness'))
            ->lockForUpdate() // Kunci agar tidak dipakai double jika barengan scan
            ->first();

        if ($token) {
            $result['status'] = 'on_time';
            $result['late_minutes'] = 0;
            $note = 'Terlambat (Ditebus via Voucher)';
            $tokenUsedId = $token->id;
        }
    }

    DB::transaction(function () use ($request, $activity, $checkinAt, $result, $note, $tokenUsedId, $userId) {
        $attendance = Attendance::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $userId],
            [
                'checkin_at'        => $checkinAt,
                'checkin_status'    => $result['status'],
                'late_minutes'      => $result['late_minutes'],
                'early_minutes'     => $result['early_minutes'],
                'final_status'      => $request->status,
                'attendance_source' => 'manual',
                'note'              => $note,
                'updated_by'        => auth()->id(),
            ]
        );

        // 2. Tandai Token sebagai TERPAKAI (Jika tadi ditemukan)
        if ($tokenUsedId) {
            \App\Models\UserToken::where('id', $tokenUsedId)->update([
                'status' => 'USED',
                'used_at_attendance_id' => $attendance->id
            ]);
        }
    });

    if ($activity->attendance_phase === 'not_started') {
        $activity->update(['attendance_phase' => 'checkin']);
    }

    return response()->json([
        'success' => true,
        'message' => $note ?? 'Status berhasil disimpan.',
        'checkin_time' => $checkinAt ? $checkinAt->format('H:i') : null,
        'token_used' => $tokenUsedId ? true : false // Kirim info ke UI jika perlu
    ]);
}
/**
 * Perbaikan Poin 2: Mendukung Voucher Bebas Telat pada Input Massal
 */
public function bulkManualCheckin(Request $request, $eskulId, Activity $activity)
{
    if (auth()->id() !== $activity->session_owner_id) abort(403);

    // 1. HAPUS VALIDASI user_ids DI SINI
    $request->validate([
        'status' => 'required|in:hadir,izin,sakit,alpha',
    ]);

    // 2. AMBIL SEMUA ID ANGGOTA DARI DB (Bukan dari request)
    // Ini solusi agar semua siswa di halaman 1 dan 2 ikut ter-absen
    $userIds = $activity->extracurricular->members()
        ->where('status', 'active')
        ->pluck('user_id');

    if ($userIds->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'Tidak ada anggota aktif.']);
    }

    DB::transaction(function () use ($request, $activity, $userIds) {
        foreach ($userIds as $userId) {
            $checkinAt = ($request->status === 'hadir') ? now() : null;
            $result = $this->resolveCheckinStatus($activity, $checkinAt);
            $note = null;
            $tokenUsedId = null;

            // CEK VOUCHER (Integrasi Marketplace)
            if ($request->status === 'hadir' && $result['status'] === 'late') {
                $token = \App\Models\UserToken::where('user_id', $userId)
                    ->where('status', 'AVAILABLE')
                    ->whereHas('item', fn($q) => $q->where('token_type', 'late_forgiveness'))
                    ->first();

                if ($token) {
                    $result['status'] = 'on_time';
                    $result['late_minutes'] = 0;
                    $note = 'Terlambat (Ditebus via Voucher)';
                    $tokenUsedId = $token->id;
                }
            }

            $attendance = Attendance::updateOrCreate(
                ['activity_id' => $activity->id, 'user_id' => $userId],
                [
                    'checkin_at'        => $checkinAt,
                    'checkin_status'    => $result['status'],
                    'late_minutes'      => $result['late_minutes'],
                    'early_minutes'     => $result['early_minutes'],
                    'final_status'      => $request->status,
                    'attendance_source' => 'manual',
                    'note'              => $note,
                    'updated_by'        => auth()->id(),
                ]
            );

            if ($tokenUsedId) {
                \App\Models\UserToken::where('id', $tokenUsedId)->update([
                    'status' => 'USED',
                    'used_at_attendance_id' => $attendance->id
                ]);
            }
        }

        if ($activity->attendance_phase === 'not_started') {
            $activity->update(['attendance_phase' => 'checkin']);
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Seluruh anggota (' . $userIds->count() . ') berhasil diperbarui.'
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
    ]);

    return redirect()
        ->route('pembina.activity.manual_page', [$eskulId, $activity->id])
        ->with('success', 'Sesi masuk dibuka. Silakan isi kehadiran anggota.');
}

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
        return response()->json([
            'success' => false,
            'message' => 'Sesi pulang belum dibuka.'
        ]);
    }

    $request->validate([
        'user_id' => 'required|exists:users,id'
    ]);

    try {

        $checkoutAt = DB::transaction(function () use ($request, $activity) {

            $attendance = Attendance::where('activity_id', $activity->id)
                ->where('user_id', $request->user_id)
                ->lockForUpdate()
                ->first();

            if (!$attendance || $attendance->final_status !== 'hadir') {
                throw new \Exception('Anggota ini tidak hadir.');
            }

            if ($attendance->checkout_at) {
                throw new \Exception('Sudah checkout.');
            }

            $time = now();

            $attendance->update([
                'checkout_at' => $time
            ]);

            return $time;
        });

        return response()->json([
            'success' => true,
            'message' => 'Checkout berhasil.',
            'checkout_time' => $checkoutAt->format('H:i'),
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
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
private function resolveCheckinStatus(Activity $activity, $checkinAt)
{
    if (!$checkinAt) {
        return ['status' => 'absent', 'late_minutes' => 0, 'early_minutes' => 0];
    }

    // Pastikan started_at adalah objek Carbon
    $start = \Carbon\Carbon::parse($activity->started_at);
    
    // Pastikan checkinAt (yang biasanya bernilai now()) adalah objek Carbon
    $checkin = \Carbon\Carbon::parse($checkinAt);

    // 1. Kondisi Tepat Waktu (Sama dengan atau sebelum jam mulai)
    if ($checkin->lte($start)) {
        return [
            'status' => 'on_time',
            'late_minutes' => 0,
            // diffInMinutes akan menghasilkan angka absolut selisih menit
            'early_minutes' => (int) $checkin->diffInMinutes($start)
        ];
    }

    // 2. Kondisi Telat (Setelah jam mulai)
    return [
        'status' => 'late',
        'late_minutes' => (int) $checkin->diffInMinutes($start),
        'early_minutes' => 0
    ];
}
private function evaluateRule($rule, $context)
{
    $field = $rule->condition_field;

    // Jika field tidak ada di konteks atau bernilai null, rule dianggap tidak terpenuhi
    if (!isset($context[$field]) || is_null($context[$field])) {
        return false;
    }

    $value = $context[$field];
    $target = $rule->condition_value;
    $target2 = $rule->condition_value_2;

    // Casting ke integer jika berhubungan dengan menit untuk perbandingan matematis yang akurat
    if ($field === 'late_minutes') {
        $value = (int) $value;
        $target = (int) $target;
        $target2 = (int) $target2;
    }

    switch ($rule->condition_operator) {
        case '=':  return $value == $target;
        case '>':  return $value > $target;
        case '<':  return $value < $target;
        case '>=': return $value >= $target;
        case '<=': return $value <= $target;
        case 'BETWEEN':
            return $value >= $target && $value <= $target2;
        default:
            return false;
    }
}
}