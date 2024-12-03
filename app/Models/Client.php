<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity',
        'docType',
        'docNumber',
        'docExp',
        'fullName',
        'familyName',
        'firstName',
        'birthDate',
        'address',
        'locality',
        'country',
        'email',
        'phone',
        'company',
        'companyvat',
        'interest',
        'referer',
        'regdate',
        'blacklist',
    ];
    
}
