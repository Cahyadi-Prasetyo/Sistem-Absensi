<?php

namespace App\Listeners;

use App\Events\AbsensiCreated;
use App\Models\AttendanceLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAbsensiEvent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AbsensiCreated $event): void
    {
        // Log the event to attendance_logs table
        AttendanceLog::create([
            'attendance_id' => $event->attendanceData['id'],
            'user_id' => $event->attendanceData['user_id'],
            'event_type' => 'absensi_created',
            'node_id' => $event->attendanceData['node_id'],
            'payload' => $event->attendanceData,
            'created_at' => now(),
        ]);
    }
}
