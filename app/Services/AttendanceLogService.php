<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceLogService
{
    /**
     * Log attendance event
     */
    public function log(string $eventType, Attendance $attendance, ?array $metadata = []): AttendanceLog
    {
        return AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'event_type' => $eventType,
            'event_data' => array_merge([
                'date' => $attendance->date->format('Y-m-d'),
                'jam_masuk' => $attendance->jam_masuk?->format('H:i:s'),
                'jam_pulang' => $attendance->jam_pulang?->format('H:i:s'),
                'status' => $attendance->status,
                'node_id' => $attendance->node_id,
            ], $metadata),
            'node_id' => config('app.node_id', 'dev-node-1'),
            'performed_by' => Auth::id(),
        ]);
    }

    /**
     * Log clock in event
     */
    public function logClockIn(Attendance $attendance): AttendanceLog
    {
        return $this->log('clock_in', $attendance, [
            'latitude' => $attendance->latitude_masuk,
            'longitude' => $attendance->longitude_masuk,
        ]);
    }

    /**
     * Log clock out event
     */
    public function logClockOut(Attendance $attendance): AttendanceLog
    {
        return $this->log('clock_out', $attendance, [
            'latitude' => $attendance->latitude_pulang,
            'longitude' => $attendance->longitude_pulang,
            'duration_minutes' => $attendance->duration_minutes,
        ]);
    }

    /**
     * Log update event
     */
    public function logUpdate(Attendance $attendance, array $changes): AttendanceLog
    {
        return $this->log('update', $attendance, [
            'changes' => $changes,
        ]);
    }

    /**
     * Log delete event
     */
    public function logDelete(Attendance $attendance): AttendanceLog
    {
        return $this->log('delete', $attendance);
    }

    /**
     * Get logs for attendance
     */
    public function getLogsForAttendance(int $attendanceId)
    {
        return AttendanceLog::where('attendance_id', $attendanceId)
            ->with('user', 'performedBy')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get logs for user
     */
    public function getLogsForUser(int $userId, ?int $limit = null)
    {
        $query = AttendanceLog::where('user_id', $userId)
            ->with('attendance', 'performedBy')
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
