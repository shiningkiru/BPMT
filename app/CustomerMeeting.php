<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerMeeting extends Model
{
    protected $fillable = [
        'details', 'dateFor', 'status'
    ];
}
