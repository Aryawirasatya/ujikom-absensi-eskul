<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Extracurricular;
use App\Models\ExtracurricularSchedule;

class ExtracurricularScheduleSeeder extends Seeder
{
    /**
     * day_of_week: ISO format
     * 1 = Senin, 2 = Selasa, 3 = Rabu, 4 = Kamis, 5 = Jumat, 6 = Sabtu, 7 = Minggu
     */
    public function run(): void
    {
        $scheduleMap = [
            'Paskibra'             => [3, 6],   // Rabu & Sabtu
            'Basket'               => [2, 5],   // Selasa & Jumat
            'Paduan Suara'         => [4],       // Kamis
            'Pramuka'              => [6],       // Sabtu
            'Futsal'               => [1, 3],   // Senin & Rabu
            'Karya Ilmiah Remaja'  => [2, 5],   // Selasa & Jumat
        ];

        foreach ($scheduleMap as $eskulName => $days) {
            $eskul = Extracurricular::where('name', $eskulName)->first();
            if (!$eskul) continue;

            foreach ($days as $day) {
                ExtracurricularSchedule::create([
                    'extracurricular_id' => $eskul->id,
                    'day_of_week'        => $day,
                    'is_active'          => 1,
                    'notes'              => null,
                ]);
            }
        }

        $this->command->info('✅ Extracurricular schedules seeded.');
    }
}