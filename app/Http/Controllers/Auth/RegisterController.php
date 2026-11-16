<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Anggota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the member registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming member registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        Anggota::create([
            'nama' => $request->validated('nama'),
            'email' => strtolower(trim($request->validated('email'))),
            'password' => Hash::make($request->validated('password')),
            'role' => 'user',
            'is_active' => true,
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan masuk.');
    }
}
