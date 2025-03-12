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
    public function store(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
            'note_date' => 'nullable|date',
            'type' => 'required|string|in:information,avertissement,premium,attention,a_contacter',
        ]);

        if (!empty($validatedData['note_date'])) {
            $validatedData['note_date'] = Carbon::parse($validatedData['note_date'])->setTimezone('Europe/Paris');
        } else {
            $validatedData['note_date'] = Carbon::now()->setTimezone('Europe/Paris');
        }

        $note = $client->notes()->create([
            ...$validatedData,
            'created_by' => Auth::id(),
        ]);

        ClientsProspectsUpdate::create([
            'updatable_type' => Client::class,
            'updatable_id' => $client->id,
            'user_id' => Auth::id(),
            'action' => 'note_added',
        ]);

        return response()->json($note->load('creator', 'updater'));
    }

    public function updateNote(Request $request, $clientId, $noteId)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
            'type' => 'required|string|in:information,avertissement,premium,attention,a_contacter',
        ]);

        try {
            $note = NoteClient::where('id', $noteId)->where('client_id', $clientId)->firstOrFail();

            $note->update([
                ...$validated,
                'updated_by' => Auth::id(),
            ]);

            return response()->json($note->load('creator', 'updater'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update note: ' . $e->getMessage()], 500);
        }
    }

    public function deleteNote($clientId, $noteId)
    {
        try {
            $note = NoteClient::where('client_id', $clientId)->findOrFail($noteId);
            $client = Client::find($clientId);
            $note->delete();

            // Ajouter une entrée dans ClientsProspectsUpdate pour suivre cette modification
            ClientsProspectsUpdate::create([
                'updatable_type' => Client::class,
                'updatable_id' => $clientId,
                'user_id' => Auth::id(),
                'action' => 'note_deleted',
            ]);

            return response()->json(['message' => 'Note deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete note: ' . $e->getMessage()], 500);
        }
    }
}
