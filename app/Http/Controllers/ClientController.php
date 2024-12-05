<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Prospect;
use Illuminate\Support\Facades\Auth;
use App\Models\ClientsProspectsUpdate;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    //Affiche les details d'un client
    public function show($id)
    {
        if (!$id) {
            return response()->json(['error' => 'Client ID is missing'], 400);
        }

        $client = Client::with(['notes'])->findOrFail($id);



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
       // Validation des données d'entrée
       $validatedData = $request->validate([
           'firstName' => 'nullable|string|max:255',
           'familyName' => 'nullable|string|max:255',
           'locality' => 'nullable|string|max:255',
           'phone' => 'nullable|string|max:255',
           'country' => 'nullable|string|max:255',
       ]);

       // Mise à jour des informations du client
       $client = Client::findOrFail($id);
       $client->update($validatedData);

       // Enregistrer la mise à jour dans la table clients_prospects_update
       ClientsProspectsUpdate::create([
           'updatable_type' => Client::class,
           'updatable_id' => $client->id,
           'user_id' => Auth::id(),
           'action' => 'updated',
       ]);

       return response()->json($client);
   }
}
