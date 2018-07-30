<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Bbq extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'image',
        'model',
        'latitude',
        'longitude',
    ];

    /**
     * The users that belong the reserved barbecue.
     */
    public function reserve()
    {
        return $this->belongsToMany('App\User')->using('App\BbqUser');
    }

    /**
     * Get the user that owns the barbecue.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getImageAttribute($value)
    {
        return !is_null($value) ? Storage::url('public/uploads/barbecues/' . $value) : null;
    }

}
