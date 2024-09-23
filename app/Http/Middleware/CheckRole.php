<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Récupérer l'utilisateur avec ses rôles
        $user = User::with('roles')->find(Auth::id());

        // Vérifier si l'utilisateur a l'un des rôles autorisés
        if (!$user->roles->whereIn('name', $roles)->count()) {
            return abort(403, 'Accès interdit');
        }

        return $next($request);
    }
}
