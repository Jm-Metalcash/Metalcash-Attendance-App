<?php

namespace App\Http\Controllers;

use App\Models\NoteProspect;
use App\Models\Prospect;
use Illuminate\Http\Request;
use App\Models\ClientsProspectsUpdate;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NoteProspectController extends Controller
{
    // Mise à jour d'une note
    public function update(Request $request, Prospect $prospect, NoteProspect $note)
    {
        // Vérifier que la note appartient bien au prospect
        if ($note->prospect_id !== $prospect->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
            'type' => 'required|string|in:information,avertissement,premium,attention',
        ]);

        $note->update($validatedData);

        return response()->json($note);
    }

    // Ajout d'une nouvelle note
    public function store(Request $request, Prospect $prospect)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
            'note_date' => 'nullable|date',
            'type' => 'required|string|in:information,avertissement,premium,attention',
        ]);

        // Convertir la date si nécessaire
        $validatedData['note_date'] = Carbon::parse($validatedData['note_date'])->setTimezone('Europe/Paris');

        // Créer une note uniquement si `content` est renseigné
        if (!empty($validatedData['content'])) {
            $note = $prospect->notes()->create($validatedData);


            return response()->json([
                'id' => $note->id,
                'content' => $note->content,
                'note_date' => $note->note_date->format('Y-m-d\TH:i:s'),
                'type' => $note->type
            ]);
        }

        // Enregistrer la mise à jour dans clients_prospects_update
        ClientsProspectsUpdate::create([
            'updatable_type' => Prospect::class,
            'updatable_id' => $prospect->id,
            'user_id' => Auth::id(),
            'action' => 'note_added',
        ]);

        return response()->json(['message' => 'Note not created as content is empty'], 200);
    }

    // Suppression d'une note
    public function destroy(Prospect $prospect, NoteProspect $note)
    {
        // Vérifier que la note appartient bien au prospect
        if ($note->prospect_id !== $prospect->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Vérifie si la note est un avertissement
        $isWarning = $note->type === 'avertissement';

        // Supprime la note
        $note->delete();

        // Si c'était un avertissement, vérifie s'il reste d'autres avertissements pour ce prospect
        if ($isWarning) {
            $hasOtherWarnings = $prospect->notes()->where('type', 'avertissement')->exists();

            // Si aucun autre avertissement n'existe, retirer le prospect de la blacklist
            if (!$hasOtherWarnings) {
                $prospect->update(['blacklist' => 0]);
            }
        }

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
