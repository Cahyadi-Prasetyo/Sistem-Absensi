<?php

namespace App\Services;

use App\Events\AttendanceCreated;
use App\Events\AttendanceUpdated;
use App\Helpers\NodeHelper;
use App\Models\Attendance;
use App\Repositories\AbsensiRepositoryInterface;
use Carbon\Carbon;
use Exception;

class AbsensiService
{
    public function __construct(
        private AbsensiRepositoryInterface $repository,
        private AttendanceLogService $logService
    ) {}

    /**
     * Clock in (absen masuk)
     */
    public function clockIn(int $userId, float $latitude, float $longitude): Attendance
    {
        // Check if already clocked in today
        if ($this->hasClockInToday($userId)) {
            throw new Exception('Anda sudah melakukan absensi masuk hari ini');
        }

        $now = now();
        $status = $this->determineStatusFromTime($now);

        $data = [
            'user_id' => $userId,
            'date' => $now->toDateString(),
            'jam_masuk' => $now,
            'latitude_masuk' => $latitude,
            'longitude_masuk' => $longitude,
            'node_id' => NodeHelper::getNodeId(),
            'status' => $status,
        ];

        $attendance = $this->repository->create($data);
        
        // Log event untuk audit trail
        $this->logService->logClockIn($attendance);
        
        // Invalidate dashboard cache
        cache()->forget('dashboard.metrics');
        
        // Broadcast event untuk real-time update (local node)
        event(new AttendanceCreated($attendance));
        
        // Publish to Redis pub/sub untuk inter-node communication
        RedisEventSubscriber::publish('AttendanceCreated', $attendance->id);
        
        return $attendance;
    }

    /**
     * Clock out (absen pulang)
     */
    public function clockOut(int $userId, float $latitude, float $longitude): Attendance
    {
        $attendance = $this->getTodayAttendance($userId);

        if (!$attendance) {
            throw new Exception('Anda belum melakukan absensi masuk hari ini');
        }

        if ($attendance->jam_pulang) {
            throw new Exception('Anda sudah melakukan absensi pulang hari ini');
        }

        $now = now();
        $durationMinutes = $now->diffInMinutes($attendance->jam_masuk);

        $data = [
            'jam_pulang' => $now,
            'latitude_pulang' => $latitude,
            'longitude_pulang' => $longitude,
            'duration_minutes' => $durationMinutes,
        ];

        $updatedAttendance = $this->repository->update($attendance->id, $data);
        
        // Log event untuk audit trail
        $this->logService->logClockOut($updatedAttendance);
        
        // Invalidate dashboard cache
        cache()->forget('dashboard.metrics');
        
        // Broadcast event untuk real-time update (local node)
        event(new AttendanceUpdated($updatedAttendance));
        
        // Publish to Redis pub/sub untuk inter-node communication
        RedisEventSubscriber::publish('AttendanceUpdated', $updatedAttendance->id);
        
        return $updatedAttendance;
    }

    /**
     * Check if user has clocked in today
     */
    public function hasClockInToday(int $userId): bool
    {
        return $this->repository->findByUserAndDate($userId, now()) !== null;
    }

    /**
     * Get today's attendance for user
     */
    public function getTodayAttendance(int $userId): ?Attendance
    {
        return $this->repository->findByUserAndDate($userId, now());
    }

    /**
     * Calculate duration between clock in and clock out
     */
    public function calculateDuration(Attendance $attendance): string
    {
        return $attendance->getDurationFormatted();
    }

    /**
     * Determine status based on attendance
     */
    public function determineStatus(Attendance $attendance): string
    {
        // If no clock out by end of day, mark as Alpha
        if (!$attendance->jam_pulang && now()->isAfter($attendance->date->endOfDay())) {
            return 'Alpha';
        }

        return $attendance->status;
    }

    /**
     * Determine status from clock in time
     */
    private function determineStatusFromTime(Carbon $time): string
    {
        // Hadir if before or at 07:30
        $cutoffTime = $time->copy()->setTime(7, 30);
        
        return $time->lte($cutoffTime) ? 'Hadir' : 'Terlambat';
    }
}
