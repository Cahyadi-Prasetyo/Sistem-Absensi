<?php

namespace App\Http\Controllers;

use App\Repositories\AbsensiRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RiwayatController extends Controller
{
    public function __construct(
        private AbsensiRepositoryInterface $repository
    ) {}

    /**
     * Admin riwayat - show all attendances
     */
    public function adminIndex(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $attendances = $this->repository->getAllAttendances($startDate, $endDate, $search);

        return view('admin.riwayat', compact('attendances', 'search', 'startDate', 'endDate'));
    }

    /**
     * Get today's attendances as JSON for real-time updates
     */
    public function todayJson()
    {
        $today = Carbon::today();
        $attendances = $this->repository->getAllAttendances($today, $today);
        
        // Transform data for frontend
        $data = $attendances->map(function($attendance) {
            return [
                'id' => $attendance->id,
                'user_name' => $attendance->user->name,
                'date' => $attendance->date->format('d M Y'),
                'jam_masuk' => $attendance->jam_masuk ? $attendance->jam_masuk->format('H:i') : '-',
                'jam_pulang' => $attendance->jam_pulang ? $attendance->jam_pulang->format('H:i') : '-',
                'status' => $attendance->status,
                'status_badge_class' => $this->getStatusBadgeClass($attendance->status),
                'duration' => $attendance->getDurationFormatted(),
            ];
        });

        return response()->json($data);
    }

    private function getStatusBadgeClass($status)
    {
        return match($status) {
            'Hadir' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'Telat' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'Izin' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'Sakit' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            'Alpha' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
            default => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        };
    }

    /**
     * Karyawan riwayat - show only own attendances
     */
    public function karyawanIndex(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $attendances = $this->repository->getUserAttendances(Auth::id(), $startDate, $endDate);

        return view('karyawan.riwayat', compact('attendances', 'startDate', 'endDate'));
    }

    /**
     * Export attendances to CSV
     */
    public function export(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        // Get attendances based on user role
        if (Auth::user()->isAdmin()) {
            $attendances = $this->repository->getAllAttendances($startDate, $endDate, $search);
        } else {
            $attendances = $this->repository->getUserAttendances(Auth::id(), $startDate, $endDate);
        }

        // Generate CSV
        $filename = 'riwayat-absensi-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Tanggal', 'Nama', 'Jam Masuk', 'Jam Pulang', 'Durasi', 'Status']);
            
            // Data
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->date->format('d/m/Y'),
                    $attendance->user->name,
                    $attendance->jam_masuk?->format('H:i'),
                    $attendance->jam_pulang?->format('H:i'),
                    $attendance->getDurationFormatted(),
                    $attendance->status,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
