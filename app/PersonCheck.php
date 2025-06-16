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

        'moment_enter',

        'moment_exit',

        'motive_id',

        'note',

        'division_id',

        'url_screen'
    ];

    protected $casts = [
        'moment' => 'datetime',
        'moment_enter' => 'datetime',
        'moment_exit' => 'datetime',
    ];

    public $timestamps = false;

    public function getMomentAttribute($value)
    {
        return $value !== null ? date('d/m/Y H:i', strtotime($value)) : null;
    }

    public function motive()
    {
        return $this->belongsTo(Motive::class, 'motive_id');
    }

}
