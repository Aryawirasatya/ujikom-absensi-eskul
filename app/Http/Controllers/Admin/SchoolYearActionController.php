<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AcademicPromotionService;
use Illuminate\Support\Facades\Hash;
class SchoolYearActionController extends Controller
{
    /**
     * Halaman status tahun ajaran + tombol ganti tahun
     */
    public function index()
{
    $currentYear = SchoolYear::current();

    $history = SchoolYear::orderByDesc('start_date')->paginate(10);

    $nextYearLabel = null;

    if ($currentYear) {
        [$from, $to] = explode('/', $currentYear->name);
        $nextYearLabel = ($from + 1) . '/' . ($to + 1);
    }

    return view('admin.school_years.index', compact(
        'currentYear',
        'nextYearLabel',
        'history'
    ));
}


    /**
     */
   public function switch(Request $request)
{
    $request->validate([
        'password' => 'required|string',
        'start_year' => 'nullable|digits:4',
    ]);

    if (!Hash::check($request->password, auth()->user()->password)) {
        return back()->withErrors(['password' => 'Password salah']);
    }

    DB::transaction(function () use ($request) {

        $current = SchoolYear::where('is_active', true)
            ->lockForUpdate()
            ->first();

        /**
         * =========================
         * TAHUN PERTAMA (INIT)
         * =========================
         */
        if (!$current) {

            if (!$request->start_year) {
                throw new \Exception('Tahun awal wajib diisi');
            }

            $startYear = (int) $request->start_year;

            SchoolYear::create([
                'name' => $startYear . '/' . ($startYear + 1),
                'start_date' => now(),
                'end_date' => null,
                'is_active' => true,
            ]);

            return;
        }

        /**
         * =========================
         * PERGANTIAN TAHUN
         * =========================
         */
        [$from, $to] = explode('/', $current->name);

        $newYear = SchoolYear::create([
            'name' => ((int)$from + 1) . '/' . ((int)$to + 1),
            'start_date' => now(),
            'end_date' => null,
            'is_active' => true,
        ]);

        app(\App\Services\AcademicPromotionService::class)
            ->promote($current, $newYear);

        $current->update([
            'is_active' => false,
            'end_date' => now(),
        ]);
    });

    return back()->with('success', 'Tahun ajaran & kenaikan kelas berhasil diproses');
}


}
