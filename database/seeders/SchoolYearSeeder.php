<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolYear;
use Carbon\Carbon;

class SchoolYearSeeder extends Seeder
{
    public function run(): void
    {
        // Tahun ajaran lama (sudah selesai)
        SchoolYear::create([
            'name'       => '2024/2025',
            'start_date' => Carbon::create(2024, 7, 17, 0, 0, 0),
            'end_date'   => Carbon::create(2025, 6, 30, 23, 59, 59),
            'is_active'  => false,
        ]);

        // Tahun ajaran aktif sekarang
        SchoolYear::create([
            'name'       => '2025/2026',
            'start_date' => Carbon::create(2025, 7, 15, 0, 0, 0),
            'end_date'   => null,
            'is_active'  => true,
        ]);

        $this->command->info('✅ School years seeded.');
    }
}