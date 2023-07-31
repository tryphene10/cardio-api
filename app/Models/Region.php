<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    public function villes(){
        return $this->hasMany('App\Models\Ville');
    }

    public function pays(){
        return $this->belongsTo('App\Models\Pays','pay_id');
    }
}
