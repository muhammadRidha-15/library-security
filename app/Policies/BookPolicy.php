<?php

namespace App\Policies;

use App\Models\Anggota;
use App\Models\Buku;

class BookPolicy
{
    /**
     * Determine whether the user can view the book.
     */
    public function view(?Anggota $user, Buku $buku): bool
    {
        return is_null($buku->archived_at);
    }

    /**
     * Determine whether the admin can view the book.
     */
    public function viewAdmin(Anggota $user, Buku $buku): bool
    {
        return $user->role === 'admin';
    }
}
