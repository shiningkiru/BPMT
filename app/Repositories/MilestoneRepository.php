<?php
namespace App\Repositories;

use App\Milestones;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class MilestoneRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Milestones());
    }
    
    public function findActiveMilestone($projectId){
        $milestone = $this->model
                        ->where('project_milestone_id','=', $projectId)
                        ->where(function($q){
                                    $q->where('milestones.status', '=', "created")
                                        ->orWhere('milestones.status', '=', "assigned")
                                        ->orWhere('milestones.status', '=', "onhold")
                                        ->orWhere('milestones.status', '=', "inprogress");
                                });
        return $milestone;
    }
}