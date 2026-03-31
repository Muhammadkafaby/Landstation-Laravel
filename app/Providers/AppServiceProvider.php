<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL; // 🔥 TAMBAH INI
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 🔥 WAJIB: paksa semua jadi HTTPS
        URL::forceScheme('http');

        Vite::prefetch(concurrency: 3);

        RateLimiter::for('public-bookings', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}