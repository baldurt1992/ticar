<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
   public $timestamps = false;

   protected $table = 'divisions';

   protected $fillable = [

        'company_id',

        'names',

        'address',

        'phone',

        'email',

        'workbegin',

        'workend',

        'status_id',
   ];

    public function Status()
    {
        return $this->hasOne('App\PersonStatus', 'id', 'status_id');

    }
}
