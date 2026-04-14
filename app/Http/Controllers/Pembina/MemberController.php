<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
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

        $schoolYear = SchoolYear::current();
        abort_unless($schoolYear, 400);

        $members = ExtracurricularMember::with('user.currentAcademic')
            ->where('extracurricular_id', $eskul->id)
            ->where('school_year_id', $schoolYear->id)
            ->orderByDesc('created_at')
            ->get();

        return view('pembina.members.index', compact(
            'eskul',
            'members'
        ));
    }

    public function searchStudents(Request $request, Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);

        $keyword = $request->q;

        $students = User::role('siswa')
            ->with('currentAcademic')
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                  ->orWhere('nisn', 'like', "%$keyword%");
            })
            ->limit(20)
            ->get();

        return response()->json(
            $students->map(function ($s) {
                $kelas = optional($s->currentAcademic)->grade . ' ' .
                         optional($s->currentAcademic)->class_label;

                return [
                    'id' => $s->id,
                    'text' => $s->name,
                    'nisn' => $s->nisn,
                    'kelas' => trim($kelas)
                ];
            })
        );
    }

    public function store(Request $request, Extracurricular $eskul)
    {
        $this->authorizeCoach($eskul);

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $schoolYear = SchoolYear::current();
        abort_unless($schoolYear, 400);

        $member = ExtracurricularMember::where([
            'extracurricular_id' => $eskul->id,
            'school_year_id' => $schoolYear->id,
            'user_id' => $request->user_id
        ])->first();

        if ($member) {

            if ($member->status === 'inactive') {
                $member->update([
                    'status' => 'active',
                    'left_at' => null
                ]);

                return back()->with('success', 'Anggota diaktifkan kembali');
            }

            return back()->withErrors('Siswa sudah menjadi anggota aktif');
        }

        ExtracurricularMember::create([
            'extracurricular_id' => $eskul->id,
            'school_year_id' => $schoolYear->id,
            'user_id' => $request->user_id,
            'joined_at' => now(),
            'status' => 'active'
        ]);

        return back()->with('success', 'Anggota berhasil ditambahkan');
    }

public function deactivate(Extracurricular $eskul, ExtracurricularMember $member)
{
    $this->authorizeCoach($eskul);

    $member->update([
        'status' => 'inactive',
        'left_at' => now()
    ]);

    return back()->with('success', 'Anggota dinonaktifkan');
}

public function activate(Extracurricular $eskul, ExtracurricularMember $member)
{
    $this->authorizeCoach($eskul);

    $member->update([
        'status' => 'active',
        'left_at' => null
    ]);

    return back()->with('success', 'Anggota diaktifkan kembali');
}
}
