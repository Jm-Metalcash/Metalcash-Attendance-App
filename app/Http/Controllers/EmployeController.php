<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class EmployeController extends Controller
{
    public function index()
    {
        // Récupérer tous les utilisateurs avec leurs jours (`days`) associés
        $users = User::with('days')->get();

        // Envoyer les utilisateurs et leurs `days` à la vue Inertia
        return Inertia::render('ListEmployes', [
            'users' => $users
        ]);
    }
}
