<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jamMasuk = fake()->dateTimeBetween('-1 month', 'now');
        $jamPulang = (clone $jamMasuk)->modify('+' . rand(7, 10) . ' hours');
        $durationMinutes = ($jamPulang->getTimestamp() - $jamMasuk->getTimestamp()) / 60;
        
        // Determine status based on clock in time
        $hour = (int) $jamMasuk->format('H');
        $minute = (int) $jamMasuk->format('i');
        $status = ($hour < 8 || ($hour == 8 && $minute <= 30)) ? 'Hadir' : 'Terlambat';
        
        return [
            'user_id' => User::factory(),
            'date' => $jamMasuk->format('Y-m-d'),
            'jam_masuk' => $jamMasuk,
            'jam_pulang' => $jamPulang,
            'latitude_masuk' => fake()->latitude(-6.3, -6.1), // Jakarta area
            'longitude_masuk' => fake()->longitude(106.7, 106.9),
            'latitude_pulang' => fake()->latitude(-6.3, -6.1),
            'longitude_pulang' => fake()->longitude(106.7, 106.9),
            'node_id' => fake()->randomElement(['app-node-1', 'app-node-2', 'app-node-3', 'dev-node-1']),
            'status' => $status,
            'duration_minutes' => (int) $durationMinutes,
        ];
    }

    /**
     * Indicate that the attendance is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->format('Y-m-d'),
            'jam_masuk' => now()->setHour(8)->setMinute(rand(0, 45)),
        ]);
    }

    /**
     * Indicate that the attendance is late.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'jam_masuk' => now()->setHour(9)->setMinute(rand(0, 59)),
            'status' => 'Terlambat',
        ]);
    }

    /**
     * Indicate that the attendance is absent (no clock out).
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'jam_pulang' => null,
            'latitude_pulang' => null,
            'longitude_pulang' => null,
            'status' => 'Alpha',
            'duration_minutes' => null,
        ]);
    }
}
