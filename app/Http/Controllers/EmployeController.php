<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class EmployeController extends Controller
{
    public function index()
    {
        // Récupérer tous les utilisateurs avec leurs jours et leurs rôles associés
        $users = User::with('days', 'roles')->get();

        // Envoyer les utilisateurs avec leurs `days` et `roles` à la vue Inertia
        return Inertia::render('ListEmployes', [
            'users' => $users
        ]);
    }
}
