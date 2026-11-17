<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'penulis',
        'deskripsi',
        'foto',
        'stok_total',
        'stok_tersedia',
        'archived_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stok_total' => 'integer',
            'stok_tersedia' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Get the loans for the book.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'buku_id');
    }

    /**
     * Get the reviews for the book.
     */
    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'buku_id');
    }

    /**
     * Get the public URL for book cover photo, fallback to default photo.
     */
    public function getFotoUrlAttribute(): string
    {
        if (! empty($this->foto)) {
            if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
                return $this->foto;
            }

            if (str_starts_with($this->foto, 'images/')) {
                return asset($this->foto);
            }

            if (Storage::disk('public')->exists($this->foto)) {
                return asset('storage/' . $this->foto);
            }
        }

        return asset('images/default-book-cover.svg');
    }
}
