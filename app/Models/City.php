<?php

namespace App\Models;

use App\Models\Arrondissement;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name'];

    public function arrondissements()
    {
        return $this->hasMany(Arrondissement::class);
    }
}
