<?php

namespace App\Http\Requests\Admin;

use App\Models\Buku;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin' && (bool) auth()->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'min:1', 'max:255'],
            'penulis' => ['required', 'string', 'min:1', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
            'stok_total' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul buku wajib diisi.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'foto.image' => 'File sampul buku harus berupa gambar valid.',
            'foto.mimes' => 'Format foto sampul yang diizinkan hanya JPEG, PNG, JPG, dan WEBP.',
            'foto.max' => 'Ukuran foto sampul buku maksimal 2 MB.',
            'stok_total.required' => 'Jumlah stok total buku wajib diisi.',
            'stok_total.min' => 'Stok buku minimal 1 eksemplar.',
        ];
    }

    /**
     * Configure the validator instance with custom invariant checks.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Buku|null $buku */
            $buku = $this->route('buku');
            $stokTotal = (int) $this->input('stok_total');

            if ($buku) {
                $borrowedCount = $buku->peminjaman()->where('status', 'dipinjam')->count();
                if ($stokTotal < $borrowedCount) {
                    $validator->errors()->add(
                        'stok_total',
                        "Jumlah stok total ({$stokTotal}) tidak boleh lebih kecil dari eksemplar yang sedang dipinjam ({$borrowedCount} eksemplar)."
                    );
                }
            }
        });
    }
}
