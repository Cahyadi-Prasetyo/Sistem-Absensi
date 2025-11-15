<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Events\AttendanceCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceService
{
    private string $nodeId;

    public function __construct()
    {
        $this->nodeId = config('app.node_id', gethostname());
    }

    public function createAttendance(array $data): array
    {
        try {
            // Validate duplicate attendance within 5 minutes
            $recentAttendance = Attendance::where('user_id', $data['user_id'])
                ->where('type', $data['type'])
                ->where('timestamp', '>', Carbon::now()->subMinutes(5))
                ->first();

            if ($recentAttendance) {
                $this->logEvent(null, 'duplicate_rejected', $data);
                return [
                    'success' => false,
                    'message' => 'Duplicate attendance detected within 5 minutes',
                ];
            }

            // Use distributed lock to prevent race condition
            $lockKey = "attendance:lock:{$data['user_id']}:{$data['type']}";
            $lock = Cache::lock($lockKey, 10);

            if (!$lock->get()) {
                return [
                    'success' => false,
                    'message' => 'Another attendance is being processed',
                ];
            }

            try {
                $attendance = DB::transaction(function () use ($data) {
                    $attendance = Attendance::create([
                        'user_id' => $data['user_id'],
                        'type' => $data['type'],
                        'timestamp' => $data['timestamp'] ?? now(),
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                        'node_id' => $this->nodeId,
                    ]);

                    $this->logEvent($attendance->id, 'created', [
                        'user_id' => $attendance->user_id,
                        'type' => $attendance->type,
                        'timestamp' => $attendance->timestamp,
                    ]);

                    return $attendance;
                });

                // Broadcast event for real-time sync
                event(new AttendanceCreated($attendance));

                return [
                    'success' => true,
                    'data' => $attendance->load('user'),
                    'node_id' => $this->nodeId,
                ];
            } finally {
                $lock->release();
            }
        } catch (\Exception $e) {
            $this->logEvent(null, 'error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create attendance: ' . $e->getMessage(),
            ];
        }
    }

    public function getTodayAttendances()
    {
        return Attendance::with('user')
            ->whereDate('timestamp', today())
            ->orderBy('timestamp', 'desc')
            ->get();
    }

    private function logEvent(?int $attendanceId, string $eventType, array $payload): void
    {
        AttendanceLog::create([
            'attendance_id' => $attendanceId,
            'event_type' => $eventType,
            'node_id' => $this->nodeId,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
