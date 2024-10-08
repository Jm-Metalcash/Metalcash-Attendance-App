<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day', 'date', 'arrival', 'departure', 'break_start', 'break_end', 'total'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
