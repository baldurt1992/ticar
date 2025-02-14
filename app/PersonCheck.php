<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PersonCheck extends Model
{
    protected $table = 'persons_checks';

    protected $fillable = [

        'check_ip',

        'person_id',

        'moment',

        'motive_id',

        'note',

        'division_id',

        'url_screen'
    ];

    public $timestamps = false;

   public function getMomentAttribute($value)
    {
        return $value !== null ?  date('d/m/Y H:i', strtotime($value)) : null;
    }

    public function Motives()
    {
        return $this->hasMany('App\Motive', 'id', 'motive_id');

    }
}
