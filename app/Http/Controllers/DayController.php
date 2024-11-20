<?php

namespace App\Http\Controllers;

use App\Models\Day;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'total' => 'nullable|string',
        ]);

        // Créer ou mettre à jour l'enregistrement dans la table `days`
        Day::updateOrCreate(
            ['user_id' => Auth::id(), 'date' => $validated['date']],
            [
                'day' => $validated['day'],
                'arrival' => $validated['arrival'],
                'departure' => $validated['departure'],
                'break_start' => $validated['break_start'],
                'break_end' => $validated['break_end'],
                'total' => $validated['total'],
            ]
        );

        return response()->json(['message' => 'Données enregistrées avec succès !'], 200);
    }

    public function index()
    {
        // Récupérer les jours avec leurs entrées de temps pour l'utilisateur connecté
        $days = Day::with(['timeEntries' => function ($query) {
            $query->orderBy('time', 'asc'); // Trier les entrées de temps par heure
        }])
            ->where('user_id', Auth::id())
            ->orderBy('date', 'asc')
            ->get();

        // Formater les dates pour le frontend
        $days = $days->map(function ($day) {
            $day->date = Carbon::parse($day->date)->format('d/m/Y'); // Format "jour/mois/année"
            return $day;
        });

        return response()->json($days);
    }


    public function updateTotal(Request $request)
{
    try {
        // Valider les données entrantes
        $validated = $request->validate([
            'day_id' => 'required|exists:days,id',
            'total' => 'required|regex:/^\d{2}:\d{2}$/', // Format HH:mm
        ]);

        // Mettre à jour le total
        $day = Day::findOrFail($validated['day_id']);
        $day->update(['total' => $validated['total']]);

        return response()->json([
            'message' => 'Total mis à jour avec succès',
            'day' => $day,
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
