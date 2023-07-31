<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Config;

class Commande extends Model
{
    use HasFactory;

    public $fillable = [
        'statut_cmd_id',
        'user_client_id',
        'user_gestionnaire_id',
        'suivi_cmd_id',
        'user_livreur_id'

    ];

    public function user_client(){
        return $this->belongsTo('App\Models\User','user_client_id');
    }

    public function user_livreur(){
        return $this->belongsTo('App\Models\User','user_livreur_id');
    }

    public function user_gestionnaire(){
        return $this->belongsTo('App\Models\User','user_gestionnaire_id');
    }

    public function statut_cmd(){
        return $this->belongsTo('App\Models\Statut_cmd','statut_cmd_id');
    }

    public function suivi(){
        return $this->belongsTo('App\Models\Suivi','suivi_id');
    }

    public function ville(){
        return $this->belongsTo('App\Models\Ville','ville_id');
    }

    public function paniers(){
        return $this->hasMany('App\Models\Panier');
    }

    /*public function values(){
        return $this->hasMany('App\Models\Value');
    }*/

    public function transactions(){
        return $this->hasMany('App\Models\Transaction');
    }

    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while ( Commande::where('ref',$token)->first() instanceof Commande);
            $this->attributes['ref'] = $token;

            return true;
        }
        return false;
    }

    //To generate an alias for the object based on the name of that object.
    public function generateAlias($name){
        $append = Config::get('constants.values.zero');
        if(empty($this->attributes['alias'])){
            do{
                if($append == Config::get('constants.values.zero')){
                    $alias = CustFunc::toAscii($name);
                }else{
                    $alias = CustFunc::toAscii($name)."-".$append;
                }
                $append += Config::get('constants.values.one');
            }while(Commande::where('alias',$alias)->first() instanceof Commande);
            $this->attributes['alias'] = $alias;
        }
    }
}
