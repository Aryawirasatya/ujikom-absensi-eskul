<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use Illuminate\Support\Facades\Auth;

class EskulController extends Controller
{
    public function index()
    {
        $eskuls = Extracurricular::whereHas('coaches', function ($q) {
            $q->where('user_id', Auth::id());
        })->get();

        return view('pembina.eskul.index', compact('eskuls'));
    }
}
