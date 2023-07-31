<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Helpers\CustFunc;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'phone',
        'email',
        'password',
        'published',
        'ville_id',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',

    ];


    public function validatePassword($value)
    {
        $toReturn = [
            'success'=>false,
            'message'=>""
        ];
        if(empty($this->attributes['password']))
        {
            $toReturn['message']    = trans('messages.password.invalid.empty');
            return $toReturn;
        }
        if(!Hash::check($value, $this->attributes['password']))
        {
            $toReturn['message']    = trans('messages.password.invalid.default');
            return $toReturn;
        }

        $toReturn['success'] = true;
        return $toReturn;
    }


    public function isPublished()
    {
        return $this->published;
    }

    public function role(){
        return $this->belongsTo('App\Models\Role','role_id');
    }

    public function ville(){
        return $this->belongsTo('App\Models\Ville','ville_id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function commandes(){
        return $this->hasMany('App\Models\Commande');
    }

    public function produits(){
        return $this->hasMany('App\Models\Produit');
    }

    public function evenements(){
        return $this->hasMany('App\Models\Evenement');
    }

    public function generateAlias($name)
    {
        $append = Config::get('constants.values.zero');
        if(empty($this->attributes['alias']))
        {
            do
            {
                if($append == Config::get('constants.values.zero'))
                {
                    $alias = CustFunc::toAscii($name);
                }else
                {
                    $alias = CustFunc::toAscii($name)."-".$append;
                }

                $append   += 1;
            }while
            (
                User::where('alias',$alias)->first() instanceof User
            );

            $this->attributes['alias'] = $alias;
        }
    }

    public function generateReference()
    {

        if(empty($this->attributes['ref']))
        {
            do
            {
                $token = CustFunc::getToken(Config::get('constants.size.ref.user'));
            }
            while
            (
                User::where('ref',$token)->first() instanceof User
            );

            $this->attributes['ref'] = $token;

            return true;
        }
        return false;
    }

    public function generateCode() {
        if(empty($this->attributes['activation_code'])) {
            do {
                $code = CustFunc::getToken(Config::get('constants.size.code.ticket'));
            }
            while (User::where('activation_code', $code)->first() instanceof User);
            $this->attributes['activation_code'] = $code;
            return true;
        }
        return false;
    }

}
