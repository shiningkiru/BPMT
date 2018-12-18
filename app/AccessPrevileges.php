<?php

namespace App;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class AccessPrevileges extends Model
{
    use LogTrait;
    protected $fillable = [
        'module_name',
        'roles',
        'access_previlage'
    ];
}
