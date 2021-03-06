<?php
namespace App\Repositories;

use App\Project;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class ProjectRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Project());
    }

    public function findWeeklyWorkingProjects(\Datetime $fromDate, \Datetime $toDate, $user=null, $project_lead_id=null){
        
        $projects = $this->model->leftJoin('milestones','milestones.project_milestone_id', '=', 'projects.id')
                            ->leftJoin('sprints','sprints.milestone_id','=','milestones.id')
                            ->leftJoin('tasks','tasks.sprint_id','=','sprints.id')
                            ->leftJoin('task_members','task_members.task_identification','=','tasks.id')
                            ->leftJoin('work_time_tracks','work_time_tracks.task_member_identification','=','task_members.id');
        
        if($user != null)
            $projects=$projects->where('task_members.member_identification','=',$user);

        if($project_lead_id != null)
            $projects=$projects->where('projects.project_lead_id','=',$project_lead_id);
        $projects=$projects->whereBetween('work_time_tracks.dateOfEntry', [$fromDate, $toDate])
                        ->select('projects.id','projects.projectName', 'projects.projectCode', 'projects.project_lead_id')
                        ->distinct('projects.id')
                        ->get();
        return $projects;
    }

    public function updateProjectStatus($projectId, $status){
        try{
            $project=$this->model->find($projectId);
            $project->status=$status;
            $project->save();
            return $project;
        }catch(\Exception $e){
            return Response::json(['errors'=>['server'=>[$e->getMessage()]]], 422);
        }
    }

    public function getDirectProjectTask($project_id){
        $project=$this->model->find($project_id);
        if((!($project instanceof Project)) || $project->projectType != 'support')return null;
        return $project->milestones()->first()->sprints()->first()->tasks()->first();
    }
}