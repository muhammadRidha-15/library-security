<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
            'nama' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:anggota,email'],
            'password' => [
                'required',
                'string',
                'min:12',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    // SEC-02: Bcrypt truncates silently at 72 bytes. Enforce strict UTF-8 byte limit.
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Kata sandi tidak boleh melebihi 72 byte.');
                    }
                },
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
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.required' => 'Kata sandi akun wajib diisi.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
            'password.max' => 'Kata sandi maksimal 72 byte.',
            'telepon.regex' => 'Format nomor telepon harus berupa 8 hingga 15 digit angka (opsional awalan +).',
        ];
    }
}
