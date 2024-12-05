<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstName',
        'familyName',
        'phone',
        'locality',
        'country',
    ];


    public function notes()
    {
        return $this->hasMany(NoteClient::class);
    }


    // Déterminer si un client a une note d'avertissement
    public function getHasWarningAttribute()
{
    // Vérifie si le client a au moins une note avec un des types spécifiés
    return $this->notes()
        ->whereIn('type', ['avertissement', 'premium', 'attention'])
        ->exists();
}

// Dans le modèle Prospect.php et Client.php
public function getLastImportantNoteAttribute()
{
    $importantNotes = $this->notes()
        ->whereIn('type', ['premium', 'avertissement', 'attention'])
        ->orderBy('note_date', 'desc')
        ->first();

    return $importantNotes ? $importantNotes->type : null;
}


}
