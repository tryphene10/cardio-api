<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Config;

class Evenement extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_image',
        'end',
        'begin',
        'description',
        'titre',
        'lieu_evenement',
        'published'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while (Evenement::where('ref',$token)->first() instanceof Evenement);
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
            }while(Evenement::where('alias',$alias)->first() instanceof Evenement);
            $this->attributes['alias'] = $alias;
        }
    }
    
}
