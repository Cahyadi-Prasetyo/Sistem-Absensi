<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'work_start_time',
        'work_end_time',
        'late_tolerance',
        'location_radius',
        'require_photo',
        'require_location',
        'office_locations',
    ];

    protected $casts = [
        'work_start_time' => 'datetime:H:i:s',
        'work_end_time' => 'datetime:H:i:s',
        'require_photo' => 'boolean',
        'require_location' => 'boolean',
        'office_locations' => 'array',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'late_tolerance' => 15,
            'location_radius' => 100,
            'require_photo' => false,
            'require_location' => false,
        ]);
    }
}
