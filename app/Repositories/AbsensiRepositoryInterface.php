<?php

namespace App\Repositories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface AbsensiRepositoryInterface
{
    /**
     * Create new attendance record
     */
    public function create(array $data): Attendance;

    /**
     * Update attendance record
     */
    public function update(int $id, array $data): Attendance;

    /**
     * Find attendance by user and date
     */
    public function findByUserAndDate(int $userId, Carbon $date): ?Attendance;

    /**
     * Get latest attendances
     */
    public function getLatestAttendances(int $limit = 10): Collection;

    /**
     * Get user attendances with optional date filtering
     */
    public function getUserAttendances(
        int $userId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    );

    /**
     * Get all attendances with optional filtering
     */
    public function getAllAttendances(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?string $search = null
    );
}
