<?php
namespace App\Repositories;

use App\Sprint;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class SprintRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Sprint());
    }
    
    public function findActiveSprint($milestoneId){
        $sprint = $this->model
                        ->where('milestone_id','=', $milestoneId)
                        ->where(function($q){
                                    $q->where('sprints.status', '=', "created")
                                        ->orWhere('sprints.status', '=', "assigned")
                                        ->orWhere('sprints.status', '=', "onhold")
                                        ->orWhere('sprints.status', '=', "inprogress");
                                });
        return $sprint;
    }
}