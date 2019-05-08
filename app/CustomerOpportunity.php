<?php

namespace App;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class CustomerOpportunity extends Model
{
    
    use LogTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dateFor', 'status', 'details', 'closeComment', 'wonComment', 'lostComment'
    ];
}
