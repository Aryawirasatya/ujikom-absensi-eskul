<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentEskulController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $memberships = $user->extracurricularMembers()
            ->with('extracurricular')
            ->where('status', 'active')
            ->get();

        return view('siswa.eskul.index', compact('memberships'));
    }
}
