<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\User;

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
        Vite::prefetch(concurrency: 3);
        Carbon::setLocale('fr');

        // Partage des messages flash
        Inertia::share('flash', function () {
            return [
                'success' => session('success'),
            ];
        });

        // Partage global des utilisateurs et de l'utilisateur connecté
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => Auth::check() ? Auth::user() : null,
                ];
            },
            'users' => function () {
                // Charger tous les utilisateurs avec leurs rôles
                return Auth::check()
                    ? User::with('roles:id,name')->get(['id', 'name', 'email'])
                    : [];
            },
        ]);
    }
}
