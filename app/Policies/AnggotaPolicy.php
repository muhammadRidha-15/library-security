<?php

namespace App\Policies;

use App\Models\Anggota;

class AnggotaPolicy
{
    /**
     * Determine whether the admin can view the member list.
     */
    public function viewAny(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the admin can view a member's detail.
     */
    public function view(Anggota $user, Anggota $target): bool
    {
        return $user->is_active && ($user->role === 'admin' || $user->id === $target->id);
    }

    /**
     * Determine whether the admin can create a new member.
     */
    public function create(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the admin can update a member.
     */
    public function update(Anggota $user, Anggota $target): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the admin can deactivate the target member.
     * SEC-10 / FR-12: The last active admin cannot be deactivated.
     */
    public function deactivate(Anggota $user, Anggota $target): bool
    {
        if (! $user->is_active || $user->role !== 'admin') {
            return false;
        }

        // If target is an admin, ensure there is at least one other active admin
        if ($target->role === 'admin') {
            $activeAdminCount = Anggota::where('role', 'admin')
                ->where('is_active', true)
                ->count();

            if ($activeAdminCount <= 1) {
                return false;
            }
        }

        return true;
    }
}
