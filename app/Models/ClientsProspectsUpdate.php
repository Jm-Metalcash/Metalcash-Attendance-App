<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientsProspectsUpdate extends Model
{
    protected $table = 'clients_prospects_update';

    protected $fillable = [
        'updatable_type',
        'updatable_id',
        'user_id',
        'action',
    ];

    public function updatable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
