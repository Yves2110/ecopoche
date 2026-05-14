<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

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
        Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        // Login : 5 tentatives/minute par IP+email, 20/minute par IP seule
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->input('email').'|'.$request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        // Mutations profil : 10/minute par utilisateur
        RateLimiter::for('profil', function (Request $request) {
            return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
        });

        // Administration : 30/minute par utilisateur
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(30)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
