<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'zip_code',
        'last_latitude',
        'last_longitude',
    ];

    /**
     * The barbecue reserved by a user.
     */
    public function reserves()
    {
        return $this->belongsToMany('App\Bbq')->using('App\BbqUser')
        ->withPivot( 'id', 'bbq_id', 'user_id', 'reserved_from', 'reserved_to');
    }

    /**
     * Get the user's barbecues.
     */
    public function barbecues()
    {
        return $this->hasMany('App\Bbq');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'password', 'remember_token', 'created_at', 'updated_at'
    ];
}
