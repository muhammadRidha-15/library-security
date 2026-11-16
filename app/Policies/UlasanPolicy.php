<?php

namespace App\Policies;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Ulasan;

class UlasanPolicy
{
    /**
     * Determine whether the member can create a review for the given book.
     */
    public function create(Anggota $user, Buku $buku): bool
    {
        return $user->is_active && is_null($buku->archived_at);
    }

    /**
     * Determine whether the user can delete the review (admin only).
     */
    public function delete(Anggota $user, Ulasan $ulasan): bool
    {
        return $user->role === 'admin';
    }
}
