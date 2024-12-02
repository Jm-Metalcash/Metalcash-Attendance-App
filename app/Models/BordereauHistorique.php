<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BordereauHistorique extends Model
{
    use HasFactory;

    // Si le nom de la table ne correspond pas au pluriel du modèle
    protected $table = 'bordereau_historique';

    // Colonnes pouvant être assignées en masse
    protected $fillable = [
        'prospect_id',
        'action',
        'details',
    ];

    // Relation avec le modèle Client
    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
    

    public function informations()
    {
        return $this->hasMany(BordereauInformation::class);
    }
}