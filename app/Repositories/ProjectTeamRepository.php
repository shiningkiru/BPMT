<?php
namespace App\Repositories;

use App\ProjectTeam;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class ProjectTeamRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new ProjectTeam());
    }

    public function findByUserAndProject($user_id, $project_id){
        $projectTeam = $this->model->where('team_project_id', '=', $project_id)->where('team_user_id', '=', $user_id)->first();
        if(!$projectTeam instanceof ProjectTeam)
            return null;
        return $projectTeam;
    }
}