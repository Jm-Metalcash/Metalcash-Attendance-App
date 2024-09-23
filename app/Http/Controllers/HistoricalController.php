<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoricalController extends Controller
{
    public function index()
    {
        // Récupérer tous les enregistrements de la table 'days' pour l'utilisateur connecté
        $days = Day::where('user_id', Auth::id())->orderBy('date', 'asc')->get();

        // Retourner la vue Inertia avec les données des jours
        return inertia('Historical', [
            'days' => $days,
        ]);
    }

    public function show($id)
{
    // Récupérer les jours de pointage pour l'utilisateur spécifié par l'ID
    $days = Day::where('user_id', $id)->orderBy('date', 'asc')->get();

    // Retourner la vue Inertia avec les données des jours pour cet utilisateur
    return inertia('Historical', [
        'days' => $days,
        'user' => User::find($id), // Renvoyer également les informations de l'utilisateur
    ]);
}

}
