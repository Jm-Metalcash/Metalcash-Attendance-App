<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\RecentView;
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
        $recentViews = RecentView::with(['user', 'prospect']) // Charge les relations
            ->whereHas('user') // Filtre les vues avec un utilisateur valide
            ->whereHas('prospect') // Filtre les vues avec un prospect valide
            ->orderBy('created_at', 'desc')
            ->take(10) // Limiter aux 10 dernières consultations
            ->get();

        return response()->json(
            $recentViews->map(function ($view) {
                return [
                    'prospect' => $view->prospect,
                    'viewedBy' => $view->user ? $view->user->name : 'Inconnu',
                    'viewedAt' => $view->created_at->format('d/m/Y H:i'),
                ];
            })
        );
    }
}
