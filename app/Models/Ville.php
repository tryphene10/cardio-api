<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    use HasFactory;

    public function commandes(){
        return $this->hasMany('App\Models\Commande');
    }

    public function user_clients(){
        return $this->hasMany('App\Models\User');
    }

    public function region(){
        return $this->belongsTo('App\Models\Region','region_id');
    }
}
