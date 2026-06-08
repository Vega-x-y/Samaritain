<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artisan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'profile_image',
        'contact_info',
    ];
}
