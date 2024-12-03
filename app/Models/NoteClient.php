<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteClient extends Model
{
    use HasFactory;

    protected $table = 'notes_client';

    protected $fillable = [
        'client_id',
        'content',
        'note_date',
        'status',
        'type'
    ];

    protected $dates = ['note_date'];

    // Relation inverse avec la table clients
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
