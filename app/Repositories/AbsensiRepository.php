<?php

namespace App\Repositories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AbsensiRepository implements AbsensiRepositoryInterface
{
    /**
     * Create new attendance record
     */
    public function create(array $data): Attendance
    {
        return Attendance::create($data);
    }

    /**
     * Update attendance record
     */
    public function update(int $id, array $data): Attendance
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($data);
        return $attendance->fresh();
    }

    /**
     * Find attendance by user and date
     */
    public function findByUserAndDate(int $userId, Carbon $date): ?Attendance
    {
        return Attendance::where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();
    }

    /**
     * Get latest attendances
     */
    public function getLatestAttendances(int $limit = 10): Collection
    {
        return Attendance::with('user')
            ->orderBy('jam_masuk', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user attendances with optional date filtering
     */
    public function getUserAttendances(
        int $userId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ) {
        $query = Attendance::where('user_id', $userId)
            ->orderBy('date', 'asc');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        return $query->paginate(10);
    }

    /**
     * Get all attendances with optional filtering
     */
    public function getAllAttendances(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?string $search = null
    ) {
        $query = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('jam_masuk', 'desc');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }
}
