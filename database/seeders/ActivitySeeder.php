<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivityQrSession;
use App\Models\Extracurricular;
use App\Models\ExtracurricularSchedule;
use App\Models\ScheduleException;
use App\Models\SchoolYear;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = SchoolYear::where('is_active', true)->first();
        $oldYear     = SchoolYear::where('is_active', false)->first();

        $this->seedForYear($oldYear, $oldYear->start_date, $oldYear->end_date);
        $this->seedForYear($currentYear, $currentYear->start_date, Carbon::now()->subDays(1));

        $this->command->info('✅ Activities seeded for both school years.');
    }

    private function seedForYear(SchoolYear $year, Carbon $from, Carbon $to): void
    {
        $eskuls = Extracurricular::with(['schedules', 'coaches.user'])->get();

        // Daftar libur nasional / libur sekolah yang umum (approximate)
        $holidays = $this->getHolidays($from->year, $to->year);

        foreach ($eskuls as $eskul) {
            $pembina = $eskul->coaches->first()?->user;
            if (!$pembina) continue;

            foreach ($eskul->schedules as $schedule) {
                // Dapatkan hari ISO dari schedule
                $dayOfWeek = $schedule->day_of_week; // 1=Senin...7=Minggu

                // Loop semua tanggal dari awal ke akhir tahun ajaran
                $period = CarbonPeriod::create($from->copy(), '1 day', $to->copy());

                $exceptionDates = [];

                foreach ($period as $date) {
                    // Cek apakah hari ini sesuai jadwal
                    if ($date->dayOfWeekIso !== $dayOfWeek) continue;

                    $dateStr = $date->toDateString();

                    // Cek hari libur nasional
                    if (in_array($dateStr, $holidays)) {
                        // Buat schedule exception
                        $alreadyException = ScheduleException::where('schedule_id', $schedule->id)
                            ->where('exception_date', $dateStr)
                            ->exists();

                        if (!$alreadyException) {
                            ScheduleException::create([
                                'schedule_id'      => $schedule->id,
                                'exception_date'   => $dateStr,
                                'status'           => 'cancelled',
                                'reason'           => 'Libur Nasional',
                                'reported_by'      => $pembina->id,
                                'approved_by_admin'=> User::role('admin')->first()->id,
                            ]);
                            $exceptionDates[] = $dateStr;
                        }
                        continue;
                    }

                    // Sekitar 5% aktivitas di-skip secara random (pembina berhalangan)
                    if (rand(1, 100) <= 5) {
                        $alreadyException = ScheduleException::where('schedule_id', $schedule->id)
                            ->where('exception_date', $dateStr)
                            ->exists();

                        if (!$alreadyException) {
                            ScheduleException::create([
                                'schedule_id'      => $schedule->id,
                                'exception_date'   => $dateStr,
                                'status'           => 'cancelled',
                                'reason'           => collect(['Pembina berhalangan hadir', 'Kegiatan sekolah bentrok', 'Hujan deras - kegiatan luar ruang'])->random(),
                                'reported_by'      => $pembina->id,
                                'approved_by_admin'=> rand(0, 1) ? User::role('admin')->first()->id : null,
                            ]);
                            $exceptionDates[] = $dateStr;
                        }
                        continue;
                    }

                    // Cek jangan duplikat
                    $exists = Activity::where('extracurricular_id', $eskul->id)
                        ->where('schedule_id', $schedule->id)
                        ->whereDate('activity_date', $dateStr)
                        ->exists();

                    if ($exists) continue;

                    // Buat aktivitas RUTIN
                    $startHour  = rand(14, 15);
                    $startedAt  = $date->copy()->setTime($startHour, 0, 0);
                    $endedAt    = $startedAt->copy()->addMinutes(rand(90, 120));

                    // 10% aktivitas adalah non-routine (kegiatan khusus)
                    $isSpecial = (rand(1, 100) <= 10);
                    $type = $isSpecial ? 'non_routine' : 'routine';
                    $title = $isSpecial
                        ? collect(['Latihan Persiapan Lomba', 'Evaluasi Tengah Tahun', 'Penampilan Khusus', 'Uji Coba Tim'])->random() . ' ' . $eskul->name
                        : 'Absensi Rutin ' . $eskul->name;

                    $activity = Activity::create([
                        'school_year_id'     => $year->id,
                        'extracurricular_id' => $eskul->id,
                        'schedule_id'        => $isSpecial ? null : $schedule->id,
                        'session_owner_id'   => $pembina->id,
                        'type'               => $type,
                        'title'              => $title,
                        'description'        => $isSpecial ? 'Kegiatan khusus yang diadakan secara insidental.' : null,
                        'activity_date'      => $dateStr,
                        'status'             => 'active',
                        'attendance_phase'   => 'finished',
                        'attendance_mode'    => rand(0, 4) < 4 ? 'qr' : 'manual', // 80% QR, 20% manual
                        'started_at'         => $startedAt,
                        'ended_at'           => $endedAt,
                        'created_by'         => $pembina->id,
                    ]);

                    // Buat QR Session jika mode = qr
                    if ($activity->attendance_mode === 'qr') {
                        $this->createQrSessions($activity, $startedAt, $endedAt, $pembina->id);
                    }
                }
            }

            // Beberapa aktivitas non-routine tambahan (lomba, evaluasi)
            $this->createSpecialActivities($eskul, $year, $pembina, $from, $to);
        }
    }

    private function createQrSessions(Activity $activity, Carbon $startedAt, Carbon $endedAt, int $createdBy): void
    {
        // QR Checkin
        $checkinDuration = rand(15, 25);
        $lateToleranceMins = rand(5, 10);
        ActivityQrSession::create([
            'activity_id'            => $activity->id,
            'mode'                   => 'checkin',
            'opened_at'              => $startedAt,
            'duration_minutes'       => $checkinDuration,
            'expires_at'             => $startedAt->copy()->addMinutes($checkinDuration + $lateToleranceMins),
            'late_tolerance_minutes' => $lateToleranceMins,
            'is_active'              => 0,
            'secret_hash'            => Str::random(32),
            'created_by'             => $createdBy,
        ]);

        // QR Checkout
        $checkoutStart = $endedAt->copy()->subMinutes(rand(5, 15));
        $checkoutDuration = rand(10, 20);
        ActivityQrSession::create([
            'activity_id'            => $activity->id,
            'mode'                   => 'checkout',
            'opened_at'              => $checkoutStart,
            'duration_minutes'       => $checkoutDuration,
            'expires_at'             => $checkoutStart->copy()->addMinutes($checkoutDuration),
            'late_tolerance_minutes' => 0,
            'is_active'              => 0,
            'secret_hash'            => Str::random(32),
            'created_by'             => $createdBy,
        ]);
    }

    private function createSpecialActivities(Extracurricular $eskul, SchoolYear $year, User $pembina, Carbon $from, Carbon $to): void
    {
        // Buat 2-4 kegiatan non-routine khusus per eskul per tahun
        $count = rand(2, 4);
        $existingDates = [];

        for ($i = 0; $i < $count; $i++) {
            // Pilih tanggal acak weekday
            $attempts = 0;
            do {
                $randomDays = rand(0, (int) $from->diffInDays($to));
                $date = $from->copy()->addDays($randomDays);
                $attempts++;
            } while (($date->isWeekend() || in_array($date->toDateString(), $existingDates)) && $attempts < 20);

            if ($attempts >= 20) continue;
            $existingDates[] = $date->toDateString();

            $startedAt = $date->copy()->setTime(8, 0, 0);
            $endedAt   = $startedAt->copy()->addHours(4);

            $specialTitles = [
                'Perlombaan Antar Kelas ' . $eskul->name,
                'Showcase & Penampilan ' . $eskul->name,
                'Workshop & Pelatihan ' . $eskul->name,
                'Studi Banding ' . $eskul->name,
                'Uji Kompetensi ' . $eskul->name,
            ];

            $exists = Activity::where('extracurricular_id', $eskul->id)
                ->whereDate('activity_date', $date->toDateString())
                ->exists();

            if ($exists) continue;

            $activity = Activity::create([
                'school_year_id'     => $year->id,
                'extracurricular_id' => $eskul->id,
                'schedule_id'        => null,
                'session_owner_id'   => $pembina->id,
                'type'               => 'non_routine',
                'title'              => $specialTitles[array_rand($specialTitles)],
                'description'        => 'Kegiatan khusus tahunan ekstrakurikuler ' . $eskul->name . '.',
                'activity_date'      => $date->toDateString(),
                'status'             => 'active',
                'attendance_phase'   => 'finished',
                'attendance_mode'    => 'manual',
                'started_at'         => $startedAt,
                'ended_at'           => $endedAt,
                'created_by'         => $pembina->id,
            ]);
        }
    }

    private function getHolidays(int $startYear, int $endYear): array
    {
        $holidays = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $holidays = array_merge($holidays, [
                // Tahun Baru
                "{$year}-01-01",
                // Hari Raya Imlek (approx)
                "{$year}-02-10",
                // Isra Mi'raj (approx)
                "{$year}-02-08",
                // Hari Raya Nyepi
                "{$year}-03-11",
                // Wafat Isa Almasih
                "{$year}-03-29",
                // Hari Buruh
                "{$year}-05-01",
                // Kenaikan Isa Almasih
                "{$year}-05-09",
                // Hari Raya Waisak
                "{$year}-05-23",
                // Hari Lahir Pancasila
                "{$year}-06-01",
                // Idul Fitri (approx - 2 hari)
                "{$year}-04-10",
                "{$year}-04-11",
                // Idul Adha (approx)
                "{$year}-06-17",
                // Tahun Baru Islam
                "{$year}-07-07",
                // Hari Kemerdekaan
                "{$year}-08-17",
                // Maulid Nabi
                "{$year}-09-16",
                // Hari Natal
                "{$year}-12-25",
                // Libur akhir tahun
                "{$year}-12-26",
                "{$year}-12-30",
                "{$year}-12-31",
            ]);
        }

        return array_unique($holidays);
    }
}