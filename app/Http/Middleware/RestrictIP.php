<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictIP
{
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur n'est pas authentifié, ne pas appliquer la restriction d'IP
        if (!Auth::check()) {
            return $next($request);
        }

        // Si l'utilisateur est sur la page de login, ne pas appliquer la restriction d'IP
        if ($request->is('login') || $request->is('/')) {
            return $next($request);
        }

        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // Si l'utilisateur a le rôle "Admin", bypass la restriction d'IP
        if ($user->roles->contains(function ($role) {
            return in_array($role->name, ['Admin', 'Informatique']);
        })) {
            return $next($request);
        }

        // Liste des adresses IP autorisées
        $allowedIps = [
            '127.0.0.1', // IP LOCALHOST
            '80.201.194.70', // IPV4 METALCASH
            '91.180.115.83', // IP HOME WORK
        ];

        // Récupérer l'IP réelle de l'utilisateur
        $clientIp = $request->header('X-Forwarded-For') ?? $request->ip();

        // Vérifier si l'IP de l'utilisateur est dans la liste des IP autorisées
        if (!in_array($clientIp, $allowedIps)) {
            return abort(403, 'Accès interdit');
        }

        return $next($request);
    }
}
