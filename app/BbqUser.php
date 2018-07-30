<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BbqUser extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'bbq_id',
        'reserved_from',
        'reserved_to',
    ];
}
