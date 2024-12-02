<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ManagementCallController extends Controller
{
    public function index(Request $request)
    {
        // Récupérer tous les prospects
        $prospects = Prospect::select(['id', 'firstName', 'familyName', 'phone', 'locality', 'country', 'created_at', 'updated_at'])
            ->get()
            ->map(function ($prospect) {
                $prospect->type = 'prospect'; // Ajouter un type
                return $prospect;
            });

        // Récupérer tous les clients
        $clients = Client::select(['id', 'firstName', 'familyName', 'phone', 'locality', 'country', 'regdate'])
            ->get()
            ->map(function ($client) {
                $client->type = 'client'; // Ajouter un type
                return $client;
            });

        // Retourner la vue avec les données combinées
        return Inertia::render('ManagementCall', [
            'prospects' => $prospects,
            'clients' => $clients,
            'currentUser' => Auth::user(),
        ]);
    }
}