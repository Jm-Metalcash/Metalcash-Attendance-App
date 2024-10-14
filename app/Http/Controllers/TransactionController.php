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
            'date' => 'required|date_format:Y-m-d', // Assurez-vous que le format de la date est correct
            'details' => 'nullable|string|max:1000',
        ]);
    
        // Formatage explicite de la date pour éviter tout problème de fuseau horaire ou de format
        $validatedData['date'] = Carbon::parse($validatedData['date'])->format('Y-m-d');
    
        // Mise à jour de la transaction
        $transaction->update($validatedData);
    
        return response()->json(['message' => 'Transaction updated successfully']);
    }
    

    // Ajout d'une nouvelle transaction
    public function store(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'type' => 'nullable|string|max:255',
            'typeofmetal' => 'required|string|max:255',
            'weight' => 'required|numeric',
            'details' => 'nullable|string|max:1000',
            'date' => 'nullable|date',
        ]);

        // Stocker la date en UTC
        $validatedData['date'] = isset($validatedData['date']) 
            ? Carbon::parse($validatedData['date'])->setTimezone('UTC') 
            : Carbon::now('UTC');

        $transaction = $client->transactions()->create($validatedData);

        // Retourner la date en UTC dans la réponse
        return response()->json([
            'id' => $transaction->id,
            'date' => $transaction->date->toISOString(), // ISO format pour la cohérence
        ]);
    }
}
