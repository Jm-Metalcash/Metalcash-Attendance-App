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
            'role' => 'required|string|in:Admin,Employé,Informatique,Comptabilité,Ouvrier',
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


    // Function pour modifier un employé
    public function edit($id)
    {
        // Récupérer l'utilisateur avec ses rôles associés
        $user = User::with('roles')->findOrFail($id);

        // Récupérer tous les rôles disponibles
        $roles = Role::all();

        // Renvoyer l'utilisateur et la liste des rôles comme props
        return Inertia::render('Profile-Employes/Edit', [
            'user' => $user,  // Envoyer l'utilisateur comme prop
            'roles' => $roles,  // Envoyer tous les rôles disponibles comme prop explicite
        ]);
    }





    // Mettre à jour les informations du profil
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Valider les informations du formulaire, incluant le rôle
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,id', // Valider que le rôle existe
        ]);

        // Mettre à jour le nom et l'email de l'utilisateur
        $user->update($request->only('name', 'email'));

        // Mettre à jour le rôle dans la table pivot role_user
        $user->roles()->sync([$request->role]); // Mettre à jour le rôle avec l'ID fourni

        // Rediriger avec un message de succès
        return redirect()->back()->with('success', 'Profil et rôle mis à jour avec succès.');
    }


    // Mettre à jour le mot de passe de l'employé
    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Mot de passe mis à jour avec succès.');
    }



    //Désactive un compte
    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->status = 1; // Désactiver le compte
        $user->save();

        return back()->with('success', 'Le compte a été désactivé avec succès.');
    }



    // Réactiver un compte
    public function reactivate($id)
    {
        $user = User::findOrFail($id);
        $user->status = 0; // Réactiver le compte
        $user->save();

        return back()->with('success', 'Le compte a été réactivé avec succès.');
    }
}
