<?php

namespace App\Http\Controllers;

use App\Models\RecentView;
use App\Models\NoteClient;
use App\Models\Client;
use App\Models\Prospect;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    //Affiche les details d'un client
    public function showClient($id)
{
    if (!$id) {
        return response()->json(['error' => 'Client ID is missing'], 400);
    }

    $client = Client::with(['notes'])->findOrFail($id);

    // Log the client view
    try {
        RecentView::updateOrCreate(
            ['user_id' => Auth::id(), 'client_id' => $id],
            ['created_at' => now(), 'prospect_id' => null]
        );
    } catch (\Exception $e) {
        return Inertia::render('Error', ['message' => 'Failed to log view: ' . $e->getMessage()]);
    }

    // Ajoute l'attribut has_warning
    $client->append('has_warning');

    // Ajouter le champ 'type' au client
    $client = array_merge($client->toArray(), ['type' => 'client']);

    return Inertia::render('ManagementCall', [
        'selectedClient' => $client,
        'prospects' => Prospect::all(),
        'clients' => Client::all(),
    ]);
}



    //Fonction pour update un client
    public function update(Request $request, $id)
    {
        // Valider les champs autorisés
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'familyName' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        // Récupérer le client
        $client = Client::findOrFail($id);

        // Mettre à jour les champs
        $client->update($validatedData);

        // Retourner les données mises à jour
        return response()->json($client, 200);
    }
}
