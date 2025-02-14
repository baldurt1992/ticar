<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
   public $timestamps = false;

   protected $table = 'persons';

   protected $fillable = [

        'names',

        'token',

        'address',

        'phone',

        'email',

        'end_at',

        'created_at',

        'status_id',

        'pic'
   ];

    public function Rols()
    {
        return $this->hasMany('App\PersonRol', 'person_id', 'id');

    }

    public function Divisions()
    {
        return $this->hasMany('App\PersonDivision', 'person_id', 'id');

    }

    public function Checks()
    {
        return $this->hasMany('App\PersonCheck', 'person_id', 'id');

    }

    public function Status()
    {
        return $this->hasOne('App\PersonStatus', 'id', 'status_id');

    }
}
