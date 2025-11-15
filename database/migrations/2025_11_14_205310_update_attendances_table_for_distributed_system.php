<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['type', 'timestamp', 'latitude', 'longitude']);
            
            // Add new columns for distributed system
            $table->date('date')->after('user_id');
            $table->timestamp('jam_masuk')->nullable()->after('date');
            $table->timestamp('jam_pulang')->nullable()->after('jam_masuk');
            $table->decimal('latitude_masuk', 10, 8)->nullable()->after('jam_pulang');
            $table->decimal('longitude_masuk', 11, 8)->nullable()->after('latitude_masuk');
            $table->decimal('latitude_pulang', 10, 8)->nullable()->after('longitude_masuk');
            $table->decimal('longitude_pulang', 11, 8)->nullable()->after('latitude_pulang');
            $table->enum('status', ['Hadir', 'Terlambat', 'Alpha'])->default('Hadir')->after('node_id');
            $table->integer('duration_minutes')->nullable()->after('status');
            
            // Add unique constraint for user_id and date
            $table->unique(['user_id', 'date']);
            
            // Update indexes
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop new columns
            $table->dropUnique(['user_id', 'date']);
            $table->dropColumn([
                'date', 'jam_masuk', 'jam_pulang',
                'latitude_masuk', 'longitude_masuk',
                'latitude_pulang', 'longitude_pulang',
                'status', 'duration_minutes'
            ]);
            
            // Restore old columns
            $table->enum('type', ['in', 'out'])->after('user_id');
            $table->timestamp('timestamp')->after('type');
            $table->decimal('latitude', 10, 8)->nullable()->after('timestamp');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }
};
