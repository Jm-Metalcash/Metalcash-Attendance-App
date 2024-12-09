<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifiez si l'utilisateur est authentifié
        if (Auth::check()) {
            $user = Auth::user();

            // Si le statut est désactivé (status === 1), déconnecter l'utilisateur
            if ($user->status === 1) {
                Auth::logout();

                // Invalider la session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Rediriger vers la page de connexion avec un message d'erreur
                return redirect()->route('login')->withErrors([
                    'email' => 'Votre compte est désactivé.',
                ]);
            }
        }

        return $next($request);
    }
}
