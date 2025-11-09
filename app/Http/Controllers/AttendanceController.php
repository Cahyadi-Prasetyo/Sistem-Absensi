<?php

namespace App\Http\Controllers;

use App\Events\AttendanceCreated;
use App\Events\AttendanceUpdated;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = Attendance::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->date, fn($q) => $q->whereDate('check_in', $request->date))
            ->latest('check_in')
            ->paginate(20);

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'check_in_location' => 'nullable|array',
            'check_in_location.lat' => 'required_with:check_in_location|numeric',
            'check_in_location.lng' => 'required_with:check_in_location|numeric',
            'check_in_photo' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if already checked in today
        if ($request->user()->hasCheckedInToday()) {
            return back()->withErrors(['message' => 'You have already checked in today.']);
        }

        $settings = AttendanceSetting::getSettings();
        $checkInTime = now();
        
        // Determine status (late or present)
        $workStart = \Carbon\Carbon::parse($settings->work_start_time);
        $minutesLate = $checkInTime->diffInMinutes($workStart, false);
        $status = $minutesLate > $settings->late_tolerance ? 'late' : 'present';

        $attendance = Attendance::create([
            'user_id' => $request->user()->id,
            'check_in' => $checkInTime,
            'check_in_location' => $validated['check_in_location'] ?? null,
            'check_in_photo' => $validated['check_in_photo'] ?? null,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
            'node_id' => env('NODE_ID', 'unknown'),
        ]);

        // Broadcast event
        broadcast(new AttendanceCreated($attendance))->toOthers();

        return back()->with('success', 'Check-in successful!');
    }

    public function update(Request $request, Attendance $attendance)
    {
        // Only allow updating own attendance
        if ($attendance->user_id !== $request->user()->id) {
            abort(403);
        }

        // Check if already checked out
        if ($attendance->check_out) {
            return back()->withErrors(['message' => 'You have already checked out.']);
        }

        $validated = $request->validate([
            'check_out_location' => 'nullable|array',
            'check_out_location.lat' => 'required_with:check_out_location|numeric',
            'check_out_location.lng' => 'required_with:check_out_location|numeric',
            'check_out_photo' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance->update([
            'check_out' => now(),
            'check_out_location' => $validated['check_out_location'] ?? null,
            'check_out_photo' => $validated['check_out_photo'] ?? null,
            'notes' => $validated['notes'] ?? $attendance->notes,
        ]);

        // Broadcast event
        broadcast(new AttendanceUpdated($attendance))->toOthers();

        return back()->with('success', 'Check-out successful!');
    }

    public function show(Attendance $attendance)
    {
        $attendance->load('user');
        
        return Inertia::render('Attendance/Show', [
            'attendance' => $attendance,
        ]);
    }

    public function todayAttendance(Request $request)
    {
        $attendance = $request->user()->todayAttendance();

        return response()->json([
            'attendance' => $attendance,
            'has_checked_in' => $attendance !== null,
            'has_checked_out' => $attendance && $attendance->check_out !== null,
        ]);
    }
}
