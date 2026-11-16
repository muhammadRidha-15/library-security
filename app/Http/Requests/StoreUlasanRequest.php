<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUlasanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (bool) auth()->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'isi' => ['required', 'string', 'min:1', 'max:2000'],
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
            'isi.required' => 'Isi ulasan wajib diisi.',
            'isi.min' => 'Isi ulasan minimal 1 karakter.',
            'isi.max' => 'Isi ulasan maksimal 2.000 karakter.',
        ];
    }
}
