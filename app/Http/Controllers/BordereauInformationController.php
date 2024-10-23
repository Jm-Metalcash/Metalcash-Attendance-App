<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BordereauHistorique;

class BordereauInformationController extends Controller
{
    public function store(Request $request, $bordereauHistoriqueId)
{
    $bordereauHistorique = BordereauHistorique::findOrFail($bordereauHistoriqueId);

    $data = $request->validate([
        'typeofmetal' => 'required|string|max:100',
        'weight' => 'required|string|max:50',
        'description' => 'nullable|string',
        'status' => 'boolean',
    ]);

    $information = $bordereauHistorique->informations()->create($data);

    return response()->json($information, 201);
}

}
