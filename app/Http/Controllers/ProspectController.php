<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\RecentView;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    // Affiche la liste des prospects avec leurs transactions et notes
    public function index($prospectId = null)
    {
        // Récupérer tous les prospects avec leurs relations
        $prospects = Prospect::with(['createdBy:id,name', 'updatedBy:id,name'])->get();

        // Récupérer tous les clients avec leurs relations
        $clients = Client::with(['notes' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->get();

        // Ajouter le type 'prospect' pour chaque prospect
        $prospects = $prospects->map(function ($prospect) {
            $prospect['type'] = 'prospect';
            return $prospect;
        });

        // Ajouter le type 'client' pour chaque client
        $clients = $clients->map(function ($client) {
            $client['type'] = 'client';
            return $client;
        });

        // Fusionner les prospects et clients pour 'recentAddedProspects'
        $recentAddedProspects = $prospects
            ->merge($clients)
            ->sortByDesc('created_at') // Trier par date d'ajout
            ->take(20) // Limiter à 20 résultats
            ->values(); // Ré-indexer la collection

        // Initialiser la variable pour le prospect sélectionné
        $selectedProspect = null;

        // Si un ID de prospect est fourni, charger le prospect avec ses relations
        if ($prospectId) {
            $selectedProspect = Prospect::with([
                'notes',
                'bordereauHistoriques.informations',
                'createdBy',
                'updatedBy'
            ])->find($prospectId);

            // Si le prospect n'est pas trouvé, rediriger avec un message d'erreur
            if (!$selectedProspect) {
                return redirect()->route('management-call')->withErrors('Prospect introuvable.');
            }

            // Enregistrer la consultation si le prospect est trouvé
            try {
                RecentView::updateOrCreate(
                    ['user_id' => Auth::id(), 'prospect_id' => $prospectId],
                    ['created_at' => now()]
                );
            } catch (\Exception $e) {
                return response()->json(['error' => 'Échec de l\'enregistrement de la vue : ' . $e->getMessage()], 500);
            }
        }

        return Inertia::render('ManagementCall', [
            'prospects' => $prospects,
            'clients' => $clients,
            'recentAddedProspects' => $recentAddedProspects,
            'currentUser' => Auth::user(),
            'selectedProspect' => $selectedProspect,
        ]);
    }




    // Affiche les détails d'un prospect spécifique
    public function show($id)
    {
        if (!$id) {
            return response()->json(['error' => 'Prospect ID is missing'], 400);
        }

        $prospect = Prospect::with([
            'notes',
            'bordereauHistoriques.informations',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);

        // Enregistrer la consultation
        try {
            RecentView::updateOrCreate(
                ['user_id' => Auth::id(), 'prospect_id' => $id],
                ['created_at' => now()]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to log view: ' . $e->getMessage()], 500);
        }

        if (request()->ajax()) {
            return response()->json(['prospect' => $prospect]);
        }

        return Inertia::render('ManagementCall', [
            'prospect' => $prospect,
        ]);
    }


    // Modifie les données d'un prospect
    public function update(Request $request, $id)
    {
        // Validation des données d'entrée
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'familyName' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'blacklist' => 'nullable|boolean',
        ]);

        // Définir recently_added en fonction du rôle
        if (Auth::user() && collect(Auth::user()->roles)->contains(fn($role) => $role->name === 'Comptabilité')) {
            $validatedData['recently_added'] = true;
        } else {
            $validatedData['recently_added'] = false;
        }

        // Ajouter l'ID de l'utilisateur qui modifie le prospect
        $validatedData['updated_by'] = Auth::id();

        // Mise à jour des informations du prospect
        $prospect = Prospect::findOrFail($id);
        $prospect->update($validatedData);

        return response()->json($prospect);
    }

    // Ajoute un nouveau prospect
    public function store(Request $request)
    {
        // Validation des données d'entrée
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'familyName' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255|unique:prospects,phone',
            'locality' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'blacklist' => 'nullable|boolean',
        ]);

        // Supprimer les champs avec des valeurs nulles
        $validatedData = array_filter($validatedData, fn($value) => !is_null($value));

        // Définir recently_added en fonction du rôle
        if (Auth::user() && collect(Auth::user()->roles)->contains(fn($role) => $role->name === 'Comptabilité')) {
            $validatedData['recently_added'] = true;
        } else {
            $validatedData['recently_added'] = false;
        }

        // Ajouter l'ID de l'utilisateur qui crée le prospect
        $validatedData['created_by'] = Auth::id();

        try {
            // Création du prospect avec les données validées
            $prospect = Prospect::create($validatedData);

            return response()->json($prospect, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création du prospect: ' . $e->getMessage()], 500);
        }
    }

    // Enregistre ou met à jour une vue lorsque l'utilisateur consulte un prospect
    public function logView(Request $request)
    {
        $request->validate([
            'prospect_id' => 'required|exists:prospects,id',
        ]);

        $userId = Auth::id();
        $prospectId = $request->input('prospect_id');

        // Ajouter ou mettre à jour la consultation
        RecentView::updateOrCreate(
            ['user_id' => $userId, 'prospect_id' => $prospectId],
            ['created_at' => now()]
        );

        return response()->json(['message' => 'View logged successfully']);
    }

    // Renvoie une liste des dernières consultations
    public function getRecentViews()
    {
        $recentViews = RecentView::with(['user', 'prospect', 'client'])
            ->whereHas('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json(
            $recentViews->map(function ($view) {
                $item = null;
                $itemType = null;

                if ($view->prospect) {
                    $item = $view->prospect;
                    $itemType = 'prospect';
                } elseif ($view->client) {
                    $item = $view->client;
                    $itemType = 'client';
                }

                return [
                    'prospect' => $item,
                    'prospect_type' => $itemType,
                    'viewedBy' => $view->user ? $view->user->name : 'Inconnu',
                    'viewedAt' => $view->created_at->format('d/m/Y H:i'),
                ];
            })
        );
    }



    public function showClient($id)
    {
        if (!$id) {
            return response()->json(['error' => 'Client ID is missing'], 400);
        }

        $client = Client::with([
            'notes',
        ])->findOrFail($id);

        // Log the client view
        try {
            RecentView::updateOrCreate(
                ['user_id' => Auth::id(), 'client_id' => $id],
                ['created_at' => now(), 'prospect_id' => null]
            );
        } catch (\Exception $e) {
            // Return an Inertia response with an error
            return Inertia::render('Error', ['message' => 'Failed to log view: ' . $e->getMessage()]);
        }

        return Inertia::render('ManagementCall', [
            'selectedClient' => $client,
            'prospects' => Prospect::all(),
            'clients' => Client::all(),
        ]);
    }
}
