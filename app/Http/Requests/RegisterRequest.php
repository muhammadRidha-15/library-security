<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation (normalize email).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email') && is_string($this->email)) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:anggota,email'],
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail): void {
                    // SEC-02: Bcrypt truncates silently at 72 bytes. Enforce strict UTF-8 byte limit.
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Password tidak boleh melebihi 72 byte.');
                    }
                },
            ],
        ];
    }
}
