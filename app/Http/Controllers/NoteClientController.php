<?php

namespace App\Http\Controllers;

use App\Models\NoteClient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NoteClientController extends Controller
{
  //Ajoute une note à un client
  public function storeNote(Request $request, $id)
  {
      $validated = $request->validate([
          'content' => 'required|string',
          'type' => 'required|string|in:information,avertissement',
      ]);

      $note = NoteClient::create([
          'client_id' => $id,
          'content' => $validated['content'],
          'type' => $validated['type'],
          'note_date' => now(),
      ]);

      return response()->json($note, 201);
  }


  //Modifie une note d'un client
  public function updateNote(Request $request, $clientId, $noteId)
  {
      $note = NoteClient::where('client_id', $clientId)->findOrFail($noteId);

      $validated = $request->validate(['content' => 'required|string']);
      $note->update(['content' => $validated['content']]);

      return response()->json($note);
  }


  //Supprime une note d'un client
  public function deleteNote($clientId, $noteId)
  {
      $note = NoteClient::where('client_id', $clientId)->findOrFail($noteId);
      $note->delete();

      return response()->noContent();
  }
}