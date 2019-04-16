<?php

namespace App;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class GlobalTask extends Model
{
    use LogTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projectCode', 'title', 'description', 'isActive'
    ];
}
