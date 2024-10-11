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
            'content' => 'required|string|max:1000',
        ]);

        $note->update($validatedData);

        return response()->json(['message' => 'Note updated successfully']);
    }

    // Ajout d'une nouvelle note
    public function store(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'content' => 'required|string|max:1000',
            'note_date' => 'required|date', // Accepte les dates au format ISO 8601
        ]);

        // Convertir la date si nécessaire
        $validatedData['note_date'] = Carbon::parse($validatedData['note_date']);

        $note = $client->notes()->create($validatedData);

        return response()->json(['id' => $note->id]);
    }
}
