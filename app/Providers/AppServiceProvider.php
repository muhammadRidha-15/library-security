<?php

namespace App\Providers;

use App\Models\Buku;
use App\Models\Ulasan;
use App\Policies\BookPolicy;
use App\Policies\UlasanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Buku::class, BookPolicy::class);
        Gate::policy(Ulasan::class, UlasanPolicy::class);
    }
}
