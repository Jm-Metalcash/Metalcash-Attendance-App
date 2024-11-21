<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\HistoricalTransaction;
use App\Models\RecentView;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Affiche la liste des clients avec leurs transactions et notes
    public function index($user = null)
    {
        // Récupérer les clients, etc.
        $clients = Client::all();

        return Inertia::render('ManagementCall', [
            'clients' => $clients,
            'currentUser' => Auth::user(),
            'selectedUserId' => $user, // Passer l'ID de l'utilisateur sélectionné
        ]);
    }


    // Affiche les détails d'un client spécifique// Vérifie que l'ID est correctement récupéré
    public function show($id)
    {
        if (!$id) {
            return response()->json(['error' => 'Client ID is missing'], 400);
        }

        $client = Client::with([
            'notes',
            'bordereauHistoriques.informations'
        ])->findOrFail($id);

        // Enregistrer la consultation
        try {
            RecentView::updateOrCreate(
                ['user_id' => Auth::id(), 'client_id' => $id],
                ['created_at' => now()]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to log view: ' . $e->getMessage()], 500);
        }

        if (request()->ajax()) {
            return response()->json(['user' => $client]);
        }

        return Inertia::render('ManagementCall', [
            'user' => $client,
        ]);
    }






    // Modifie les données d'un client
    public function update(Request $request, $id)
    {
        // Validation des données d'entrée
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

        // Condition pour définir recently_added (changement de couleur pipelette) pour le rôle 'Comptabilité'
        if (Auth::user() && collect(Auth::user()->roles)->contains(fn($role) => $role->name === 'Comptabilité')) {
            $validatedData['recently_added'] = true;
        } else {
            $validatedData['recently_added'] = false;
        }

        // Mise à jour des informations du client
        $client = Client::findOrFail($id);
        $client->update($validatedData);

        return response()->json($client);
    }




    //Ajoute un nouveau fournisseur
    public function store(Request $request)
    {
        // Validation des données d'entrée
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

        // Condition pour définir recently_added (changement de couleur pipelette) pour le rôle 'Comptabilité'
        if (Auth::user() && collect(Auth::user()->roles)->contains(fn($role) => $role->name === 'Comptabilité')) {
            $validatedData['recently_added'] = true;
        } else {
            $validatedData['recently_added'] = false;
        }


        try {
            // Création du client avec les données validées
            $client = Client::create($validatedData);

            return response()->json($client, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création du client: ' . $e->getMessage()], 500);
        }
    }


    // méthode pour enregistrer ou mettre à jour une vue lorsque l'utilisateur consulte un client.
    public function logView(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $userId = Auth::id();
        $clientId = $request->input('client_id');

        // Ajouter ou mettre à jour la consultation
        RecentView::updateOrCreate(
            ['user_id' => $userId, 'client_id' => $clientId],
            ['created_at' => now()]
        );

        return response()->json(['message' => 'View logged successfully']);
    }




    // méthode renvoie une liste des dernières consultations
    public function getRecentViews()
{
    $recentViews = RecentView::with(['user', 'client']) // Inclut le client et l'utilisateur
        ->orderBy('created_at', 'desc')
        ->take(10) // Limiter aux 10 dernières consultations
        ->get();

    return response()->json($recentViews);
}

}
