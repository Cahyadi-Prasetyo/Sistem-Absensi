<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@absensi.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create karyawan users
        $karyawanNames = [
            'Ahmad Rizki',
            'Siti Nurhaliza',
            'Budi Santoso',
            'Dewi Lestari',
            'Eko Prasetyo',
            'Fitri Handayani',
            'Gunawan Wijaya',
            'Hana Pertiwi',
            'Indra Kusuma',
            'Joko Widodo',
            'Rina Melati',
        ];

        foreach ($karyawanNames as $index => $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@absensi.com';
            
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'karyawan',
                ]
            );
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin: admin@absensi.com / password');
        $this->command->info('Karyawan: [nama].@absensi.com / password');
    }
}
