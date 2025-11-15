<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'jam_masuk',
        'jam_pulang',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'node_id',
        'status',
        'duration_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'latitude_masuk' => 'float',
        'longitude_masuk' => 'float',
        'latitude_pulang' => 'float',
        'longitude_pulang' => 'float',
        'duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Get formatted duration string
     */
    public function getDurationFormatted(): string
    {
        if (!$this->duration_minutes) {
            return '-';
        }
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        return "{$hours}j {$minutes}m";
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Hadir' => 'green',
            'Terlambat' => 'yellow',
            'Alpha' => 'red',
            default => 'gray',
        };
    }
}
