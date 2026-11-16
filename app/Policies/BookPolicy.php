<?php

namespace App\Policies;

use App\Models\Anggota;
use App\Models\Buku;

class BookPolicy
{
    /**
     * Determine whether the public/guest can view active books.
     */
    public function view(?Anggota $user, Buku $buku): bool
    {
        return is_null($buku->archived_at);
    }

    /**
     * Determine whether the user can view any books list in admin.
     */
    public function viewAny(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the admin can view the book (including archived).
     */
    public function viewAdmin(Anggota $user, Buku $buku): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can create books.
     */
    public function create(Anggota $user): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the book.
     */
    public function update(Anggota $user, Buku $buku): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete or archive the book.
     */
    public function delete(Anggota $user, Buku $buku): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can archive the book.
     */
    public function archive(Anggota $user, Buku $buku): bool
    {
        return $user->is_active && $user->role === 'admin';
    }
}
