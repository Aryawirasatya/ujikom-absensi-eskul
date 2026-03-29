<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Extracurricular;
use App\Models\ExtracurricularCoach;
use App\Models\User;

class ExtracurricularSeeder extends Seeder
{
    public function run(): void
    {
        $eskuls = [
            [
                'name'        => 'Paskibra',
                'description' => 'Pasukan Pengibar Bendera sekolah yang bertugas pada upacara resmi.',
                'is_active'   => 1,
                'pembina'     => 'budi@sekolah.sch.id',
            ],
            [
                'name'        => 'Basket',
                'description' => 'Ekstrakurikuler olahraga bola basket untuk mengembangkan kemampuan atletik siswa.',
                'is_active'   => 1,
                'pembina'     => 'siti@sekolah.sch.id',
            ],
            [
                'name'        => 'Paduan Suara',
                'description' => 'Kelompok paduan suara sekolah yang tampil pada acara resmi dan perlombaan.',
                'is_active'   => 1,
                'pembina'     => 'ahmad@sekolah.sch.id',
            ],
            [
                'name'        => 'Pramuka',
                'description' => 'Gerakan Pramuka untuk membentuk karakter dan kepemimpinan siswa.',
                'is_active'   => 1,
                'pembina'     => 'dewi@sekolah.sch.id',
            ],
            [
                'name'        => 'Futsal',
                'description' => 'Ekstrakurikuler futsal untuk mengembangkan bakat sepak bola di dalam ruangan.',
                'is_active'   => 1,
                'pembina'     => 'rizky@sekolah.sch.id',
            ],
            [
                'name'        => 'Karya Ilmiah Remaja',
                'description' => 'KIR untuk mengembangkan kemampuan riset dan penulisan ilmiah siswa.',
                'is_active'   => 1,
                'pembina'     => 'nur@sekolah.sch.id',
            ],
        ];

        foreach ($eskuls as $data) {
            $pembina = User::where('email', $data['pembina'])->first();

            $eskul = Extracurricular::create([
                'name'        => $data['name'],
                'description' => $data['description'],
                'is_active'   => $data['is_active'],
            ]);

            if ($pembina) {
                ExtracurricularCoach::create([
                    'extracurricular_id' => $eskul->id,
                    'user_id'            => $pembina->id,
                    'is_primary'         => 1,
                ]);
            }
        }

        $this->command->info('✅ Extracurriculars seeded: ' . count($eskuls) . ' eskul.');
    }
}