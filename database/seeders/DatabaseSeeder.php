<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SchoolYearSeeder::class,
            UserSeeder::class,
            ExtracurricularSeeder::class,
            ExtracurricularScheduleSeeder::class,
            ExtracurricularMemberSeeder::class,
            ActivitySeeder::class,
            AttendanceSeeder::class,
           AssessmentSeeder::class

        ]);
    }
}