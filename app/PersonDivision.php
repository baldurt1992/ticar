<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonDivision extends Model
{
    protected $table = 'persons_divisions';

    protected $fillable = ['person_id', 'division_id'];

    public $timestamps = false;
}
