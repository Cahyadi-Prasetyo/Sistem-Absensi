<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard API endpoints (for real-time updates)
    Route::get('/api/dashboard/metrics', [DashboardController::class, 'metrics'])->name('api.dashboard.metrics');
    Route::get('/api/dashboard/latest', [DashboardController::class, 'latestAttendances'])->name('api.dashboard.latest');
    
    // Absensi routes (with rate limiting)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/absensi/masuk', [AttendanceController::class, 'clockIn'])->name('absensi.masuk');
        Route::post('/absensi/pulang', [AttendanceController::class, 'clockOut'])->name('absensi.pulang');
    });
    Route::get('/absensi/status', [AttendanceController::class, 'status'])->name('absensi.status');
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/riwayat', [\App\Http\Controllers\RiwayatController::class, 'adminIndex'])->name('admin.riwayat');
        
        // User Management
        Route::resource('admin/users', \App\Http\Controllers\Admin\UserManagementController::class)->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
        Route::post('/admin/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserManagementController::class, 'resetPassword'])->name('admin.users.reset-password');
    });
    
    Route::middleware('role:karyawan')->group(function () {
        Route::get('/karyawan/riwayat', [\App\Http\Controllers\RiwayatController::class, 'karyawanIndex'])->name('karyawan.riwayat');
    });
    
    // Export (both roles)
    Route::get('/riwayat/export', [\App\Http\Controllers\RiwayatController::class, 'export'])->name('riwayat.export');
    
    // Tentang Sistem (both roles)
    Route::get('/tentang-sistem', [\App\Http\Controllers\TentangSistemController::class, 'index'])->name('tentang-sistem');
});
