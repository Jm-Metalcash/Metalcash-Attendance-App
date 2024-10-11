<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'note_date', 'client_id'];

    protected $dates = ['note_date'];

    // Relation inverse avec la table clients
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

