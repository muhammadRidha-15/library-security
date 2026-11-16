<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Anggota extends Authenticatable
{
    use HasFactory;

    protected $table = 'anggota';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'is_active',
        'telepon_enc',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
            'telepon_enc' => 'array',
        ];
    }

    /**
     * Get the loans associated with the member.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'anggota_id');
    }

    /**
     * Get the reviews written by the member.
     */
    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'anggota_id');
    }
}
