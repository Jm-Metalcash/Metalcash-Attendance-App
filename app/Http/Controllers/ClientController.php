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
        $clients = Client::with(['notes'])->get();

        // Envoie les données à la vue Inertia
        return Inertia::render('ManagementCall', [
            'clients' => $clients,
        ]);
    }

    // Affiche les détails d'un client spécifique
    public function show($id)
    {
        // Récupérer le client avec ses notes, ses historiques de bordereaux et les informations associées
        $client = Client::with([
            'notes',
            'bordereauHistoriques.informations'
        ])->findOrFail($id);

        // Vérifier si la requête est une requête AJAX
        if (request()->ajax()) {
            return response()->json(['user' => $client]);
        }

        // Envoie les données à la vue Inertia
        return Inertia::render('ManagementCall', [
            'user' => $client,
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
            'entity' => 'nullable|string|max:255',
            'docType' => 'nullable|string|max:255',
            'docNumber' => 'nullable|string|max:255',
            'docExp' => 'nullable|date',
            'interest' => 'nullable|string|max:255',
            'referer' => 'nullable|string|max:255',
            'birthDate' => 'nullable|date',
        ]);

        $client = Client::findOrFail($id);
        $client->update($validatedData);

        return response()->json($client);
    }




    // Fonction pour créer un nouveau client
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'familyName' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255|unique:clients,phone',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'companyvat' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:10',
            'locality' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'blacklist' => 'nullable|boolean',
            'entity' => 'nullable|string|max:255',
            'docType' => 'nullable|string|max:255',
            'docNumber' => 'nullable|string|max:255',
            'docExp' => 'nullable|date',
            'interest' => 'nullable|string|max:255',
            'referer' => 'nullable|string|max:255',
            'birthDate' => 'nullable|date',
        ]);

        // Supprimer les champs avec des valeurs nulles
        $validatedData = array_filter($validatedData, fn($value) => !is_null($value));

        try {
            $client = Client::create($validatedData);

            return response()->json($client, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création du client: ' . $e->getMessage()], 500);
        }
    }
}
