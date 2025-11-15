<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PartialAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan membuat data absensi untuk SEBAGIAN karyawan saja.
     * Sisanya akan tetap kosong (belum pernah absen).
     */
    public function run(): void
    {
        $this->command->info('Membuat data absensi untuk sebagian karyawan...');
        $this->command->info('');

        // Ambil semua karyawan
        $karyawan = User::where('role', 'karyawan')->get();
        
        if ($karyawan->isEmpty()) {
            $this->command->error('Tidak ada karyawan! Jalankan FreshUserSeeder terlebih dahulu.');
            return;
        }

        // Pilih 5 karyawan pertama untuk diberi riwayat absensi
        $karyawanDenganAbsensi = $karyawan->take(5);
        
        // 5 karyawan sisanya tidak akan diberi riwayat absensi
        $karyawanTanpaAbsensi = $karyawan->skip(5);

        $this->command->info('Karyawan yang AKAN diberi riwayat absensi:');
        foreach ($karyawanDenganAbsensi as $user) {
            $this->command->info("  ✓ {$user->name} ({$user->email})");
        }
        $this->command->info('');

        // Generate absensi untuk 7 hari terakhir
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();
        $nodeIds = ['app-node-1', 'app-node-2', 'app-node-3'];

        foreach ($karyawanDenganAbsensi as $user) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Skip weekend (Sabtu & Minggu)
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                // Random: 80% hadir, 15% terlambat, 5% tidak absen
                $random = rand(1, 100);
                
                if ($random <= 5) {
                    // 5% tidak absen (skip)
                    $currentDate->addDay();
                    continue;
                }

                // Tentukan jam masuk
                if ($random <= 85) {
                    // 80% hadir tepat waktu (06:30 - 07:30)
                    $jamMasuk = $currentDate->copy()->setTime(
                        rand(6, 7),
                        rand(0, 59),
                        rand(0, 59)
                    );
                    $status = 'Hadir';
                } else {
                    // 15% terlambat (07:31 - 09:00)
                    $jamMasuk = $currentDate->copy()->setTime(
                        rand(7, 8),
                        rand(31, 59),
                        rand(0, 59)
                    );
                    $status = 'Terlambat';
                }

                // Tentukan jam pulang (16:00 - 18:00)
                $jamPulang = $currentDate->copy()->setTime(
                    rand(16, 17),
                    rand(0, 59),
                    rand(0, 59)
                );

                // Hitung durasi
                $durationMinutes = $jamPulang->diffInMinutes($jamMasuk);

                // Random node
                $nodeId = $nodeIds[array_rand($nodeIds)];

                // Random koordinat (simulasi lokasi kantor)
                $latitude = -6.2088 + (rand(-100, 100) / 10000); // Jakarta area
                $longitude = 106.8456 + (rand(-100, 100) / 10000);

                // Buat attendance record
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $currentDate->toDateString(),
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'latitude_masuk' => $latitude,
                    'longitude_masuk' => $longitude,
                    'latitude_pulang' => $latitude + 0.0001,
                    'longitude_pulang' => $longitude + 0.0001,
                    'status' => $status,
                    'duration_minutes' => $durationMinutes,
                    'node_id' => $nodeId,
                ]);

                // Buat attendance logs
                AttendanceLog::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'event_type' => 'clock_in',
                    'event_data' => [
                        'date' => $currentDate->toDateString(),
                        'jam_masuk' => $jamMasuk->format('H:i:s'),
                        'status' => $status,
                        'node_id' => $nodeId,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                    'node_id' => $nodeId,
                    'performed_by' => $user->id,
                ]);

                AttendanceLog::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'event_type' => 'clock_out',
                    'event_data' => [
                        'date' => $currentDate->toDateString(),
                        'jam_pulang' => $jamPulang->format('H:i:s'),
                        'duration_minutes' => $durationMinutes,
                        'node_id' => $nodeId,
                        'latitude' => $latitude + 0.0001,
                        'longitude' => $longitude + 0.0001,
                    ],
                    'node_id' => $nodeId,
                    'performed_by' => $user->id,
                ]);

                $currentDate->addDay();
            }
        }

        $this->command->info('');
        $this->command->info('==============================================');
        $this->command->info('Data absensi berhasil dibuat!');
        $this->command->info('==============================================');
        $this->command->info('');
        $this->command->info("Karyawan DENGAN riwayat absensi: {$karyawanDenganAbsensi->count()} orang");
        foreach ($karyawanDenganAbsensi as $user) {
            $count = Attendance::where('user_id', $user->id)->count();
            $this->command->info("  • {$user->name}: {$count} hari");
        }
        
        $this->command->info('');
        $this->command->info("Karyawan TANPA riwayat absensi: {$karyawanTanpaAbsensi->count()} orang");
        foreach ($karyawanTanpaAbsensi as $user) {
            $this->command->info("  • {$user->name} ({$user->email})");
        }
        
        $this->command->info('');
        $this->command->info('Periode: 7 hari terakhir (hari kerja saja)');
        $this->command->info('Status: 80% Hadir, 15% Terlambat, 5% Tidak Absen');
        $this->command->info('==============================================');
    }
}
