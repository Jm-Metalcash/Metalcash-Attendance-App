<?php

namespace App\Http\Controllers;

use App\Models\NoteClient;
use Illuminate\Http\Request;
use App\Models\ClientsProspectsUpdate;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;

class NoteClientController extends Controller
{
    // Ajouter une note à un client
    public function storeNote(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);
    
        $validatedData = $request->validate([
            'type' => 'required|string|max:255',
            'content' => 'nullable|string',
            'note_date' => 'nullable|date',
        ]);
    
        $validatedData['client_id'] = $client->id;
        $validatedData['user_id'] = Auth::id();
    
        $note = NoteClient::create($validatedData);
    
        // Enregistrer la mise à jour dans clients_prospects_update
        ClientsProspectsUpdate::create([
            'updatable_type' => Client::class,
            'updatable_id' => $client->id,
            'user_id' => Auth::id(),
            'action' => 'note_added',
        ]);
    
        return response()->json($note);
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
