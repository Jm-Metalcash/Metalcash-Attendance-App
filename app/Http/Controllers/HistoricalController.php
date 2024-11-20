<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\User;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoricalController extends Controller
{
    public function index()
    {
        // Récupérer tous les enregistrements de la table 'days' pour l'utilisateur connecté, avec les time_entries associés
        $days = Day::with('timeEntries')
            ->where('user_id', Auth::id())
            ->orderBy('date', 'asc')
            ->get();

        // Retourner la vue Inertia avec les données des jours
        return inertia('Historical', [
            'days' => $days,
        ]);
    }

    public function show($id)
    {
        // Récupérer les jours de pointage pour l'utilisateur spécifié par l'ID, avec les time_entries associés
        $days = Day::with('timeEntries')
            ->where('user_id', $id)
            ->orderBy('date', 'asc')
            ->get();

        // Retourner la vue Inertia avec les données des jours pour cet utilisateur
        return inertia('Historical', [
            'days' => $days,
            'user' => User::find($id), // Renvoyer également les informations de l'utilisateur
            'isShow' => true, // Indiquer que la page est une page de visualisation
        ]);
    }

    // Modifier les heures d'arrivée et de départ d'un jour
    public function updateDay(Request $request, $id)
    {
        // Valider les données envoyées
        $validatedData = $request->validate([
            'arrivals' => 'nullable|array',
            'arrivals.*' => 'date_format:H:i',
            'departures' => 'nullable|array',
            'departures.*' => 'date_format:H:i',
        ]);

        // Trouver le jour à mettre à jour
        $day = Day::find($id);

        if (!$day) {
            return response()->json(['message' => 'Jour non trouvé'], 404);
        }

        // Supprimer les time_entries existants pour ce jour
        TimeEntry::where('day_id', $day->id)->delete();

        // Créer les nouvelles entrées de temps
        $userId = $day->user_id;

        if (!empty($validatedData['arrivals'])) {
            foreach ($validatedData['arrivals'] as $time) {
                TimeEntry::create([
                    'user_id' => $userId,
                    'day_id' => $day->id,
                    'type' => 'arrival',
                    'time' => $time,
                ]);
            }
        }

        if (!empty($validatedData['departures'])) {
            foreach ($validatedData['departures'] as $time) {
                TimeEntry::create([
                    'user_id' => $userId,
                    'day_id' => $day->id,
                    'type' => 'departure',
                    'time' => $time,
                ]);
            }
        }

        // Recalculer le total pour ce jour
        $this->recalculateTotal($day);

        return response()->json(['message' => 'Jour mis à jour avec succès']);
    }

    // Ajoute un nouveau jour
    public function addDay(Request $request)
    {
        try {
            // Valider les données reçues
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'day' => 'required|string',
                'date' => 'required|date',
                'arrivals' => 'nullable|array',
                'arrivals.*' => 'date_format:H:i',
                'departures' => 'nullable|array',
                'departures.*' => 'date_format:H:i',
            ]);

            // Créer un nouveau jour dans la base de données
            $day = Day::create([
                'user_id' => $validatedData['user_id'],
                'day' => $validatedData['day'],
                'date' => $validatedData['date'],
                'total' => '00:00',
            ]);

            // Créer les entrées de temps associées
            $userId = $validatedData['user_id'];

            if (!empty($validatedData['arrivals'])) {
                foreach ($validatedData['arrivals'] as $time) {
                    TimeEntry::create([
                        'user_id' => $userId,
                        'day_id' => $day->id,
                        'type' => 'arrival',
                        'time' => $time,
                    ]);
                }
            }

            if (!empty($validatedData['departures'])) {
                foreach ($validatedData['departures'] as $time) {
                    TimeEntry::create([
                        'user_id' => $userId,
                        'day_id' => $day->id,
                        'type' => 'departure',
                        'time' => $time,
                    ]);
                }
            }

            // Calculer le total pour ce jour
            $this->recalculateTotal($day);

            return response()->json(['message' => 'Jour ajouté avec succès'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Fonction pour recalculer le total des heures d'un jour
    private function recalculateTotal(Day $day)
    {
        $timeEntries = $day->timeEntries;

        $arrivals = $timeEntries->where('type', 'arrival')->pluck('time')->toArray();
        $departures = $timeEntries->where('type', 'departure')->pluck('time')->toArray();

        $totalMinutes = 0;

        for ($i = 0; $i < min(count($arrivals), count($departures)); $i++) {
            $arrivalTime = \Carbon\Carbon::createFromFormat('H:i:s', $arrivals[$i]);
            $departureTime = \Carbon\Carbon::createFromFormat('H:i:s', $departures[$i]);

            // Si l'heure de départ est avant l'heure d'arrivée, ajouter un jour à la sortie
            if ($departureTime->lt($arrivalTime)) {
                $departureTime->addDay();
            }

            // Ajouter la différence en minutes
            $totalMinutes += $departureTime->diffInMinutes($arrivalTime);
        }

        // Convertir les minutes totales en format HH:mm
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $formattedTotal = sprintf('%02d:%02d', $hours, $minutes);

        // Mettre à jour le total dans la table `days`
        $day->update(['total' => $formattedTotal]);
    }
}
