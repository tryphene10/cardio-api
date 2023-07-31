<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Config;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'published',
        'categorie_id',
        'designation',
        'description',
        'prix_produit',
        'qte'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function categorie(){
        return $this->belongsTo('App\Models\Categorie','categorie_id');
    }

    public function paniers(){
        return $this->hasMany('App\Models\Panier');
    }

    public function produitImgs(){
        return $this->hasMany('App\Models\Produit_img');
    }
    public function kit_produits(){
        return $this->hasMany('App\Models\Kit_produit');
    }

    public function long_stents(){
        return $this->hasMany('App\Models\Long_stent');
    }

    public function detail_paniers(){
        return $this->hasMany('App\Models\Detail_panier');
    }

    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while (Produit::where('ref',$token)->first() instanceof Produit);
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
            }while(Produit::where('alias',$alias)->first() instanceof Produit);
            $this->attributes['alias'] = $alias;
        }
    }
}
