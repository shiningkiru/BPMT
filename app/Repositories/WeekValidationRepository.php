<?php
namespace App\Repositories;

use App\WeekValidation;
use Illuminate\Http\Request;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class WeekValidationRepository extends Repository {

    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new WeekValidation());
    }

    public function getWeekValidation($user_id, $week, $year){
        return WeekValidation::where('user_id','=',$user_id)->where('weekNumber','=',$week)->where('entryYear','=',$year)->first();        
    }
}