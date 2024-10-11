<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    //Affiche la liste des clients avec leurs transactions et notes
    public function index()
    {
        // Récupère les clients avec leurs transactions et notes
        $clients = Client::with(['transactions', 'notes'])->get();

        // Envoie les données à la vue Inertia
        return Inertia::render('ManagementCall', [
            'clients' => $clients,
        ]);
    }



    //Modifie les données d'un client
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'firstName' => 'string|max:255',
            'familyName' => 'string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
        ]);

        $client = Client::findOrFail($id);
        $client->update($validatedData);

        // Recharger les données du client depuis la base de données
        $client->refresh();

        return response()->json($client);
    }
}
