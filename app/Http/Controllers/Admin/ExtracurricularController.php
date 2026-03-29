<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtracurricularController extends Controller
{

    public function index()
    {
        $eskuls = Extracurricular::with([
            'primaryCoach.user'
        ])->get();

        return view('admin.eskul.index', compact('eskuls'));
    }

    public function create()
    {
        $pembinas = User::role('pembina')
            ->where('is_active', 1)
            ->get();

        return view('admin.eskul.create', compact('pembinas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:extracurriculars,name',
            'primary_coach' => 'required|exists:users,id'
        ]);

        DB::transaction(function () use ($request) {

            $eskul = Extracurricular::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => 1,
            ]);

            $eskul->coaches()->create([
                'user_id' => $request->primary_coach,
                'is_primary' => 1,
            ]);
        });

        return redirect()
            ->route('admin.eskul.index')
            ->with('success', 'Eskul berhasil dibuat');
    }

    public function toggle(Extracurricular $eskul)
    {
        $eskul->update([
            'is_active' => !$eskul->is_active
        ]);

        return back()->with(
            'success',
            'Status eskul berhasil diperbarui'
        );
    }

    public function edit(Extracurricular $eskul)
    {
        $pembinas = User::role('pembina')
            ->where('is_active', 1)
            ->get();

        $primary = $eskul->coaches()
            ->where('is_primary', 1)
            ->first();

        return view('admin.eskul.edit', compact(
            'eskul',
            'pembinas',
            'primary'
        ));
    }

    public function update(Request $request, Extracurricular $eskul)
    {
        $request->validate([
            'name' => 'required|unique:extracurriculars,name,' . $eskul->id,
            'primary_coach' => 'required|exists:users,id'
        ]);

        DB::transaction(function () use ($request, $eskul) {

            $eskul->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $eskul->coaches()->delete();

            $eskul->coaches()->create([
                'user_id' => $request->primary_coach,
                'is_primary' => 1,
            ]);
        });

        return redirect()
            ->route('admin.eskul.index')
            ->with('success', 'Eskul berhasil diperbarui');
    }
}