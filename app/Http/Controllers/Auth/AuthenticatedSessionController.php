<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'login'    => 'required|string',
        'password' => 'required|string',
    ]);

    $loginInput = $request->login;

    // cek apakah email atau nisn
    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $field = 'email';
    } else {
        $field = 'nisn';
    }
    $user = \App\Models\User::where($field, $loginInput)->first();

    if ($user && $user->is_active == 0) {
        return back()->withErrors(['login' => 'Akun Anda sudah dinonaktifkan karena sudah lulus/alumni.']);
    }
    if (!Auth::attempt([
        $field => $loginInput,
        'password' => $request->password,
        'is_active' => 1
    ], $request->boolean('remember'))) {

        return back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors([
                'login' => 'Login gagal. Email/NISN atau password salah.'
            ]);
    }

    $request->session()->regenerate();

    return redirect()->intended('/dashboard');
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}