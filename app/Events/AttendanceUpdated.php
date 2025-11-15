<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Attendance $attendance)
    {
        $this->attendance->load('user');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('attendances'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->attendance->id,
            'user' => [
                'id' => $this->attendance->user->id,
                'name' => $this->attendance->user->name,
                'role' => $this->attendance->user->role,
            ],
            'date' => $this->attendance->date->format('Y-m-d'),
            'jam_masuk' => $this->attendance->jam_masuk?->format('H:i:s'),
            'jam_pulang' => $this->attendance->jam_pulang?->format('H:i:s'),
            'duration_minutes' => $this->attendance->duration_minutes,
            'status' => $this->attendance->status,
            'node_id' => $this->attendance->node_id,
            'updated_at' => $this->attendance->updated_at->toISOString(),
        ];
    }
}
