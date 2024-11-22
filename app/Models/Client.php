<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'fullName',
        'familyName',
        'firstName',
        'address',
        'locality',
        'postalCode',
        'country',
        'email',
        'phone',
        'blacklist',
        'recently_added',
        'company',
        'companyvat',
        'regdate',
        'entity',
        'docType',
        'docNumber',
        'docExp',
        'interest',
        'referer',
        'birthDate',
        'created_by',
        'updated_by',
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
