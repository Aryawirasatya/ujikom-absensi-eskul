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

        return view('pembina.schedules.index',
            compact('eskul','schedules'));
    }

    public function store(Request $request, Extracurricular $eskul)
{
    $this->authorizeCoach($eskul);

    $request->validate([
        'day_of_week' => 'required|integer|min:1|max:7'
    ]);

    if (
        $eskul->schedules()
            ->where('day_of_week', $request->day_of_week)
            ->exists()
    ) {
        return back()->withErrors('Hari tersebut sudah ada');
    }

    ExtracurricularSchedule::create([
        'extracurricular_id' => $eskul->id,
        'day_of_week' => $request->day_of_week,
        'is_active' => 1
    ]);

    return back()->with('success','Jadwal ditambahkan');
}


    public function toggle($eskul, ExtracurricularSchedule $schedule)
    {
        $this->authorizeCoach($schedule->extracurricular);

        $schedule->update([
            'is_active'=>!$schedule->is_active
        ]);

        return back();
    }

    public function destroy(ExtracurricularSchedule $schedule)
{
    abort_unless(
        $schedule->extracurricular
            ->coaches()
            ->where('user_id', auth()->id())
            ->exists(),
        403
    );

    $schedule->delete();

    return back()->with('success', 'Jadwal berhasil dihapus');
}

}
