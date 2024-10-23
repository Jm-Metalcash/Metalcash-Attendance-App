<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\BordereauHistorique;
use Illuminate\Http\Request;

class BordereauHistoriqueController extends Controller
{
    public function show($clientId, $bordereauHistoriqueId)
    {
        $bordereauHistorique = BordereauHistorique::with('informations')
            ->where('client_id', $clientId)
            ->findOrFail($bordereauHistoriqueId);
    
        return response()->json($bordereauHistorique);
    }
    


    public function store(Request $request, $clientId)
    {
        $client = Client::findOrFail($clientId);
    
        $data = $request->validate([
            'action' => 'required|string|max:255',
            'details' => 'nullable|string',
            'informations' => 'required|array',
            'informations.*.typeofmetal' => 'required|string|max:100',
            'informations.*.weight' => 'required|string|max:50',
            'informations.*.description' => 'nullable|string',
            'informations.*.status' => 'boolean',
        ]);
    
        $data['client_id'] = $client->id;
    
        // Créer le bordereau historique
        $bordereauHistorique = BordereauHistorique::create($data);
    
        // Ajouter les informations
        foreach ($data['informations'] as $infoData) {
            $bordereauHistorique->informations()->create($infoData);
        }
    
        return response()->json($bordereauHistorique->load('informations'), 201);
    }
    
}
