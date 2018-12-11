<?php
namespace App\Repositories;

use App\Tasks;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class TasksRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Tasks());
    }

    public function findWeeklyWorkingTasks(\Datetime $fromDate, \Datetime $toDate, $project=null, $user=null){
        $tasks = $this->model->leftJoin('task_members','task_members.task_identification','=','tasks.id')
                        ->leftJoin('work_time_tracks','work_time_tracks.task_member_identification','=','task_members.id')
                        ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                        ->leftJoin('milestones','milestones.id','=','sprints.milestone_id');
        //according to single project
        if($project != null)
            $tasks = $tasks->where('milestones.project_milestone_id','=',$project);
        //according to single user
        if($user != null)
            $tasks = $tasks->where('task_members.member_identification','=',$user);

        $tasks = $tasks->whereBetween('work_time_tracks.dateOfEntry', [$fromDate, $toDate])
                        ->select('tasks.id', 'tasks.taskName')
                        ->get();
        
        return $tasks;
    }
}