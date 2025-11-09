<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'check_in_location',
        'check_out_location',
        'check_in_photo',
        'check_out_photo',
        'status',
        'notes',
        'node_id',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'check_in_location' => 'array',
        'check_out_location' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isLate(): bool
    {
        $settings = AttendanceSetting::first();
        if (!$settings) {
            return false;
        }

        $workStart = \Carbon\Carbon::parse($settings->work_start_time);
        $checkIn = \Carbon\Carbon::parse($this->check_in);
        
        $minutesLate = $checkIn->diffInMinutes($workStart, false);
        
        return $minutesLate > $settings->late_tolerance;
    }

    public function getWorkDurationAttribute(): ?int
    {
        if (!$this->check_out) {
            return null;
        }

        return $this->check_in->diffInMinutes($this->check_out);
    }
}
