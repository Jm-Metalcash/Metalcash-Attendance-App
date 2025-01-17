<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Prospect;
use App\Models\NoteClient;
use App\Models\NoteProspect;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

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

        // Récupérer l'historique des 7 derniers jours
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        $clientHistory = NoteClient::where('date_relance_modified', '>=', $sevenDaysAgo)
            ->whereNotNull('date_relance_modified')
            ->with(['client:id,fullName', 'modifiedBy:id,name'])
            ->get()
            ->map(function ($note) {
                return [
                    'date_relance_modified' => $note->date_relance_modified,
                    'contact_name' => $note->client->fullName,
                    'old_status_relance' => $note->old_status_relance,
                    'new_status_relance' => $note->new_status_relance,
                    'modified_by_relance' => $note->modifiedBy ? $note->modifiedBy->name : 'Système',
                    'type' => 'client',
                    'note' => $note->note_content_status
                ];
            });

        $prospectHistory = NoteProspect::where('date_relance_modified', '>=', $sevenDaysAgo)
            ->whereNotNull('date_relance_modified')
            ->with(['prospect:id,fullName', 'modifiedBy:id,name'])
            ->get()
            ->map(function ($note) {
                return [
                    'date_relance_modified' => $note->date_relance_modified,
                    'contact_name' => $note->prospect->fullName,
                    'old_status_relance' => $note->old_status_relance,
                    'new_status_relance' => $note->new_status_relance,
                    'modified_by_relance' => $note->modifiedBy ? $note->modifiedBy->name : 'Système',
                    'type' => 'prospect',
                    'note' => $note->note_content_status
                ];
            });

        $callHistory = $clientHistory->concat($prospectHistory)
            ->sortByDesc('date_relance_modified')
            ->values();

        return Inertia::render('ContactRelance', [
            'contactsToCall' => $contactsToCall,
            'callHistory' => $callHistory
        ]);
    }

    public function updateAction(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:client,prospect',
            'action' => 'required|integer|between:0,4',
            'old_status' => 'nullable|integer|between:0,4',
            'note' => 'nullable|string'
        ]);

        if ($request->type === 'client') {
            $note = NoteClient::findOrFail($request->id);
        } else {
            $note = NoteProspect::findOrFail($request->id);
        }

        // Enregistrer l'historique
        $note->date_relance_modified = now();
        $note->old_status_relance = $request->old_status;
        $note->new_status_relance = $request->action;
        $note->modified_by_relance = auth()->id();
        $note->note_content_status = $request->note;
        
        // Mettre à jour l'action
        $note->action_relance = $request->action;
        $note->save();

        return back();
    }
}
