<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProspectController extends Controller
{
    // Affiche la liste des prospects avec leurs transactions et notes
    public function index($prospectId = null)
    {
        // Charger les prospects avec les relations nécessaires
        $prospects = Prospect::with([
            'createdBy:id,name',
            'updatedBy:id,name',
            'notes' => function ($query) {
                $query->select('id', 'prospect_id', 'type', 'note_date')
                    ->whereIn('type', ['premium', 'avertissement', 'attention'])
                    ->orderBy('note_date', 'desc');
            }
        ])
            ->select('id', 'firstName', 'familyName', 'phone', 'country', 'created_at', 'updated_at')
            ->get();

        // Ajouter last_important_note à chaque prospect
        $prospects->each(function ($prospect) {
            $prospect->last_important_note = $prospect->notes->first()?->type;
        });


        // Charger le prospect sélectionné si un ID est fourni
        $selectedProspect = null;
        if ($prospectId) {
            $selectedProspect = Prospect::with([
                'notes' => function ($query) {
                    $query->select('id', 'prospect_id', 'type', 'content', 'note_date')
                        ->whereIn('type', ['premium', 'avertissement', 'attention'])
                        ->latest('note_date');
                },
                'bordereauHistoriques.informations',
                'createdBy:id,name',
                'updatedBy:id,name',
            ])
                ->select('id', 'firstName', 'familyName', 'phone', 'country', 'locality', 'created_at', 'updated_at')
                ->find($prospectId);

            if (!$selectedProspect) {
                return redirect()->route('management-call')->withErrors('Prospect introuvable.');
            }

        }

        return Inertia::render('ManagementCall', [
            'prospects' => $prospects,
            'clients' => Client::all(),
            'currentUser' => Auth::user(),
            'selectedProspect' => $selectedProspect,
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
                $query->select('id', 'prospect_id', 'type', 'content', 'note_date')
                    ->whereIn('type', ['avertissement', 'premium', 'attention'])
                    ->latest('note_date');
            },
            'bordereauHistoriques.informations',
            'createdBy:id,name',
            'updatedBy:id,name',
        ])
            ->select('id', 'firstName', 'familyName', 'phone', 'country', 'locality', 'created_at', 'updated_at')
            ->findOrFail($id);

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
}
