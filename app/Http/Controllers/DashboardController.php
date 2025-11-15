<?php

namespace App\Http\Controllers;

use App\Services\AbsensiService;
use App\Services\DashboardService;
use App\Services\ServerStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private ServerStatusService $serverStatusService,
        private AbsensiService $absensiService
    ) {}

    /**
     * Show dashboard based on user role
     */
    public function index()
    {
        if (Auth::user()->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->karyawanDashboard();
    }

    /**
     * Admin dashboard
     */
    private function adminDashboard()
    {
        $metrics = $this->dashboardService->getMetrics();
        $latestAttendances = $this->dashboardService->getLatestAttendances(10);
        $serverStatus = $this->serverStatusService->getServerStatus();

        return view('admin.dashboard', compact('metrics', 'latestAttendances', 'serverStatus'));
    }

    /**
     * Karyawan dashboard (Portal Karyawan)
     */
    private function karyawanDashboard()
    {
        $todayAttendance = $this->absensiService->getTodayAttendance(Auth::id());
        
        $canClockIn = $todayAttendance === null;
        $canClockOut = $todayAttendance !== null && $todayAttendance->jam_pulang === null;

        return view('karyawan.dashboard', compact('todayAttendance', 'canClockIn', 'canClockOut'));
    }

    /**
     * API endpoint for dashboard metrics (for real-time updates)
     */
    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getMetrics(),
        ]);
    }

    /**
     * API endpoint for latest attendances (for real-time updates)
     */
    public function latestAttendances()
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getLatestAttendances(10),
        ]);
    }
}
