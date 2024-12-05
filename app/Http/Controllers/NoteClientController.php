<?php

namespace App\Http\Controllers;

use App\Models\NoteClient;
use Illuminate\Http\Request;
use App\Models\ClientsProspectsUpdate;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use Carbon\Carbon;


class NoteClientController extends Controller
{
    // Ajouter une note à un client
    public function store(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
            'note_date' => 'nullable|date',
            'type' => 'required|string|in:information,avertissement,premium,attention',
        ]);

        // Convertir la date si nécessaire
        if (!empty($validatedData['note_date'])) {
            $validatedData['note_date'] = Carbon::parse($validatedData['note_date'])->setTimezone('Europe/Paris');
        } else {
            $validatedData['note_date'] = Carbon::now()->setTimezone('Europe/Paris');
        }

        // Créer une note uniquement si `content` est renseigné
        if (!empty($validatedData['content'])) {
            $note = $client->notes()->create($validatedData);

            // Enregistrer la mise à jour dans clients_prospects_update
            ClientsProspectsUpdate::create([
                'updatable_type' => Client::class,
                'updatable_id' => $client->id,
                'user_id' => Auth::id(),
                'action' => 'note_added',
            ]);

            return response()->json([
                'id' => $note->id,
                'content' => $note->content,
                'note_date' => $note->note_date->format('Y-m-d\TH:i:s'),
                'type' => $note->type,
            ]);
        }

        // Si le contenu est vide, enregistrer quand même la mise à jour
        ClientsProspectsUpdate::create([
            'updatable_type' => Client::class,
            'updatable_id' => $client->id,
            'user_id' => Auth::id(),
            'action' => 'note_added',
        ]);

        return response()->json(['message' => 'Note non créée car le contenu est vide'], 200);
    }


    // Modifier une note d'un client
    public function updateNote(Request $request, $clientId, $noteId)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500', // Limite la longueur du contenu
            'type' => 'required|string|in:information,avertissement,premium,attention',
        ]);

        try {
            $note = NoteClient::where('id', $noteId)
                ->where('client_id', $clientId)
                ->firstOrFail();

            $note->update($validated);

            return response()->json($note);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update note: ' . $e->getMessage()], 500);
        }
    }

    // Supprimer une note d'un client
    public function deleteNote($clientId, $noteId)
    {
        try {
            $note = NoteClient::where('client_id', $clientId)->findOrFail($noteId);
            $note->delete();

            return response()->json(['message' => 'Note deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete note: ' . $e->getMessage()], 500);
        }
    }
}
