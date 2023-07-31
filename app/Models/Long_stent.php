<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Config;

class Long_stent extends Model
{
    use HasFactory;

    public function long(){
        return $this->belongsTo('App\Models\Long','long_id');
    }

    public function stent(){
        return $this->belongsTo('App\Models\Stent','stent_id');
    }

    public function produit(){
        return $this->belongsTo('App\Models\Produit','produit_id');
    }

    public function paniers(){
        return $this->hasMany('App\Models\Panier');
    }

    public function detail_paniers(){
        return $this->hasMany('App\Models\Detail_panier');
    }

    /*public function values(){
        return $this->hasMany('App\Models\Value');
    }*/

    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while (Long_stent::where('ref',$token)->first() instanceof Long_stent);
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
            }while(Long_stent::where('alias',$alias)->first() instanceof Long_stent);
            $this->attributes['alias'] = $alias;
        }
    }
}
