<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "========================================\n";
echo "  Database Verification\n";
echo "========================================\n\n";

// Check connection
try {
    DB::connection()->getPdo();
    echo "✓ Database connection: OK\n";
    echo "  Database: " . DB::connection()->getDatabaseName() . "\n";
    echo "  Driver: " . DB::connection()->getDriverName() . "\n\n";
} catch (\Exception $e) {
    echo "✗ Database connection: FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Check users
$userCount = \App\Models\User::count();
echo "Total Users: $userCount\n\n";

if ($userCount > 0) {
    echo "Users List:\n";
    echo str_repeat("-", 60) . "\n";
    printf("%-25s %-25s %-10s\n", "Name", "Email", "Role");
    echo str_repeat("-", 60) . "\n";
    
    \App\Models\User::all(['name', 'email', 'role'])->each(function($user) {
        printf("%-25s %-25s %-10s\n", 
            substr($user->name, 0, 24), 
            substr($user->email, 0, 24), 
            $user->role
        );
    });
    echo str_repeat("-", 60) . "\n";
}

// Check tables
echo "\nDatabase Tables:\n";
$tables = DB::select('SHOW TABLES');
$dbName = 'Tables_in_' . DB::connection()->getDatabaseName();
foreach ($tables as $table) {
    echo "  - " . $table->$dbName . "\n";
}

echo "\n";
echo "========================================\n";
echo "  ✓ Database Setup Complete!\n";
echo "========================================\n\n";

echo "Access Application:\n";
echo "  - Local: http://localhost:8000/login\n";
echo "  - WAMP: http://localhost/absensi/public/login\n\n";

echo "Login Credentials:\n";
echo "  - admin@test.com / password (Admin)\n";
echo "  - teacher@test.com / password (Teacher)\n";
echo "  - student1@test.com / password (Student)\n\n";
