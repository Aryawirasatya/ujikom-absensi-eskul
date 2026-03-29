<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\SchoolYear;
use App\Models\User;

class ExtracurricularMemberSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = SchoolYear::where('is_active', true)->first();
        $oldYear     = SchoolYear::where('is_active', false)->first();

        $siswaUsers = User::role('siswa')->where('is_active', 1)->get();
        $eskuls     = Extracurricular::where('is_active', 1)->get();

        /**
         * Distribusi anggota per eskul:
         * Setiap siswa bisa ikut 1-2 eskul.
         * Masing-masing eskul punya sekitar 15-20 anggota.
         */
        $memberMap = [
            'Paskibra'            => [],
            'Basket'              => [],
            'Paduan Suara'        => [],
            'Pramuka'             => [],
            'Futsal'              => [],
            'Karya Ilmiah Remaja' => [],
        ];

        // Bagi siswa merata ke eskul, tiap siswa max 2 eskul
        $siswaIds = $siswaUsers->pluck('id')->toArray();
        shuffle($siswaIds);

        $eskulNames = array_keys($memberMap);
        $perEskul   = 15; // min 15 anggota per eskul

        foreach ($eskulNames as $idx => $eskulName) {
            // Ambil slice sesuai index, wrap around jika perlu
            for ($i = 0; $i < $perEskul; $i++) {
                $siswaIdx = ($idx * 10 + $i) % count($siswaIds);
                $memberMap[$eskulName][] = $siswaIds[$siswaIdx];
            }
            // Hapus duplikat
            $memberMap[$eskulName] = array_unique($memberMap[$eskulName]);
        }

        // Tambahan: beberapa siswa ikut eskul ke-2
        $doubleJoin = array_slice($siswaIds, 0, 20);
        foreach ($doubleJoin as $i => $siswaId) {
            $eskulName = $eskulNames[($i + 2) % count($eskulNames)];
            if (!in_array($siswaId, $memberMap[$eskulName])) {
                $memberMap[$eskulName][] = $siswaId;
            }
        }

        // Simpan ke database
        $totalInserted = 0;
        foreach ($memberMap as $eskulName => $userIds) {
            $eskul = $eskuls->where('name', $eskulName)->first();
            if (!$eskul) continue;

            foreach ($userIds as $userId) {
                // Cek duplikat sebelum insert
                $exists = ExtracurricularMember::where([
                    'extracurricular_id' => $eskul->id,
                    'school_year_id'     => $currentYear->id,
                    'user_id'            => $userId,
                ])->exists();

                if (!$exists) {
                    ExtracurricularMember::create([
                        'extracurricular_id' => $eskul->id,
                        'school_year_id'     => $currentYear->id,
                        'user_id'            => $userId,
                        'joined_at'          => $currentYear->start_date->addDays(rand(1, 14))->toDateString(),
                        'left_at'            => null,
                        'status'             => 'active',
                    ]);
                    $totalInserted++;
                }

                // Juga daftarkan di tahun lama untuk siswa kelas 11 & 12
                $academic = \App\Models\StudentAcademic::where('user_id', $userId)
                    ->where('school_year_id', $oldYear->id)
                    ->first();

                if ($academic) {
                    $existsOld = ExtracurricularMember::where([
                        'extracurricular_id' => $eskul->id,
                        'school_year_id'     => $oldYear->id,
                        'user_id'            => $userId,
                    ])->exists();

                    if (!$existsOld) {
                        ExtracurricularMember::create([
                            'extracurricular_id' => $eskul->id,
                            'school_year_id'     => $oldYear->id,
                            'user_id'            => $userId,
                            'joined_at'          => $oldYear->start_date->addDays(rand(1, 14))->toDateString(),
                            'left_at'            => $oldYear->end_date->toDateString(),
                            'status'             => 'inactive',
                        ]);
                    }
                }
            }
        }

        $this->command->info("✅ Extracurricular members seeded: {$totalInserted} anggota aktif.");
    }
}