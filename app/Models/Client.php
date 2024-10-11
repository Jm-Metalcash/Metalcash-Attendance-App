<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'fullName', 'familyName', 'firstName', 'address', 'locality', 'postalCode', 'country', 'email', 'phone', 'company', 'companyvat', 'regdate'
    ];

    // Relation avec la table historical_transactions (transactions)
    public function transactions()
    {
        return $this->hasMany(HistoricalTransaction::class, 'client_id');
    }

    // Relation avec la table notes
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}

