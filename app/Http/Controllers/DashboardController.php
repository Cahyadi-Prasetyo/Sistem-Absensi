<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = today();
        
        // Today's statistics
        $todayAttendances = Attendance::whereDate('check_in', $today)->count();
        $totalUsers = User::count();
        $presentToday = Attendance::whereDate('check_in', $today)
            ->where('status', 'present')
            ->count();
        $lateToday = Attendance::whereDate('check_in', $today)
            ->where('status', 'late')
            ->count();

        // Recent attendances (live updating list)
        $recentAttendances = Attendance::with('user')
            ->whereDate('check_in', $today)
            ->latest('check_in')
            ->limit(10)
            ->get();

        // User's today attendance
        $myAttendance = $request->user()->todayAttendance();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'today_attendances' => $todayAttendances,
                'present_today' => $presentToday,
                'late_today' => $lateToday,
                'absent_today' => $totalUsers - $todayAttendances,
            ],
            'recent_attendances' => $recentAttendances,
            'my_attendance' => $myAttendance,
        ]);
    }

    public function liveStats()
    {
        $today = today();
        
        return response()->json([
            'today_attendances' => Attendance::whereDate('check_in', $today)->count(),
            'present_today' => Attendance::whereDate('check_in', $today)->where('status', 'present')->count(),
            'late_today' => Attendance::whereDate('check_in', $today)->where('status', 'late')->count(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
