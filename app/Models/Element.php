<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Config;

class Element extends Model
{
    use HasFactory;

    protected $fillable = [
        'published','name'
    ];

    public function kit_produits(){
        return $this->hasMany('App\Models\Kit_produit');
    }

    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while (Element::where('ref',$token)->first() instanceof Element);
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
            }while(Element::where('alias',$alias)->first() instanceof Element);
            $this->attributes['alias'] = $alias;
        }
    }
}
