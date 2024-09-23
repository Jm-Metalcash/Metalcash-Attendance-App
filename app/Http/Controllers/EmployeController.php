<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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


    // Function pour ajouter un nouvel employé
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:Admin,Employé',
        ]);

        // Création du nouvel utilisateur
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Recherche du rôle par son nom
        $role = Role::where('name', $validated['role'])->firstOrFail();

        // Associer le rôle à l'utilisateur
        $user->roles()->attach($role);

        // Redirection ou réponse Inertia
        return redirect()->back()->with('success', 'Employé ajouté avec succès!');
    }
}
