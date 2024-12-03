<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentView extends Model
{
    protected $fillable = [
        'user_id',
        'prospect_id',
        'client_id',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
