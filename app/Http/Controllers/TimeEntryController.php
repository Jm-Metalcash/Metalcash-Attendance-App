<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeEntry;
use App\Models\Day;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Forcer la mise à jour du fuseau horaire
            date_default_timezone_set('Europe/Paris');
            
            // Validation des données de base
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'day_id' => 'nullable|exists:days,id',
                'type' => 'required|in:arrival,departure',
            ]);

            // Utiliser l'heure du serveur
            $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $validated['time'] = $now->format('H:i');

            // Vérifier si `day_id` n'est pas fourni
            if (empty($validated['day_id'])) {
                // Créer ou récupérer le jour
                $day = Day::updateOrCreate(
                    [
                        'user_id' => $validated['user_id'],
                        'date' => Carbon::now()->format('Y-m-d'),
                    ],
                    [
                        'day' => Carbon::now()->translatedFormat('l'), // Jour en français
                    ]
                );

                // Associer le `day_id` généré
                $validated['day_id'] = $day->id;
            } else {
                // Récupérer le jour existant
                $day = Day::findOrFail($validated['day_id']);
            }

            // Créer l'entrée de temps associée
            $timeEntry = TimeEntry::create($validated);

            // Mettre à jour le statut du jour
            $day->updateStatus();

            return response()->json([
                'message' => 'Entrée de temps enregistrée avec succès',
                'time_entry' => $timeEntry,
                'day_id' => $validated['day_id'],
            ]);
        } catch (\Exception $e) {
            // Retourner une réponse d'erreur
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Nouvelle méthode pour obtenir l'heure du serveur
    public function getCurrentTime()
    {
        // Forcer la mise à jour du fuseau horaire
        date_default_timezone_set('Europe/Paris');
        
        // Récupérer l'heure du serveur directement via PHP
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        
        return response()->json([
            'time' => $now->format('H:i'),
            'date' => $now->format('d/m/Y'),
            'day' => Carbon::now()->translatedFormat('l')
        ]);
    }
}
