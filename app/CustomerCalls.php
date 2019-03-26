<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerCalls extends Model
{
    protected $fillable = [
        'details', 'dateFor', 'status'
    ];
}
