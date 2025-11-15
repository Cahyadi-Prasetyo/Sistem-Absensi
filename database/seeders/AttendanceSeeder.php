<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawans = User::where('role', 'karyawan')->get();
        
        if ($karyawans->isEmpty()) {
            $this->command->warn('No karyawan users found. Please run UserSeeder first.');
            return;
        }

        // Generate attendance for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            foreach ($karyawans as $karyawan) {
                // 90% chance of attendance
                if (rand(1, 100) <= 90) {
                    $this->createAttendance($karyawan, $date);
                }
            }
        }

        $this->command->info('Attendance data seeded successfully!');
    }

    private function createAttendance(User $user, Carbon $date)
    {
        // Random clock in time between 07:30 and 09:00
        $jamMasuk = $date->copy()
            ->setHour(rand(7, 8))
            ->setMinute(rand(0, 59))
            ->setSecond(0);

        // Determine status based on clock in time
        $status = ($jamMasuk->hour < 8 || ($jamMasuk->hour == 8 && $jamMasuk->minute <= 30)) 
            ? 'Hadir' 
            : 'Terlambat';

        // 80% chance of clock out
        $hasClockOut = rand(1, 100) <= 80;
        
        $jamPulang = null;
        $durationMinutes = null;
        
        if ($hasClockOut) {
            // Clock out between 16:00 and 18:00
            $jamPulang = $date->copy()
                ->setHour(rand(16, 17))
                ->setMinute(rand(0, 59))
                ->setSecond(0);
            
            $durationMinutes = $jamPulang->diffInMinutes($jamMasuk);
        } else {
            // No clock out = Alpha
            $status = 'Alpha';
        }

        Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $date->toDateString(),
            ],
            [
                'jam_masuk' => $jamMasuk,
                'jam_pulang' => $jamPulang,
                'latitude_masuk' => -6.2088 + (rand(-100, 100) / 10000), // Jakarta area
                'longitude_masuk' => 106.8456 + (rand(-100, 100) / 10000),
                'latitude_pulang' => $hasClockOut ? -6.2088 + (rand(-100, 100) / 10000) : null,
                'longitude_pulang' => $hasClockOut ? 106.8456 + (rand(-100, 100) / 10000) : null,
                'node_id' => 'app-node-' . rand(1, 3),
                'status' => $status,
                'duration_minutes' => $durationMinutes,
            ]
        );
    }
}
