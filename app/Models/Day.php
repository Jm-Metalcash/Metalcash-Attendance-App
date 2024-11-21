<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day', 'date', 'total', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }



    // Méthode pour mettre à jour le statut
    public function updateStatus()
    {
        // Récupérer toutes les entrées de temps pour ce jour, triées par heure
        $timeEntries = $this->timeEntries()->orderBy('time')->get();

        $balance = 0; // Balance des entrées et sorties

        foreach ($timeEntries as $entry) {
            if ($entry->type == 'arrival') {
                $balance += 1;
            } elseif ($entry->type == 'departure') {
                $balance -= 1;
            }
        }

        // Si la balance est positive, il y a une arrivée sans sortie correspondante
        $status = $balance > 0 ? 1 : 0;

        // Mettre à jour le statut
        $this->status = $status;
        $this->save();
    }
}
