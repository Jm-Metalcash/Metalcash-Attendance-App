<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        // Récupère les clients avec leurs transactions et notes
        $clients = Client::with(['transactions', 'notes'])->get();

        // Envoie les données à la vue Inertia
        return Inertia::render('ManagementCall', [
            'clients' => $clients,
        ]);
    }
}

