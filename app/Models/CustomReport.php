<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomReport extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'columns',
        'filters',
        'emails',
        'format',
        'schedule',
        'custom_day',
        'custom_time',
        'cron',
        'active',
        'last_sent_at'
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'emails' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
