<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get dashboard metrics (with caching)
     */
    public function getMetrics(): array
    {
        // Cache for 1 minute
        return cache()->remember('dashboard.metrics', 60, function () {
            $today = now()->toDateString();
            $weekStart = now()->startOfWeek()->toDateString();
            $weekEnd = now()->endOfWeek()->toDateString();

            // Count today's attendances
            $todayCount = Attendance::whereDate('date', $today)->count();

            // Count this week's attendances
            $weekCount = Attendance::whereBetween('date', [$weekStart, $weekEnd])->count();

            // Calculate attendance rate (percentage of employees who attended today)
            $totalEmployees = cache()->remember('total.employees', 300, function () {
                return \App\Models\User::where('role', 'karyawan')->count();
            });
            
            $attendanceRate = $totalEmployees > 0 
                ? round(($todayCount / $totalEmployees) * 100) 
                : 0;

            // Get server status
            $serverStatus = app(ServerStatusService::class)->getServerStatus();
            $serversOnline = collect($serverStatus)->where('status', 'online')->count();
            $serversTotal = count($serverStatus);

            return [
                'today_count' => $todayCount,
                'week_count' => $weekCount,
                'attendance_rate' => $attendanceRate,
                'servers_online' => $serversOnline,
                'servers_total' => $serversTotal,
            ];
        });
    }

    /**
     * Get latest attendances for dashboard
     */
    public function getLatestAttendances(int $limit = 10): array
    {
        return Attendance::with('user')
            ->whereNotNull('jam_masuk')
            ->orderBy('jam_masuk', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'user_name' => $attendance->user->name,
                    'user_photo' => null, // TODO: Add user photo
                    'jam_masuk' => $attendance->jam_masuk->format('H:i'),
                    'jam_pulang' => $attendance->jam_pulang?->format('H:i'),
                    'duration_minutes' => $attendance->duration_minutes,
                    'status' => $attendance->status,
                    'status_color' => $attendance->status_color,
                ];
            })
            ->toArray();
    }
}
