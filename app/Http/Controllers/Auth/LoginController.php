<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $throttleKey = 'login:'.$email.'|'.$request->ip();

        if ($email !== '' && RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            abort(429, 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam '.$seconds.' detik.');
        }

        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $email, 'password' => $request->input('password'), 'is_active' => true])) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // VULNERABLE BRANCH: Session fixation vulnerability - session ID not regenerated upon login (TKT-25 / SEC-10)
        // Main branch enforces: $request->session()->regenerate();
        // $request->session()->regenerate();

        if ($request->user()->role === 'admin') {
            return redirect()->intended('/admin/buku');
        }

        return redirect()->intended('/buku');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}
