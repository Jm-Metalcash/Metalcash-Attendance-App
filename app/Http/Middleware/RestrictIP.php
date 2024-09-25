<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictIP
{
    public function handle(Request $request, Closure $next)
    {
        // Liste des adresses IP autorisées
        $allowedIps = [
            '127.0.0.1', //IP LOCALHOST
            '2a02:a03f:ad7d:6a00:bd0a:ae5b:ecc5:864', //IPV6 TEMPORAIRE
            '109.137.81.79' //IPV4
        ];

        // Récupérer l'IP réelle de l'utilisateur (Ngrok peut la transmettre via X-Forwarded-For)
        $clientIp = $request->header('X-Forwarded-For') ?? $request->ip();

        // Vérifier si l'IP de l'utilisateur est dans la liste des IP autorisées
        if (!in_array($clientIp, $allowedIps)) {
            // Si l'IP n'est pas autorisée, retourne une réponse d'accès interdit
            return abort(403, 'Accès interdit');
        }

        return $next($request);
    }
}
