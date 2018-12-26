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

    public function getWeekValidation($user_id=null, $week=null, $year=null, $id=null){
        $query =  $this->model;
        if($user_id!=null)
            $query =  $query->where('user_id','=',$user_id);
        if($week!=null)
            $query =  $query->where('weekNumber','=',$week);
          
        if($year != null)
            $query =  $query->where('entryYear','=',$year);      
          
        if($id != null)
            $query =  $query->where('id','=',$id);    
        
        return $query;
    }

    public function getWeekValidationWithProductiveHours($user_id=null, $week=null, $year=null){
        $week = $this->getWeekValidation($user_id, $week, $year)
                    ->leftJoin('work_time_tracks', 'work_time_tracks.week_number','=','week_validations.id');
                    // ->selectRaw('SUM(work_time_tracks.)')
    }
}