<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    private function authorizeCoach(Extracurricular $eskul)
    {
        abort_unless(
            $eskul->coaches()
                ->where('user_id', Auth::id())
                ->exists(),
            403
        );
    }

    public function index(Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);

        $schedules = $eskul->schedules()->get();

        return view('pembina.schedules.index', compact('eskul','schedules'));
    }

    public function store(Request $request, Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);

        // ✅ VALIDASI BARU (PAKAI JAM)
        $request->validate([
            'day_of_week'      => 'required|integer|min:1|max:7',
            'start_time'       => 'required|date_format:H:i',
            'checkin_open_at'  => 'required|date_format:H:i',
        ]);

        // ✅ VALIDASI LOGIKA (PENTING BANGET)
        if ($request->checkin_open_at >= $request->start_time) {
            return back()->withErrors('Jam buka absensi harus sebelum jam masuk');
        }

        // ❌ CEGAH DUPLIKAT HARI
        if (
            $eskul->schedules()
                ->where('day_of_week', $request->day_of_week)
                ->exists()
        ) {
            return back()->withErrors('Hari tersebut sudah ada');
        }

        // ✅ CREATE DATA BARU (TANPA MENIT)
        ExtracurricularSchedule::create([
            'extracurricular_id' => $eskul->id,
            'day_of_week'        => $request->day_of_week,
            'start_time'         => $request->start_time,
            'checkin_open_at'    => $request->checkin_open_at,
            'is_active'          => 1
        ]);

        return back()->with('success', 'Jadwal ditambahkan');
    }

    public function toggle($eskul, ExtracurricularSchedule $schedule)
    {
        $this->authorizeCoach($schedule->extracurricular);

        $schedule->update([
            'is_active' => !$schedule->is_active
        ]);

        return back();
    }

    public function destroy(Extracurricular $eskul, ExtracurricularSchedule $schedule)
{
    $this->authorizeCoach($eskul);

    // Optional tapi bagus (biar aman relasi)
    if ($schedule->extracurricular_id !== $eskul->id) {
        abort(404);
    }

    $schedule->delete();

    return back()->with('success', 'Jadwal berhasil dihapus');
}
}