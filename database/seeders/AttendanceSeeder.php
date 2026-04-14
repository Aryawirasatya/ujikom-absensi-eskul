<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\ExtracurricularMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Distribusi kehadiran realistis:
     * - hadir    : 65%
     * - telat    : 10%  (khusus mode QR)
     * - izin     : 8%
     * - sakit    : 7%
     * - alpha    : 10%
     * - libur    : 0% (sudah dihandle di cancel)
     */
    private array $statusWeights = [
        'hadir' => 65,
        'izin'  => 8,
        'sakit' => 7,
        'alpha' => 20,  // sisanya alpha (incl beberapa yg late)
    ];

    public function run(): void
    {
        // Ambil semua aktivitas yang finished
        $activities = Activity::with([
            'extracurricular.members' => fn($q) => $q->where('status', 'active'),
            'qrSessions',
        ])
        ->where('attendance_phase', 'finished')
        ->get();

        $this->command->info("Seeding attendance for {$activities->count()} activities...");

        $bar = $this->command->getOutput()->createProgressBar($activities->count());
        $bar->start();

        $batchSize = 500;
        $buffer = [];

        foreach ($activities as $activity) {
            $members = $this->getActiveMembers($activity);

            if ($members->isEmpty()) {
                $bar->advance();
                continue;
            }

            $startedAt = $activity->started_at ?? Carbon::parse($activity->activity_date)->setTime(14, 0);
            $endedAt   = $activity->ended_at   ?? $startedAt->copy()->addMinutes(90);

            // Sesi checkin (untuk menentukan late threshold)
            $checkinSession = $activity->qrSessions->where('mode', 'checkin')->first();
            $lateThreshold  = $checkinSession
                ? Carbon::parse($checkinSession->opened_at)->addMinutes($checkinSession->duration_minutes)
                : $startedAt->copy()->addMinutes(15);

            foreach ($members as $member) {
                $roll = rand(1, 100);
                $cumulative = 0;

                $finalStatus = 'alpha';
                foreach ($this->statusWeights as $status => $weight) {
                    $cumulative += $weight;
                    if ($roll <= $cumulative) {
                        $finalStatus = $status;
                        break;
                    }
                }

                [$checkinAt, $checkoutAt, $checkinStatus, $source] = $this->resolveTimings(
                    $finalStatus, $startedAt, $endedAt, $lateThreshold, $activity->attendance_mode
                );

                // Jika mode manual, tidak ada checkin_status 'late' — jadi override
                if ($activity->attendance_mode === 'manual' && $checkinStatus === 'late') {
                    $checkinStatus = 'on_time';
                    $finalStatus   = 'hadir';
                }

                $buffer[] = [
                    'activity_id'       => $activity->id,
                    'user_id'           => $member->user_id,
                    'checkin_at'        => $checkinAt,
                    'checkout_at'       => $checkoutAt,
                    'checkin_status'    => $checkinStatus,
                    'final_status'      => $finalStatus,
                    'attendance_source' => $source,
                    'note'              => $this->generateNote($finalStatus),
                    'updated_by'        => null,
                    'created_at'        => $startedAt,
                    'updated_at'        => $endedAt,
                ];

                if (count($buffer) >= $batchSize) {
                    $this->flushBuffer($buffer);
                    $buffer = [];
                }
            }

            $bar->advance();
        }

        // Flush sisa buffer
        if (!empty($buffer)) {
            $this->flushBuffer($buffer);
        }

        $bar->finish();
        $this->command->newLine();

        $total = Attendance::count();
        $this->command->info("✅ Attendance seeded: {$total} records total.");
    }

    private function getActiveMembers(Activity $activity)
    {
        return ExtracurricularMember::where('extracurricular_id', $activity->extracurricular_id)
            ->where('status', 'active')
            ->get();
    }

    private function resolveTimings(
        string $finalStatus,
        Carbon $startedAt,
        Carbon $endedAt,
        Carbon $lateThreshold,
        ?string $mode
    ): array {
        $source = 'scan';

        switch ($finalStatus) {
            case 'hadir':
                // Checkin antara startedAt sampai lateThreshold (on time)
                $checkinAt  = $startedAt->copy()->addMinutes(rand(0, (int) $startedAt->diffInMinutes($lateThreshold) - 1));
                $checkoutAt = $endedAt->copy()->subMinutes(rand(0, 10));
                $checkinStatus = 'on_time';
                $source = ($mode === 'manual') ? 'manual' : 'scan';
                break;

            case 'izin':
            case 'sakit':
                $checkinAt     = null;
                $checkoutAt    = null;
                $checkinStatus = 'absent';
                $source        = 'manual';
                break;

            case 'alpha':
            default:
                // 30% alpha masih punya scan checkin tapi tidak checkout
                if (rand(1, 100) <= 30 && $mode === 'qr') {
                    // Terlambat dan dianggap alpha oleh sistem
                    $checkinAt     = $lateThreshold->copy()->addMinutes(rand(1, 20));
                    $checkoutAt    = null;
                    $checkinStatus = 'late';
                    $finalStatus   = 'hadir'; // telat tapi hadir
                    $source        = 'scan';
                } else {
                    $checkinAt     = null;
                    $checkoutAt    = null;
                    $checkinStatus = 'absent';
                    $source        = 'system';
                }
                break;
        }

        return [$checkinAt, $checkoutAt, $checkinStatus, $source];
    }

    private function generateNote(string $finalStatus): ?string
    {
        $notes = [
            'izin' => [
                'Izin keperluan keluarga',
                'Izin acara keluarga',
                'Izin karena ada kegiatan sekolah lain',
                null,
            ],
            'sakit' => [
                'Demam',
                'Flu dan batuk',
                'Sakit kepala',
                null,
            ],
            'alpha' => [null, null, null, 'Tidak ada keterangan'],
            'hadir' => [null],
        ];

        $list = $notes[$finalStatus] ?? [null];
        return $list[array_rand($list)];
    }

    private function flushBuffer(array &$buffer): void
    {
        // Hindari duplikat: group by activity_id + user_id, ambil yang terakhir
        $unique = [];
        foreach ($buffer as $row) {
            $key = $row['activity_id'] . '_' . $row['user_id'];
            $unique[$key] = $row;
        }

        try {
            DB::table('attendances')->upsert(
                array_values($unique),
                ['activity_id', 'user_id'],
                [
                    'checkin_at', 'checkout_at', 'checkin_status',
                    'final_status', 'attendance_source', 'note',
                    'updated_by', 'updated_at',
                ]
            );
        } catch (\Exception $e) {
            // Fallback: insert satu per satu jika upsert gagal
            foreach ($unique as $row) {
                try {
                    DB::table('attendances')->updateOrInsert(
                        ['activity_id' => $row['activity_id'], 'user_id' => $row['user_id']],
                        $row
                    );
                } catch (\Exception $e2) {
                    // skip duplicate
                }
            }
        }
    }
}