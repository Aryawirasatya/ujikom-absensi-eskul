<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class StudentQrController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user->hasRole('siswa')) {
            abort(403);
        }

        /*
            | AMBIL DATA AKADEMIK AKTIF
            | Gunakan relasi yang SUDAH ADA di model: currentAcademic()
        */
        $academic = $user->currentAcademic()->first();

        if (!$academic) {
            abort(404, 'Data akademik aktif tidak ditemukan');
        }

        /*
            | ENCRYPTED PAYLOAD
            */
        $payload = [
            'uid'   => $user->id,
            'nisn'  => $user->nisn,
            'grade' => $academic->grade,
            'class' => $academic->class_label,
        ];

        $encrypted = Crypt::encryptString(json_encode($payload));

        return view('siswa.qr.qr', [
            'user'     => $user,
            'academic' => $academic,
            'qrValue'  => $encrypted,
        ]);
    }
}
