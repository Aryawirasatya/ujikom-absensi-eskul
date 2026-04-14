<?php

namespace App\Imports;

use App\Models\User;
use App\Models\StudentAcademic;
use App\Models\SchoolYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $year = SchoolYear::current();

        if (!$year) {
            throw new \Exception('Tidak ada tahun ajaran aktif');
        }

        DB::transaction(function () use ($rows, $year) {

            foreach ($rows->skip(1) as $index => $row) {

                /*
                            | NORMALISASI DATA EXCEL (ANTI ANEH)
                            */

                // NAMA
                $name = trim((string) ($row[0] ?? ''));

                // ===== GENDER (INI INTI MASALAH) =====
                $rawGender = (string) ($row[1] ?? '');

                // 1. convert ke uppercase (multibyte safe)
                $rawGender = mb_strtoupper($rawGender, 'UTF-8');

                // 2. hapus SEMUA karakter selain huruf
                $normalizedGender = preg_replace('/[^A-Z]/u', '', $rawGender);

                // 3. mapping nilai manusiawi
                $genderMap = [
                    'L'          => 'L',
                    'LAKI'       => 'L',
                    'LAKILAKI'   => 'L',
                    'LAKILAKILAKI' => 'L',

                    'P'          => 'P',
                    'PEREMPUAN'  => 'P',
                ];

                $gender = $genderMap[$normalizedGender] ?? null;

                // NISN
                $nisn = preg_replace('/\D/', '', (string) ($row[2] ?? ''));

                // GRADE
                $grade = (int) trim((string) ($row[3] ?? 0));

                // KELAS
                $class = trim((string) ($row[4] ?? ''));

                /*
                            | VALIDASI FAIL-FAST (ERROR JELAS)
                            */
                $line = $index + 2;

                if ($name === '') {
                    throw new \Exception("Nama kosong di baris ke-{$line}");
                }

                if (!$gender || !in_array($gender, ['L', 'P'])) {
                    throw new \Exception(
                        "Gender tidak valid di baris ke-{$line} (isi: '{$row[1]}')"
                    );
                }

                if ($nisn === '') {
                    throw new \Exception("NISN kosong / tidak valid di baris ke-{$line}");
                }

                if (!in_array($grade, [7, 8, 9], true)) {
                    throw new \Exception("Grade tidak valid di baris ke-{$line}");
                }

                /*
                            | USER (KHUSUS SISWA)
                            */
                $user = User::where('nisn', $nisn)
                    ->lockForUpdate()
                    ->first();

                if ($user) {
                    if (!$user->hasRole('siswa')) {
                        throw new \Exception(
                            "NISN {$nisn} sudah dipakai user non-siswa (baris ke-{$line})"
                        );
                    }
                } else {
                    $user = User::create([
                        'name'      => $name,
                        'gender'    => $gender,
                        'nisn'      => $nisn,
                        'password'  => Hash::make($nisn),
                        'is_active' => 1,
                    ]);

                    $user->assignRole('siswa');
                }

                /*
                            | CEK DUPLIKAT AKADEMIK
                            */
                $exists = StudentAcademic::where('user_id', $user->id)
                    ->where('school_year_id', $year->id)
                    ->exists();

                if ($exists) {
                    throw new \Exception(
                        "Siswa dengan NISN {$nisn} sudah terdaftar di tahun ajaran ini"
                    );
                }

                /*
                            | SIMPAN AKADEMIK
                            */
                StudentAcademic::create([
                    'user_id'         => $user->id,
                    'school_year_id'  => $year->id,
                    'grade'           => $grade,
                    'class_label'     => $class ?: null,
                    'academic_status' => 'active',
                ]);
            }
        });
    }
}
