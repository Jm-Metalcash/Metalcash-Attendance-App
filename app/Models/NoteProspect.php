<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteProspect extends Model
{
    use HasFactory;

    protected $table = 'notes_prospect';

    protected $fillable = [
        'content',
        'note_date',
        'prospect_id',
        'type',
        'created_by',
        'updated_by',
        'note_content_status',
    ];

    protected $dates = ['note_date'];

    /**
     * Relation inverse avec la table prospects.
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by_relance');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
}
