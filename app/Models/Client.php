<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'fullName', 'familyName', 'firstName', 'address', 'locality', 'postalCode', 'country', 'email', 'phone', 
        'blacklist', 'company', 'companyvat', 'regdate', 'entity', 'docType', 'docNumber', 'docExp', 'interest', 
        'referer', 'birthDate'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($client) {
            $client->fullName = trim($client->firstName . ' ' . $client->familyName);
        });
    }
   
    // Relation avec la table notes
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function bordereauHistoriques()
    {
        return $this->hasMany(BordereauHistorique::class);
    }
}
