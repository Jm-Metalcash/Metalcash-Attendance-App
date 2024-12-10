<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Authentifier les informations d'identification
        $request->authenticate();

        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // Vérifier si le compte est désactivé
        if ($user->status === 1) {
            // Déconnecter immédiatement l'utilisateur
            Auth::logout();

            // Invalider la session pour éviter qu'il reste connecté
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Retourner une erreur à la vue de connexion
            return back()->withErrors([
                'email' => 'Votre compte est désactivé.',
            ]);
        }

        // Si l'utilisateur est actif, régénérer la session
        $request->session()->regenerate();

        // Rediriger vers la page prévue
        return redirect()->intended(route('index', absolute: false));
    }



    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
