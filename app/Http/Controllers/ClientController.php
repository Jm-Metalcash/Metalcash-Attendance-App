<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\HistoricalTransaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Affiche la liste des clients avec leurs transactions et notes
    public function index()
    {
        // Récupère les clients avec leurs transactions et notes
        $clients = Client::with(['transactions', 'notes'])->get();

        // Envoie les données à la vue Inertia
        return Inertia::render('ManagementCall', [
            'clients' => $clients,
        ]);
    }

    // Modifie les données d'un client
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'familyName' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'blacklist' => 'nullable|boolean',
        ]);

        $client = Client::findOrFail($id);
        $client->update($validatedData);

        // Recharger les données du client depuis la base de données
        $client->refresh();

        return response()->json($client);
    }



    // Fonction pour créer un nouveau client
    public function store(Request $request)
    {
        // Validation des données avec des messages d'erreur personnalisés
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'familyName' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255|unique:clients,phone', // Le numéro de téléphone est requis et doit être unique
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'companyvat' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:10',
            'locality' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'blacklist' => 'nullable|boolean',
        ], [
            // Messages d'erreur personnalisés
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Le numéro indiqué est déjà existant.',
            'email.email' => 'Le format ne correspond pas à une adresse e-mail valide.',
        ]);
    
        // Supprimer les champs avec des valeurs nulles
        $validatedData = array_filter($validatedData, fn($value) => !is_null($value));
    
        try {
            // Si la validation est réussie, on crée le client
            $client = Client::create($validatedData);
    
            // Renvoyer une réponse JSON avec le client créé
            return response()->json($client, 201);
        } catch (\Exception $e) {
            // Capturer les erreurs et renvoyer une réponse appropriée
            return response()->json(['error' => 'Erreur lors de la création du client: ' . $e->getMessage()], 500);
        }
    }
    
}