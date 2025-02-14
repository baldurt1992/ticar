<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';

    public $timestamps = false;

    protected $fillable = [

        'names',

        'address',

        'phone',

        'email',

        'city',

        'town',

        'zip',

    ];
}
