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
        'type',
        'created_by',
        'updated_by',
        'note_content_status',
    ];

    protected $dates = ['note_date'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by_relance');
    }
}
