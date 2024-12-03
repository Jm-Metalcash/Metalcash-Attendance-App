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

    // Relation avec la table notes
    public function notes()
    {
        return $this->hasMany(NoteProspect::class);
    }
    

    public function recentViews()
    {
        return $this->hasMany(RecentView::class);
    }

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
}