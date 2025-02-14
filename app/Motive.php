<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Motive extends Model
{
    public $timestamps = false;

    protected $table = 'motives';

    protected $fillable = [

        'motive'
    ];
}
