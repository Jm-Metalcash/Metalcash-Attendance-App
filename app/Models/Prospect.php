<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    use HasFactory;

    // Colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'fullName',
        'familyName',
        'firstName',
        'country',
        'locality',
        'phone',
        'blacklist',
        'recently_added',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($prospect) {
            $prospect->fullName = trim($prospect->firstName . ' ' . $prospect->familyName);
        });
    }

    // Relation avec les notes du prospect
    public function notes()
    {
        return $this->hasMany(NoteProspect::class);
    }

    // Relation pour la dernière note importante
    public function lastImportantNote()
    {
        return $this->hasOne(NoteProspect::class, 'prospect_id')
            ->whereIn('type', ['premium', 'avertissement', 'attention', 'a_contacter'])
            ->latest('note_date');
    }

    // Accesseur pour l'attribut has_warning
    public function getHasWarningAttribute()
    {
        return $this->notes()
            ->whereIn('type', ['avertissement', 'premium', 'attention', 'a_contacter'])
            ->exists();
    }

    // Relations supplémentaires
    public function bordereauHistoriques()
    {
        return $this->hasMany(BordereauHistorique::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relation avec les mises à jour
    public function updates()
    {
        return $this->morphMany(ClientsProspectsUpdate::class, 'updatable');
    }
}
