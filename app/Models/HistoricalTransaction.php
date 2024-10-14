<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalTransaction extends Model
{
    use HasFactory;

    // Colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'client_id', 'date', 'type', 'typeofmetal', 'weight', 'details'
    ];

    protected $dates = ['date'];

    // Relation inverse avec la table clients
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
