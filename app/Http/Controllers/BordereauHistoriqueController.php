<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\BordereauHistorique;
use Illuminate\Http\Request;

class BordereauHistoriqueController extends Controller
{
    /**
     * Show a specific Bordereau Historique for a given prospect.
     */
    public function show($prospectId, $bordereauHistoriqueId)
    {
        $bordereauHistorique = BordereauHistorique::with('informations')
            ->where('prospect_id', $prospectId)
            ->findOrFail($bordereauHistoriqueId);
    
        return response()->json($bordereauHistorique);
    }
    
    /**
     * Store a new Bordereau Historique for a given prospect.
     */
    public function store(Request $request, $prospectId)
    {
        $prospect = Prospect::findOrFail($prospectId);
    
        $data = $request->validate([
            'action' => 'required|string|max:255',
            'details' => 'nullable|string',
            'informations' => 'required|array',
            'informations.*.typeofmetal' => 'required|string|max:100',
            'informations.*.weight' => 'required|string|max:50',
            'informations.*.description' => 'nullable|string',
            'informations.*.status' => 'boolean',
        ]);
    
        $data['prospect_id'] = $prospect->id;
    
        // Create the Bordereau Historique
        $bordereauHistorique = BordereauHistorique::create($data);
    
        // Add the associated information
        foreach ($data['informations'] as $infoData) {
            $bordereauHistorique->informations()->create($infoData);
        }
    
        return response()->json($bordereauHistorique->load('informations'), 201);
    }
}
