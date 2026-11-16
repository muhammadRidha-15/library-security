<?php

namespace App\Http\Requests\Admin;

use App\Models\Anggota;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
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
        /** @var Anggota|null $target */
        $target = $this->route('anggota');

        return [
            'nama' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('anggota', 'email')->ignore($target?->id),
            ],
            'telepon' => ['nullable', 'string', 'regex:/^(\+?[0-9]{8,15})?$/'],
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
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'telepon.regex' => 'Format nomor telepon harus berupa 8 hingga 15 digit angka (opsional awalan +).',
        ];
    }
}
