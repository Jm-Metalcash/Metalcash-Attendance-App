<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'note_date', 'prospect_id', 'type'];

    protected $dates = ['note_date'];

    // Relation inverse avec la table prospects
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
