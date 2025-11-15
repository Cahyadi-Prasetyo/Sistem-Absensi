<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine if user can view any attendances
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all attendances
        return $user->isAdmin();
    }

    /**
     * Determine if user can view specific attendance
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Admin can view any attendance
        if ($user->isAdmin()) {
            return true;
        }

        // Karyawan can only view their own attendance
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine if user can create attendance
     */
    public function create(User $user): bool
    {
        // Only karyawan can create attendance
        return $user->isKaryawan();
    }

    /**
     * Determine if user can update attendance
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Only karyawan can update their own attendance
        return $user->isKaryawan() && $user->id === $attendance->user_id;
    }

    /**
     * Determine if user can delete attendance
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // Only admin can delete attendance
        return $user->isAdmin();
    }

    /**
     * Determine if user can export attendances
     */
    public function export(User $user): bool
    {
        // Both admin and karyawan can export
        // But karyawan can only export their own data (handled in controller)
        return true;
    }
}
