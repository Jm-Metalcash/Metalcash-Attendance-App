<?php

namespace App\Http\Controllers;

use App\Models\Day;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DayController extends Controller
{
    public function store(Request $request)
    {
        // Valider les données reçues
        $validated = $request->validate([
            'day' => 'required|string',
            'date' => 'required|date',
            'arrival' => 'nullable|date_format:H:i',
            'departure' => 'nullable|date_format:H:i',
            'total' => 'nullable|string',
        ]);

        // Créer ou mettre à jour l'enregistrement dans la table `days`
        Day::updateOrCreate(
            ['user_id' => Auth::id(), 'date' => $validated['date']],
            [
                'day' => $validated['day'],
                'arrival' => $validated['arrival'],
                'departure' => $validated['departure'],
                'total' => $validated['total']
            ]
        );

        return response()->json(['message' => 'Données enregistrées avec succès !'], 200);
        
    }

    public function index()
    {
        // Récupérer les jours de la semaine pour l'utilisateur connecté
        $days = Day::where('user_id', Auth::id())->get();

        return response()->json($days);
    }
}
