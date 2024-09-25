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
            'isShow' => true, // Indiquer que la page est une page de visualisation
        ]);
    }

    //Modifier les heures d'entrée et de sortie d'un jour
    public function updateDay(Request $request, $id)
    {
        // Valider les données envoyées
        $request->validate([
            'arrival' => 'required|date_format:H:i:s',
            'departure' => 'required|date_format:H:i:s|after:arrival',
        ]);

        // Trouver l'entrée à mettre à jour
        $day = Day::find($id);

        if (!$day) {
            return response()->json(['message' => 'Jour non trouvé'], 404);
        }

        // Mettre à jour les champs
        $day->arrival = $request->arrival;
        $day->departure = $request->departure;
        $day->save();

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
                'arrival' => 'required|date_format:H:i:s',
                'departure' => 'required|date_format:H:i:s|after:arrival',
                'total' => 'nullable|string',
                'break_start' => 'nullable|date_format:H:i:s',
                'break_end' => 'nullable|date_format:H:i:s|after:break_start',
            ]);
    
            // Créer un nouveau jour dans la base de données
            Day::create([
                'user_id' => $validatedData['user_id'],  // S'assurer que user_id est bien là
                'day' => $validatedData['day'],
                'date' => $validatedData['date'],
                'arrival' => $validatedData['arrival'],
                'departure' => $validatedData['departure'],
                'break_start' => $validatedData['break_start'],  // Ce champ peut être `null`
                'break_end' => $validatedData['break_end'],  // Ce champ peut être `null`
                'total' => $validatedData['total'] ?? '0h00',  // Ce champ peut être `null`
            ]);
    
            return response()->json(['message' => 'Jour ajouté avec succès'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    
    
    

}
