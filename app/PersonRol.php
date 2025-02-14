<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonRol extends Model
{
    protected $table = 'persons_rols';

    protected $fillable = ['person_id', 'rol_id'];

    public $timestamps = false;
}
