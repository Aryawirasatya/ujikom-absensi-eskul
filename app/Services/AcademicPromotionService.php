<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\StudentAcademic;
use App\Models\ExtracurricularMember;
use Illuminate\Support\Facades\DB;

class AcademicPromotionService
{
    public function promote(SchoolYear $oldYear, SchoolYear $newYear): void
    {
        // Gunakan chunk agar server tidak berat jika siswa ada ribuan
        StudentAcademic::where('school_year_id', $oldYear->id)
            ->where('academic_status', 'active')
            ->with('user') // Eager load user untuk efisiensi
            ->chunk(100, function ($academics) use ($newYear) {
                foreach ($academics as $academic) {

                    // ==========================================
                    // KELAS 9 → LULUS & DIBLOKIR
                    // ==========================================
                    if ($academic->grade >= 9) {
                        // 1. Update status akademik menjadi graduated
                        $academic->update([
                            'academic_status' => 'graduated',
                        ]);

                        // 2. BLOKIR LOGIN: Set is_active pada tabel users menjadi 0
                        $academic->user->update([
                            'is_active' => 0
                        ]);

                        // 3. NONAKTIFKAN ESKUL: Keluarkan dari semua eskul yang diikuti
                        ExtracurricularMember::where('user_id', $academic->user_id)
                            ->where('status', 'active')
                            ->update([
                                'status' => 'inactive',
                                'left_at' => now()
                            ]);

                        continue;
                    }

                    // ==========================================
                    // SISWA NAIK KELAS (Grade 7 & 8)
                    // ==========================================
                    StudentAcademic::create([
                        'user_id' => $academic->user_id,
                        'school_year_id' => $newYear->id,
                        'grade' => $academic->grade + 1,
                        'class_label' => $academic->class_label,
                        'academic_status' => 'active',
                    ]);

                    $academic->update([
                        'academic_status' => 'promoted',
                    ]);
                }
            });
    }
}