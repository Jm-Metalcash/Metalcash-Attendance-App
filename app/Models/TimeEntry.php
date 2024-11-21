<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day_id', 'type', 'time'];

    public function day()
    {
        return $this->belongsTo(Day::class);
    }



    protected static function boot()
    {
        parent::boot();

        static::created(function ($timeEntry) {
            $timeEntry->day->updateStatus();
        });

        static::updated(function ($timeEntry) {
            $timeEntry->day->updateStatus();
        });

        static::deleted(function ($timeEntry) {
            $timeEntry->day->updateStatus();
        });
    }
}

