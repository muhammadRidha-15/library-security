<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Perpustakaan Mini') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-full flex flex-col text-slate-800 bg-slate-50 font-sans antialiased">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="text-xl font-bold text-emerald-700 tracking-tight flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Perpustakaan Mini</span>
                </a>
            </div>
            <nav class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('buku.index') }}" class="text-slate-600 hover:text-emerald-600 transition">Katalog</a>
                @auth
                    @if (auth()->user()->role === 'user')
                        <a href="{{ route('peminjaman.index') }}" class="text-slate-600 hover:text-emerald-600 transition">Peminjaman Saya</a>
                    @elseif (auth()->user()->role === 'admin')
                        <a href="{{ url('/admin/buku') }}" class="text-slate-600 hover:text-emerald-600 transition">Panel Admin</a>
                    @endif
                    <a href="{{ route('profil.show') }}" class="text-slate-600 hover:text-emerald-600 transition">Profil</a>
                    <span class="text-slate-400 font-normal">|</span>
                    <span class="text-slate-600">{{ auth()->user()->nama }}</span>
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-slate-500 hover:text-red-600 transition">Keluar</button>
                        </form>
                    @endif
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-emerald-600 transition">Masuk</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition">Daftar</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="grow py-8 px-4 sm:px-6 lg:px-8">
        <div class="@yield('container-class', 'max-w-7xl') mx-auto">
            @if (session('success'))
                <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Perpustakaan Mini — Sistem Pengganti UTS Keamanan Sistem Komputer</p>
    </footer>
</body>
</html>
