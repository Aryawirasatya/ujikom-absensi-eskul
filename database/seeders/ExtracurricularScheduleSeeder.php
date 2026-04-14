<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Extracurricular;
use App\Models\ExtracurricularSchedule;

class ExtracurricularScheduleSeeder extends Seeder
{
    
    public function run(): void
    {
        $scheduleMap = [
            'Paskibra'             => [3, 6],     
            'Basket'               => [2, 5],    
            'Paduan Suara'         => [4],       
            'Pramuka'              => [6],       
            'Futsal'               => [1, 3],   
            'Karya Ilmiah Remaja'  => [2, 5],   
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