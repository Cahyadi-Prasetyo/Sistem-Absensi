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
