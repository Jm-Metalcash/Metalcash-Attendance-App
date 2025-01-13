<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\ClientsProspectsUpdate;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    // Affiche la liste des prospects avec leurs transactions et notes
    public function index($prospectId = null)
    {
        // Récupérer les dernières modifications depuis clients_prospects_update
        $recentUpdates = ClientsProspectsUpdate::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10) // Limiter à 10 modifications
            ->get();

        // Collecter les IDs des prospects et clients modifiés récemment
        $prospectIds = [];
        $clientIds = [];
        foreach ($recentUpdates as $update) {
            if ($update->updatable_type == Prospect::class) {
                $prospectIds[] = $update->updatable_id;
            } elseif ($update->updatable_type == Client::class) {
                $clientIds[] = $update->updatable_id;
            }
        }
        $prospectIds = array_unique($prospectIds);
        $clientIds = array_unique($clientIds);

        // Charger les prospects modifiés récemment
        $recentProspects = Prospect::with([
            'lastImportantNote:id,prospect_id,type',
        ])
            ->select('id', 'firstName', 'familyName', 'phone', 'country')
            ->whereIn('id', $prospectIds)
            ->get();

        // Ajouter last_important_note à chaque prospect
        $recentProspects->each(function ($prospect) {
            $prospect->last_important_note = $prospect->lastImportantNote?->type;
        });

        // Charger les clients modifiés récemment
        $recentClients = Client::with([
            'lastImportantNote:id,client_id,type',
        ])
            ->select('id', 'firstName', 'familyName', 'phone', 'country')
            ->whereIn('id', $clientIds)
            ->get();

        // Ajouter last_important_note à chaque client
        $recentClients->each(function ($client) {
            $client->last_important_note = $client->lastImportantNote?->type;
        });

        // Créer des mappings pour un accès rapide
        $prospectMap = $recentProspects->keyBy('id');
        $clientMap = $recentClients->keyBy('id');

        // Construire la liste des éléments modifiés récemment
        $recentModifiedItems = [];

        foreach ($recentUpdates as $update) {
            if ($update->updatable_type == Prospect::class) {
                $prospect = $prospectMap->get($update->updatable_id);

                if ($prospect) {
                    $recentModifiedItems[] = [
                        'id' => $prospect->id,
                        'firstName' => $prospect->firstName,
                        'familyName' => $prospect->familyName,
                        'phone' => $prospect->phone,
                        'country' => $prospect->country,
                        'type' => 'prospect',
                        'last_modifier' => $update->user->name ?? 'Inconnu',
                        'modified_at' => $update->created_at,
                        'lastImportantNote' => $prospect->last_important_note,
                    ];
                }
            } elseif ($update->updatable_type == Client::class) {
                $client = $clientMap->get($update->updatable_id);

                if ($client) {
                    $recentModifiedItems[] = [
                        'id' => $client->id,
                        'firstName' => $client->firstName,
                        'familyName' => $client->familyName,
                        'phone' => $client->phone,
                        'country' => $client->country,
                        'type' => 'client',
                        'last_modifier' => $update->user->name ?? 'Inconnu',
                        'modified_at' => $update->created_at,
                        'lastImportantNote' => $client->last_important_note,
                    ];
                }
            }
        }

        // Supprimer les doublons
        $recentModifiedItems = collect($recentModifiedItems)->unique(function ($item) {
            return $item['id'] . '-' . $item['type'];
        })->values();

        // Charger tous les prospects (limiter ou paginer si nécessaire)
        $prospects = Prospect::with([
            'lastImportantNote:id,prospect_id,type',
            'createdBy:id,name',
            'notes.creator:id,name',
            'notes.updater:id,name',
        ])
            ->select('id', 'firstName', 'familyName', 'phone', 'country', 'created_by', 'created_at')
            ->get();

        $prospects->each(function ($prospect) {
            $prospect->last_important_note = $prospect->lastImportantNote?->type;
        });

        // Charger tous les clients (limiter ou paginer si nécessaire)
        $clients = Client::with([
            'lastImportantNote:id,client_id,type',
        ])
            ->select('id', 'firstName', 'familyName', 'phone', 'country')
            ->get();

        $clients->each(function ($client) {
            $client->last_important_note = $client->lastImportantNote?->type;
        });

        // Charger le prospect sélectionné si un ID est fourni
        $selectedProspect = null;
        if ($prospectId) {
            $selectedProspect = Prospect::with([
                'notes' => function ($query) {
                    $query->select('id', 'prospect_id', 'type', 'content', 'note_date', 'created_by', 'updated_by')
                        ->with(['creator:id,name', 'updater:id,name'])
                        ->whereIn('type', ['information', 'premium', 'avertissement', 'attention', 'a_contacter'])
                        ->latest('note_date');
                },
                'bordereauHistoriques.informations',
            ])
                ->select('id', 'firstName', 'familyName', 'phone', 'country', 'locality', 'created_at', 'updated_at')
                ->find($prospectId);

            if (!$selectedProspect) {
                return redirect()->route('management-call')->withErrors('Prospect introuvable.');
            }
        }

        return Inertia::render('ManagementCall', [
            'prospects' => $prospects,
            'clients' => $clients,
            'currentUser' => Auth::user(),
            'selectedProspect' => $selectedProspect,
            'recentModifiedItems' => $recentModifiedItems,
        ]);
    }




    // Affiche les détails d'un prospect spécifique
    public function showProspect($id)
    {
        if (!$id) {
            return response()->json(['error' => 'Prospect ID is missing'], 400);
        }

        $prospect = Prospect::with([
            'notes' => function ($query) {
                $query->select('id', 'prospect_id', 'type', 'content', 'note_date', 'created_by', 'updated_by')
                    ->with(['creator:id,name', 'updater:id,name'])
                    ->whereIn('type', ['information', 'avertissement', 'premium', 'attention', 'a_contacter'])
                    ->latest('note_date');
            },
            'bordereauHistoriques.informations',
            'createdBy:id,name',
            'updatedBy:id,name',
        ])
            ->select('id', 'firstName', 'familyName', 'phone', 'country', 'locality', 'created_at', 'updated_at')
            ->findOrFail($id);

        return response()->json($prospect->notes);

        $latestWarning = $prospect->notes->first();
        $prospect->has_warning = !is_null($latestWarning);
        $prospect->latest_warning_type = $latestWarning->type ?? null;

        return Inertia::render('ManagementCall', [
            'selectedProspect' => $prospect,
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

        // Enregistrer la mise à jour dans la table clients_prospects_update
        ClientsProspectsUpdate::create([
            'updatable_type' => Prospect::class,
            'updatable_id' => $prospect->id,
            'user_id' => Auth::id(),
            'action' => 'updated',
        ]);

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

        $validatedData = array_filter($validatedData, fn($value) => !is_null($value));

        $validatedData['recently_added'] = Auth::user() && collect(Auth::user()->roles)
            ->contains(fn($role) => $role->name === 'Comptabilité');

        $validatedData['created_by'] = Auth::id();

        try {
            // Création du prospect
            $prospect = Prospect::create($validatedData);

            // Enregistrer la création dans la table clients_prospects_update
            ClientsProspectsUpdate::create([
                'updatable_type' => Prospect::class,
                'updatable_id' => $prospect->id,
                'user_id' => Auth::id(),
                'action' => 'created',
            ]);

            // Retourne les données avec Inertia pour redirection côté client
            return response()->json(['id' => $prospect->id], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}
