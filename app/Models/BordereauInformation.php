<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BordereauInformation extends Model
{
    use HasFactory;

    protected $table = 'bordereau_informations';

    protected $fillable = [
        'bordereau_historique_id',
        'typeofmetal',
        'weight',
        'description',
        'status',
    ];

    // Relation avec le bordereau historique
    public function bordereauHistorique()
    {
        return $this->belongsTo(BordereauHistorique::class);
    }
}
