<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PembinaController extends Controller
{
    public function index()
    {
        $pembinas = User::role('pembina')->get();
        return view('admin.pembina.index', compact('pembinas'));
    }

    public function create()
    {
        return view('admin.pembina.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => 1,
        ]);

        $user->assignRole('pembina');

        return redirect()
            ->route('admin.pembina.index')
            ->with('success', 'Pembina berhasil dibuat');
    }

  

    public function update(Request $request, User $pembina)
    {
      $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $pembina->id,
        'password' => 'nullable|min:6',
        ]);

        $pembina->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $pembina->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return redirect()
            ->route('admin.pembina.index')
            ->with('success', 'Data pembina berhasil diperbarui');
    }

    public function toggle($id)
{
    $pembina = User::role('pembina')->findOrFail($id);

    if($pembina->id === auth()->id()){
        return back()->withErrors('Tidak bisa menonaktifkan akun sendiri.');
    }

    $pembina->update([
        'is_active' => !$pembina->is_active
    ]);

    return back()->with('success','Status diperbarui');
}
}
