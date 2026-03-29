<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentQuestion; // <-- TAMBAHAN IMPORT
use App\Models\Extracurricular;
use App\Models\SchoolYear;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Pastikan ada school year aktif ──
        $schoolYear = SchoolYear::where('is_active', true)->first();
        if (! $schoolYear) {
            $this->command->warn('Tidak ada school year aktif. Seeder dihentikan.');
            return;
        }

        // ── 2. Buat kategori penilaian & Pertanyaan dasar jika belum ada ──
        $categoryData = [
            ['name' => 'Kedisiplinan',   'description' => 'Ketepatan waktu, kehadiran, dan kepatuhan terhadap peraturan.'],
            ['name' => 'Kerja Sama',     'description' => 'Kemampuan bekerja dalam tim dan mendukung sesama anggota.'],
            ['name' => 'Tanggung Jawab', 'description' => 'Menyelesaikan tugas yang diberikan dengan tuntas.'],
            ['name' => 'Sportivitas',    'description' => 'Sikap jujur, menghargai lawan, dan menerima hasil dengan lapang dada.'],
            ['name' => 'Inisiatif',      'description' => 'Keberanian mengambil tindakan tanpa harus diperintah.'],
        ];

        $categories = collect();
        foreach ($categoryData as $cat) {
            $category = AssessmentCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['description' => $cat['description'], 'is_active' => true, 'show_to_student' => true]
            );

            // TAMBAHAN: Buat 1 pertanyaan default untuk kategori ini biar seeder punya question_id
            AssessmentQuestion::firstOrCreate(
                ['category_id' => $category->id, 'question' => 'Indikator capaian ' . strtolower($category->name)],
                ['is_active' => true]
            );

            $categories->push($category);
        }

        $this->command->info("✓ {$categories->count()} kategori dan indikator soal siap.");

        // ── 3. Ambil eskul aktif ──
        $eskuls = Extracurricular::where('is_active', true)->get();

        if ($eskuls->isEmpty()) {
            $this->command->warn('Tidak ada ekstrakurikuler aktif. Seeder dihentikan.');
            return;
        }

        $this->command->info("✓ {$eskuls->count()} eskul aktif ditemukan.");

        // ── 4. Loop per eskul ──
        foreach ($eskuls as $eskul) {

            // Ambil pembina pertama eskul ini
            $coach = User::whereHas('extracurricularCoaches', function ($q) use ($eskul) {
                $q->where('extracurricular_id', $eskul->id);
            })->first();

            // Fallback ke user role pembina pertama yang ada
            if (! $coach) {
                $coach = User::role('pembina')->first();
            }

            // Fallback ke admin jika tidak ada pembina sama sekali
            if (! $coach) {
                $coach = User::role('admin')->first();
            }

            if (! $coach) {
                $this->command->warn("  Eskul [{$eskul->name}]: tidak ada coach/pembina, skip.");
                continue;
            }

            // Ambil anggota aktif eskul ini
            $members = User::whereHas('extracurricularMembers', function ($q) use ($eskul, $schoolYear) {
                $q->where('extracurricular_id', $eskul->id)
                  ->where('status', 'active')
                  ->where('school_year_id', $schoolYear->id);
            })->get();

            if ($members->isEmpty()) {
                $this->command->warn("  Eskul [{$eskul->name}]: tidak ada anggota aktif, skip.");
                continue;
            }

            $this->command->line("  Eskul [{$eskul->name}]: {$members->count()} anggota, coach: {$coach->name}");

            // ── 5. Buat 3 periode: 3 bulan lalu, 2 bulan lalu, bulan ini ──
            $periodMonths = [
                now()->subMonths(2)->format('Y-m'),
                now()->subMonths(1)->format('Y-m'),
                now()->format('Y-m'),
            ];

            foreach ($periodMonths as $idx => $periodLabel) {
                $isLast   = $idx === array_key_last($periodMonths);
                $status   = $isLast ? 'open' : 'closed';
                $closedAt = $isLast ? null : Carbon::createFromFormat('Y-m', $periodLabel)->endOfMonth();

                // Buat atau ambil periode
                $period = AssessmentPeriod::firstOrCreate(
                    ['extracurricular_id' => $eskul->id, 'period_label' => $periodLabel],
                    [
                        'period_type' => 'monthly',
                        'status'      => $status,
                        'closed_at'   => $closedAt,
                        'closed_by'   => $isLast ? null : $coach->id,
                    ]
                );

                // ── 6. Buat assessment untuk setiap anggota ──
                foreach ($members as $member) {

                    // Jangan duplikat jika sudah ada
                    $existing = Assessment::where([
                        'evaluator_id'       => $coach->id,
                        'evaluatee_id'       => $member->id,
                        'extracurricular_id' => $eskul->id,
                        'period_label'       => $periodLabel,
                    ])->first();

                    if ($existing) {
                        continue;
                    }

                    DB::transaction(function () use (
                        $coach, $member, $eskul, $periodLabel, $categories
                    ) {
                        $assessmentDate = Carbon::createFromFormat('Y-m', $periodLabel)
                            ->day(rand(10, 25))
                            ->toDateString();

                        $assessment = Assessment::create([
                            'evaluator_id'       => $coach->id,
                            'evaluatee_id'       => $member->id,
                            'extracurricular_id' => $eskul->id,
                            'assessment_date'    => $assessmentDate,
                            'period_type'        => 'monthly',
                            'period_label'       => $periodLabel,
                            'general_notes'      => $this->randomNote(),
                        ]);

                        // Detail per kategori — skor 2–5 (distribusi realistis)
                        foreach ($categories as $cat) {
                            
                            // AMBIL ID PERTANYAAN TERKAIT KATEGORI INI
                            $question = AssessmentQuestion::where('category_id', $cat->id)->first();

                            if ($question) {
                                AssessmentDetail::create([
                                    'assessment_id' => $assessment->id,
                                    'category_id'   => $cat->id,
                                    'question_id'   => $question->id, // <-- SEKARANG SUDAH ADA ISINYA
                                    'score'         => $this->realisticScore(),
                                ]);
                            }
                        }
                    });
                }

                $countAssessed = Assessment::where('extracurricular_id', $eskul->id)
                    ->where('period_label', $periodLabel)
                    ->count();

                $this->command->line("    Periode {$periodLabel} [{$status}]: {$countAssessed} penilaian");
            }
        }

        $this->command->info('');
        $this->command->info('✅ AssessmentSeeder selesai.');
        $this->command->info('   Total assessments : ' . Assessment::count());
        $this->command->info('   Total details     : ' . AssessmentDetail::count());
        $this->command->info('   Total periods     : ' . AssessmentPeriod::count());
    }

    // ── Helpers ──

    /**
     * Skor realistis: mayoritas 3-4, sesekali 2 atau 5
     */
    private function realisticScore(): int
    {
        $weights = [2 => 10, 3 => 30, 4 => 40, 5 => 20]; // persen
        $rand    = rand(1, 100);
        $cumulative = 0;
        foreach ($weights as $score => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $score;
            }
        }
        return 4;
    }

    /**
     * Catatan pembina acak — kadang null
     */
    private function randomNote(): ?string
    {
        $notes = [
            null,
            null,
            null,
            'Siswa menunjukkan perkembangan yang baik bulan ini.',
            'Perlu lebih aktif berpartisipasi dalam latihan.',
            'Kedisiplinan meningkat dibanding periode sebelumnya.',
            'Semangat tinggi, pertahankan!',
            'Masih perlu bimbingan lebih lanjut dalam kerja tim.',
            'Prestasi membaik, terus tingkatkan.',
            'Hadir tepat waktu dan antusias mengikuti kegiatan.',
        ];

        return $notes[array_rand($notes)];
    }
}