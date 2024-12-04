<?php

namespace App\Http\Controllers;

use App\Models\NoteClient;
use Illuminate\Http\Request;

class NoteClientController extends Controller
{
    // Ajouter une note à un client
    public function storeNote(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500', // Limite la longueur du contenu
            'type' => 'required|string|in:information,avertissement,premium,attention',
        ]);

        try {
            $note = NoteClient::create([
                'client_id' => $id,
                'content' => $validated['content'],
                'type' => $validated['type'],
                'note_date' => now(),
            ]);

            return response()->json($note, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create note: ' . $e->getMessage()], 500);
        }
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
