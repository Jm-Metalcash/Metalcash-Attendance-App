<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Prospect;
use App\Models\NoteClient;
use App\Models\NoteProspect;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactRelanceController extends Controller
{
    public function index()
    {
        // Récupérer les notes clients avec status "a_contacter"
        $clientNotes = NoteClient::where('type', 'a_contacter')
            ->with('client:id,fullName,phone')
            ->orderBy('note_date', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'name' => $note->client->fullName,
                    'phone' => $note->client->phone,
                    'status' => 'Client',
                    'note' => $note->content,
                    'date' => $note->note_date,
                    'action_relance' => $note->action_relance,
                    'type' => 'client'
                ];
            });

        // Récupérer les notes prospects avec status "a_contacter"
        $prospectNotes = NoteProspect::where('type', 'a_contacter')
            ->with('prospect:id,fullName,phone')
            ->orderBy('note_date', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'name' => $note->prospect->fullName,
                    'phone' => $note->prospect->phone,
                    'status' => 'Prospect',
                    'note' => $note->content,
                    'date' => $note->note_date,
                    'action_relance' => $note->action_relance,
                    'type' => 'prospect'
                ];
            });

        // Combiner les notes et trier le résultat final par date
        $contactsToCall = $clientNotes->concat($prospectNotes)
            ->sortByDesc('date')
            ->values();

        return Inertia::render('ContactRelance', [
            'contactsToCall' => $contactsToCall
        ]);
    }

    public function updateAction(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:client,prospect',
            'action' => 'required|integer|between:0,4'
        ]);

        if ($request->type === 'client') {
            $note = NoteClient::findOrFail($request->id);
        } else {
            $note = NoteProspect::findOrFail($request->id);
        }

        $note->action_relance = $request->action;
        $note->save();

        return back();
    }
}
