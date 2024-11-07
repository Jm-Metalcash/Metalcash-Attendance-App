<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Affiche le tableau de bord.
     */
    public function index()
    {
        // Retourne la vue Index via Inertia
        return Inertia::render('Index');
    }
}
