<?php

namespace App\Policies;

use App\Models\Anggota;
use App\Models\Peminjaman;
use Illuminate\Auth\Access\Response;

class PeminjamanPolicy
{
    /**
     * Determine whether the member can view any loan records.
     */
    public function viewAny(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'user';
    }

    /**
     * Determine whether the admin can view any loan records in the admin area.
     */
    public function viewAnyAdmin(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the member can view a specific loan record.
     * SEC-10: Deny as 404 (denyAsNotFound) to prevent leaking transaction existence.
     */
    public function view(Anggota $user, Peminjaman $peminjaman): Response
    {
        if ($user->role === 'admin' || $user->id === $peminjaman->anggota_id) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Data peminjaman tidak ditemukan.');
    }

    /**
     * Determine whether the user can create a borrowing record.
     */
    public function create(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'user';
    }

    /**
     * Determine whether an admin can create a loan record manually.
     */
    public function createAdmin(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether an admin can process a book return.
     */
    public function returnBook(Anggota $user, Peminjaman $peminjaman): bool
    {
        return $user->is_active && $user->role === 'admin';
    }
}
