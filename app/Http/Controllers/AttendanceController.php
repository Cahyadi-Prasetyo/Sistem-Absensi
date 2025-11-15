<?php

namespace App\Http\Controllers;

use App\Events\AbsensiCreated;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AttendanceController extends Controller
{
    public function __construct(
        private AbsensiService $absensiService
    ) {}

    /**
     * Clock in (absen masuk)
     */
    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            $attendance = $this->absensiService->clockIn(
                Auth::id(),
                $validated['latitude'],
                $validated['longitude']
            );

            // Broadcast event
            event(new AbsensiCreated($attendance));

            return response()->json([
                'success' => true,
                'message' => 'Absensi masuk berhasil dicatat',
                'data' => $attendance,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clock out (absen pulang)
     */
    public function clockOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            $attendance = $this->absensiService->clockOut(
                Auth::id(),
                $validated['latitude'],
                $validated['longitude']
            );

            // Broadcast event
            event(new AbsensiCreated($attendance));

            return response()->json([
                'success' => true,
                'message' => 'Absensi pulang berhasil dicatat',
                'data' => $attendance,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get today's attendance status for current user
     */
    public function status()
    {
        $attendance = $this->absensiService->getTodayAttendance(Auth::id());

        return response()->json([
            'success' => true,
            'data' => [
                'has_clock_in' => $attendance !== null,
                'has_clock_out' => $attendance?->jam_pulang !== null,
                'attendance' => $attendance,
            ],
        ]);
    }
}
