<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Config;


class Detail_panier extends Model
{
    use HasFactory;
 
    public function long_stent(){
        return $this->belongsTo('App\Models\Long_stent','long_stent_id');
    }

    public function produit(){
        return $this->belongsTo('App\Models\Produit','produit_id');
    }
    
    public function panier(){
        return $this->belongsTo('App\Models\Panier','panier_id');
    }

    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while (Detail_panier::where('ref',$token)->first() instanceof Detail_panier);
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
            }while(Detail_panier::where('alias',$alias)->first() instanceof Detail_panier);
            $this->attributes['alias'] = $alias;
        }
    }


}
