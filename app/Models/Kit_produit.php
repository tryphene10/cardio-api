<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Config;

class Kit_produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'published',
        'kit_id',
        'produit_id'
    ];
    
    public function produit(){
        return $this->belongsTo('App\Models\Produit','produit_id');
    }

    public function kit(){
        return $this->belongsTo('App\Models\Produit','kit_id');
    }

    public function element(){
        return $this->belongsTo('App\Models\Element','element_id');
    }


    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while (Kit_produit::where('ref',$token)->first() instanceof Kit_produit);
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
            }while(Kit_produit::where('alias',$alias)->first() instanceof Kit_produit);
            $this->attributes['alias'] = $alias;
        }
    }
}
