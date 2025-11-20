<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbsensiCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $attendanceData;

    /**
     * Create a new event instance.
     */
    public function __construct(Attendance $attendance)
    {
        // Load user relationship
        $attendance->load('user');

        // Prepare data for broadcasting
        $this->attendanceData = [
            'id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'user_name' => $attendance->user->name,
            'date' => $attendance->date->format('Y-m-d'),
            'jam_masuk' => $attendance->jam_masuk?->format('H:i'),
            'jam_pulang' => $attendance->jam_pulang?->format('H:i'),
            'status' => $attendance->status,
            'duration' => $attendance->getDurationFormatted(),
            'node_id' => $attendance->node_id,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('attendances'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AttendanceCreated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'attendance' => $this->attendanceData,
        ];
    }
}
