<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NoteController extends Controller
{
    // Mise à jour d'une note
    public function update(Request $request, Client $client, Note $note)
    {
        // Vérifier que la note appartient bien au client
        if ($note->client_id !== $client->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
        ]);

        $note->update($validatedData);

        return response()->json($note);
    }

    // Ajout d'une nouvelle note
    public function store(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
            'note_date' => 'nullable|date',
            'type' => 'required|string|in:information,avertissement'
        ]);

        // Convertir la date si nécessaire
        $validatedData['note_date'] = Carbon::parse($validatedData['note_date'])->setTimezone('Europe/Paris');

        // Créer une note uniquement si `content` est renseigné
        if (!empty($validatedData['content'])) {
            $note = $client->notes()->create($validatedData);

            // Retourner la note avec la date formatée
            return response()->json([
                'id' => $note->id,
                'content' => $note->content,
                'note_date' => $note->note_date->format('Y-m-d\TH:i:s'),
                'type' => $note->type
            ]);
        }

        return response()->json(['message' => 'Note not created as content is empty'], 200);
    }
}
