<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessPrevileges extends Model
{
    protected $fillable = [
        'module_name',
        'roles',
        'access_previlage'
    ];
}
