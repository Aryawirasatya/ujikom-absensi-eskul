<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForceStudentChangePassword
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Hanya berlaku untuk role siswa
        if (!$user->hasRole('siswa')) {
            if ($request->is('change-password*')) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        }

        /**
         * Optimasi: Gunakan session agar tidak melakukan Hash::check (bcrypt) 
         * di setiap request halaman karena operasi tersebut sangat berat bagi CPU.
         */
        if (!$request->session()->has('password_is_default')) {
            $isDefault = $user->nisn && Hash::check($user->nisn, $user->password);
            $request->session()->put('password_is_default', $isDefault);
        }

        $passwordMasihDefault = $request->session()->get('password_is_default');

        // Jika password masih default (sama dengan NISN)
        if ($passwordMasihDefault) {
            // Jika user mencoba akses selain halaman ganti password, paksa redirect
            if (!$request->is('change-password*')) {
                return redirect()->route('password.change.form')
                    ->with('warning', 'Demi keamanan, harap ganti password default Anda.');
            }
        } else {
            // Jika sudah ganti password, dilarang masuk ke halaman ganti password lagi
            if ($request->is('change-password*')) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}