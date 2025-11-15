<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Seeder;

class EmptyAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan membuat karyawan baru TANPA riwayat absensi sama sekali.
     * Berguna untuk testing fitur absensi pertama kali.
     */
    public function run(): void
    {
        $this->command->info('Membersihkan semua data absensi...');
        Attendance::truncate();
        
        $this->command->info('');
        $this->command->info('==============================================');
        $this->command->info('Semua data absensi telah dihapus!');
        $this->command->info('==============================================');
        $this->command->info('');
        
        $karyawan = User::where('role', 'karyawan')->get();
        
        $this->command->info("Total karyawan: {$karyawan->count()} orang");
        $this->command->info('');
        $this->command->info('Karyawan yang BELUM memiliki riwayat absensi:');
        
        foreach ($karyawan as $user) {
            $this->command->info("  • {$user->name} ({$user->email})");
        }
        
        $this->command->info('');
        $this->command->info('Silakan login dan lakukan absensi pertama kali!');
        $this->command->info('Password untuk semua akun: password');
        $this->command->info('==============================================');
    }
}
