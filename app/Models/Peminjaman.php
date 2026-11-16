<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'anggota_id',
        'buku_id',
        'dipinjam_pada',
        'jatuh_tempo',
        'dikembalikan_pada',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dipinjam_pada' => 'datetime',
            'jatuh_tempo' => 'date',
            'dikembalikan_pada' => 'datetime',
        ];
    }

    /**
     * Get the member that owns the loan.
     */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    /**
     * Get the book associated with the loan.
     */
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }
}
