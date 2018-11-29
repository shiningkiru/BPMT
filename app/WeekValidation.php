<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeekValidation extends Model
{
    protected $fillable = [
        'status', 'request_time', 'accept_time'
    ];
}
