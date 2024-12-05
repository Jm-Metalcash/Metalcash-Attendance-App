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

    public $timestamps = true;

    // Relation avec les notes du client
    public function notes()
    {
        return $this->hasMany(NoteClient::class);
    }

    // Relation pour la dernière note importante
    public function lastImportantNote()
    {
        return $this->hasOne(NoteClient::class, 'client_id')
            ->whereIn('type', ['information', 'premium', 'avertissement', 'attention'])
            ->latest('note_date');
    }

    // Accesseur pour l'attribut has_warning
    public function getHasWarningAttribute()
    {
        return $this->notes()
            ->whereIn('type', ['avertissement', 'premium', 'attention'])
            ->exists();
    }

    // Relation avec les mises à jour
    public function updates()
    {
        return $this->morphMany(ClientsProspectsUpdate::class, 'updatable');
    }
}
