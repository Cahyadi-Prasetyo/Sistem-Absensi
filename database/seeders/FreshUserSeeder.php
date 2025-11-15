<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FreshUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus semua data absensi terlebih dahulu
        $this->command->info('Menghapus semua data absensi...');
        Attendance::truncate();
        
        // Hapus semua user kecuali admin
        $this->command->info('Menghapus semua karyawan...');
        User::where('role', 'karyawan')->delete();
        
        // Buat admin jika belum ada
        $this->command->info('Membuat akun admin...');
        User::updateOrCreate(
            ['email' => 'admin@absensi.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Buat karyawan baru tanpa riwayat absensi
        $this->command->info('Membuat karyawan baru...');
        $karyawanNames = [
            'Andi Wijaya',
            'Bella Safira',
            'Citra Dewi',
            'Doni Pratama',
            'Eka Putri',
            'Fajar Ramadhan',
            'Gita Maharani',
            'Hendra Gunawan',
            'Indah Permata',
            'Joko Susilo',
        ];

        foreach ($karyawanNames as $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@absensi.com';
            
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'karyawan',
            ]);
            
            $this->command->info("✓ {$name} - {$email}");
        }

        $this->command->info('');
        $this->command->info('==============================================');
        $this->command->info('Data berhasil di-reset!');
        $this->command->info('==============================================');
        $this->command->info('');
        $this->command->info('Admin Account:');
        $this->command->info('  Email: admin@absensi.com');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('Karyawan Accounts (10 users):');
        $this->command->info('  Email: [nama.lengkap]@absensi.com');
        $this->command->info('  Password: password');
        $this->command->info('  Contoh: andi.wijaya@absensi.com');
        $this->command->info('');
        $this->command->info('Semua karyawan BELUM memiliki riwayat absensi');
        $this->command->info('==============================================');
    }
}
