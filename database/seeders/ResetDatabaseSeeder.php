<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResetDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds - Reset semua data dan ID dari awal
     */
    public function run(): void
    {
        $this->command->info('🔄 Memulai reset database...');
        $this->command->info('');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tables (reset ID auto increment)
        $this->command->info('🗑️  Menghapus semua data...');
        Attendance::truncate();
        User::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ Semua data berhasil dihapus');
        $this->command->info('');
        
        // Buat admin (ID akan mulai dari 1)
        $this->command->info('👤 Membuat akun admin...');
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@absensi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $this->command->info('✅ Admin created (ID: 1)');
        $this->command->info('');

        // Buat karyawan baru (ID akan mulai dari 2)
        $this->command->info('👥 Membuat karyawan baru...');
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

        $userId = 2; // Start from 2 (admin is 1)
        foreach ($karyawanNames as $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@absensi.com';
            
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'karyawan',
            ]);
            
            $this->command->info("  ✓ ID {$userId}: {$name} - {$email}");
            $userId++;
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('✅ DATABASE BERHASIL DI-RESET!');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('  • Total Users: ' . User::count());
        $this->command->info('  • Admin: 1 user (ID: 1)');
        $this->command->info('  • Karyawan: 10 users (ID: 2-11)');
        $this->command->info('  • Riwayat Absensi: 0 records');
        $this->command->info('');
        $this->command->info('🔐 Login Credentials:');
        $this->command->info('  Admin:');
        $this->command->info('    Email: admin@absensi.com');
        $this->command->info('    Password: password');
        $this->command->info('');
        $this->command->info('  Karyawan:');
        $this->command->info('    Email: [nama.lengkap]@absensi.com');
        $this->command->info('    Password: password');
        $this->command->info('    Contoh: andi.wijaya@absensi.com');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════');
    }
}
