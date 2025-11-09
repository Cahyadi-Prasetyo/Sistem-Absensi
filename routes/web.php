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
    Route::get('dashboard/live-stats', [App\Http\Controllers\DashboardController::class, 'liveStats'])->name('dashboard.live-stats');
    
    // Attendance routes
    Route::get('attendances', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('attendances', [App\Http\Controllers\AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('attendances/{attendance}', [App\Http\Controllers\AttendanceController::class, 'show'])->name('attendances.show');
    Route::put('attendances/{attendance}', [App\Http\Controllers\AttendanceController::class, 'update'])->name('attendances.update');
    Route::get('attendances/today/me', [App\Http\Controllers\AttendanceController::class, 'todayAttendance'])->name('attendances.today');
});

require __DIR__.'/settings.php';
