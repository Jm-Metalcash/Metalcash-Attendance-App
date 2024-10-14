<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\HistoricalTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    // Mise à jour d'une transaction
    public function update(Request $request, Client $client, HistoricalTransaction $transaction)
    {
        // Vérifier que la transaction appartient bien au client
        if ($transaction->client_id !== $client->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validation des données
        $validatedData = $request->validate([
            'type' => 'nullable|string|max:255',
            'typeofmetal' => 'required|string|max:255',
            'weight' => 'required|numeric',
            'date' => 'required|date',
            'details' => 'nullable|string|max:1000',
        ]);

        // Mise à jour de la transaction
        $transaction->update($validatedData);

        return response()->json(['message' => 'Transaction updated successfully']);
    }

    // Ajout d'une nouvelle transaction
    public function store(Request $request, Client $client)
    {
        // Validation des données
        $validatedData = $request->validate([
            'type' => 'nullable|string|max:255',
            'typeofmetal' => 'required|string|max:255',
            'weight' => 'required|numeric',
            'details' => 'nullable|string|max:1000',
        ]);
    
        // Utiliser la date et l'heure actuelles pour l'encodement
        $validatedData['date'] = Carbon::now();
    
        // Créer la transaction et l'associer au client
        $transaction = $client->transactions()->create($validatedData);
    
        return response()->json(['id' => $transaction->id]);
    }
    
    
}
