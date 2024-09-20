<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Envoie les rôles de l'utilisateur connecté à la vue Dashboard.
     */
    public function index()
    {
        // Récupérer les rôles de l'utilisateur connecté
        $user = Auth::user();
        $roles = $user->roles->pluck('name'); // Extraire uniquement les noms des rôles

        // Passer les rôles à la vue Dashboard
        return Inertia::render('Dashboard', [
            'roles' => $roles,
        ]);
    }
}
