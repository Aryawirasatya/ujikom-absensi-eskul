<?php

namespace App\Services;

use App\Models\User;
use App\Models\SchoolYear;
use App\Models\StudentAcademic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentManagementService
{
   public function createManual(array $data): void
{
    $year = SchoolYear::current();

    if (!$year) {
        throw new \Exception('Tahun ajaran aktif tidak ditemukan');
    }

    DB::transaction(function () use ($data, $year) {

        $existing = User::where('nisn', $data['nisn'])->first();
        if ($existing) {
            throw new \Exception('NISN sudah digunakan');
        }

        $photoName = null;

        if (isset($data['photo'])) {
            $photoName = $data['nisn'] . '_' . time() . '.' . $data['photo']->getClientOriginalExtension();
            $data['photo']->storeAs('students', $photoName, 'public');
        }

        $user = User::create([
            'name'     => $data['name'],
            'nisn'     => $data['nisn'],
            'gender'   => $data['gender'],
            'password' => Hash::make($data['nisn']),
            'is_active'=> 1,
            'photo'    => $photoName,
        ]);

        $user->assignRole('siswa');

        StudentAcademic::create([
            'user_id'        => $user->id,
            'school_year_id' => $year->id,
            'grade'          => $data['grade'],
            'class_label'    => $data['class_label'],
            'academic_status'=> 'active',
        ]);
    });
}

}
