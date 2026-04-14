<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\StudentAcademic;
use Illuminate\Support\Facades\DB;

class AcademicPromotionService
{
    public function promote(SchoolYear $oldYear, SchoolYear $newYear): void
    {
        $oldAcademics = StudentAcademic::where('school_year_id', $oldYear->id)
            ->where('academic_status', 'active')
            ->get();

        foreach ($oldAcademics as $academic) {

            // KELAS 9 → LULUS
            if ($academic->grade >= 9) {
                $academic->update([
                    'academic_status' => 'graduated',
                ]);
                continue;
            }

            // NAIK KELAS
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
    }
}

