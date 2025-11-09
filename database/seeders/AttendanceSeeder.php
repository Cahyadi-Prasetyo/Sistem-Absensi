<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Create attendance settings
        AttendanceSetting::create([
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'late_tolerance' => 15,
            'location_radius' => 100,
            'require_photo' => false,
            'require_location' => false,
        ]);

        // Create some test users if not exists
        $users = User::all();
        
        if ($users->count() < 5) {
            for ($i = $users->count(); $i < 5; $i++) {
                User::create([
                    'name' => "User " . ($i + 1),
                    'email' => "user" . ($i + 1) . "@example.com",
                    'password' => bcrypt('password'),
                    'role' => 'pegawai',
                    'employee_id' => 'EMP' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                ]);
            }
            $users = User::all();
        }

        // Create today's attendances for some users
        $today = today();
        $statuses = ['present', 'late'];
        
        foreach ($users->take(3) as $index => $user) {
            $checkIn = $today->copy()->addHours(8)->addMinutes(rand(-10, 30));
            $status = $checkIn->format('H:i') > '08:15' ? 'late' : 'present';
            
            Attendance::create([
                'user_id' => $user->id,
                'check_in' => $checkIn,
                'check_out' => rand(0, 1) ? $checkIn->copy()->addHours(9) : null,
                'status' => $status,
                'node_id' => rand(1, 3),
            ]);
        }

        // Create some historical attendances
        for ($day = 1; $day <= 7; $day++) {
            $date = today()->subDays($day);
            
            foreach ($users->random(rand(2, 4)) as $user) {
                $checkIn = $date->copy()->addHours(8)->addMinutes(rand(-10, 30));
                $status = $checkIn->format('H:i') > '08:15' ? 'late' : 'present';
                
                Attendance::create([
                    'user_id' => $user->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkIn->copy()->addHours(rand(8, 10)),
                    'status' => $status,
                    'node_id' => rand(1, 3),
                ]);
            }
        }
    }
}
