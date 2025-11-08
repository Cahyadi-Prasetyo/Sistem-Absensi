<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Attendance routes
    Route::post('attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/check-out', [App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('attendance/today', [App\Http\Controllers\AttendanceController::class, 'today'])->name('attendance.today');
    Route::get('attendance/history', [App\Http\Controllers\AttendanceController::class, 'history'])->name('attendance.history');
    
    // Server node routes
    Route::get('server-nodes', [App\Http\Controllers\ServerNodeController::class, 'index'])->name('server-nodes.index');
    Route::post('server-nodes/heartbeat', [App\Http\Controllers\ServerNodeController::class, 'heartbeat'])->name('server-nodes.heartbeat');
    Route::patch('server-nodes/{id}/status', [App\Http\Controllers\ServerNodeController::class, 'updateStatus'])->name('server-nodes.update-status');
    
    // Real-time data endpoint
    Route::get('dashboard/realtime-attendances', [App\Http\Controllers\DashboardController::class, 'realtimeAttendances'])->name('dashboard.realtime');
});

require __DIR__.'/settings.php';
