<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\ExtracurricularSchedule;
use App\Models\ScheduleException;
use Illuminate\Http\Request;

class ScheduleExceptionController extends Controller
{
    public function store(Request $request, ExtracurricularSchedule $schedule)
    {
        abort_unless(
            $schedule->extracurricular
                ->coaches()
                ->where('user_id', auth()->id())
                ->exists(),
            403
        );

        $request->validate([
            'exception_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $exists = ScheduleException::where('schedule_id', $schedule->id)
            ->where('exception_date', $request->exception_date)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'exception_date' => 'Tanggal ini sudah diajukan'
            ]);
        }

        ScheduleException::create([
            'schedule_id'    => $schedule->id,
            'exception_date' => $request->exception_date,
            'status'         => 'cancelled',
            'reason'         => $request->reason,
            'reported_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Pengajuan libur berhasil dikirim');
    }
}
