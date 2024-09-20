<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name'];

    /**
     * Relation entre le rôle et les utilisateurs
     * Un rôle peut appartenir à plusieurs utilisateurs.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
