<?php
namespace App\Helpers;
use App\User;
use App\Project;
use App\TaskMember;
use App\ProjectTeam;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskMemberRepository;
use App\Repositories\ProjectTeamRepository;
use App\Repositories\NotificationRepository;

class HelperFunctions{
    public function getLastEmployeeId(){
        
        $user = User::max('employeeId');
        return $user+1;
    }

    public function getRoles(){
        $roles = [
            'admin', 
            'management', 
            'hr', 
            'team-lead', 
            'project-lead', 
            'employee'
        ];
        return $roles;
    }

    public function getModels(){
        $modules = [
            'project', 
            'milestone', 
            'sprint', 
            'task', 
            'project_team', 
            'client', 
            'user', 
            'my_task', 
            'time_sheet', 
            'settings', 
            'access_previlages',
            'activity_logs',
            'documents'
        ];
        return $modules;
    }

    public function updateProjectTeam($user_id, $project_id, $status, $id=null){
        try{
            \DB::transaction(function() use ($user_id, $project_id, $status, $id){
                $projectRepository = new ProjectRepository();
                $taskMemberRepository = new TaskMemberRepository();
                $teamRepository = new ProjectTeamRepository();
                $notificationRepository = new NotificationRepository();
                $status = "edit";
                if(empty($id)){
                    $team = $teamRepository->findByUserAndProject($user_id, $project_id);
                    if($team == null){
                        $team=new ProjectTeam();
                        $status = "new";
                        $directProjectTask = $projectRepository->getDirectProjectTask($project_id);
                        if($directProjectTask != null){
                            $taskMember =$taskMemberRepository->findByUserAndTask($user_id, $directProjectTask->id);
                            if($taskMember == null)
                                $taskMember = new TaskMember();
                            $taskMember->task_identification = $directProjectTask->id;
                            $taskMember->member_identification = $user_id;
                            $taskMember->save();
                        }
                    }
                }else {
                    $team=ProjectTeam::find($id);
                }
                $team->team_user_id=$user_id;
                $team->team_project_id=$project_id;
                $team->status=$status;
                $team->save();
                if($status == "new"){
                    $project = $projectRepository->show($project_id);
                    $projectType = 'project-team';
                    if($project->projectType == 'support')
                        $projectType = 'direct-project-team';
                    if($project->project_lead_id != $user_id){
                        $message = "You are assigned for a new project ".$project->projectName;
                        $notificationRepository->sendNotification(\Auth::user(), User::find($user_id), $message, $projectType, $project->id);
                    }
                }
                return $team;
            });
        }catch(\Exception $e){
            return $e;
        }
    }


    public function getStartAndEndDate($date)
    {
        $yearWeek=$this->getYearWeekNumber($date);
        $year=$yearWeek['year'];
        $week = $yearWeek['week'];
        $dto = new \DateTime();
        $dto->setISODate($year, $week);
        $date = new \Datetime($dto->format('Y-m-d'));
        $timeGaps[0] = $date;
        $dto->modify('+6 days');
        $timeGaps[1] = $dto;
        return $timeGaps;
    }

    public function getYearWeekNumber($date){
        $year=$date->format("Y");
        $week = $date->format("W");
        $month=$date->format("m");
        if($month == 12 && $week == 1) $year = (int)$year + 1;
        $res['year']=$year;
        $res['week']=$week;
        return $res;
    }

    public function getDateRange($fromDate, $toDate){
        $fromTime=strtotime($fromDate->format('d-m-Y'));
        $toTime=strtotime($toDate->format('d-m-Y'));
        $dates=[];
        while($fromTime <= $toTime){
            $dates[]=date("m-d-Y",$fromTime);
            $fromTime=strtotime(date('d-m-Y', strtotime('+1 day', $fromTime)));
        }
        return $dates;
    }

    public function getInternalProjectId($type = "internal"){
        $digits = ($type == "internal")?3:4;
        $prefix = ($type == "internal")?"IPR-":"PR-";
        $lastRecord=Project::where("projectCategory",'=',$type)->orderby('id', 'desc')->first();
        $currentCode = (($lastRecord)?(explode("-", $lastRecord->projectCode))[1]:0) + 1;
        return $prefix. str_pad($currentCode, $digits, "0", STR_PAD_LEFT) ."-".date("y");
    }

    public function timeToSec($time){
        $timeArray=explode(":", $time);
        
        $totalTime = $timeArray[0] * 3600;

        if(array_key_exists(1, $timeArray))
            $totalTime+= $timeArray[1]*60;
        if(array_key_exists(2, $timeArray))
            $totalTime+= $timeArray[2];
        return $totalTime;
      
    }

    public function timeConversion($time){
        $size = sizeof(explode(":",$time));
        if($size == 1)
            return $time.":00:00";
        if($size == 2)
            return $time.":00";
        return $time;
    }

    public function secToTime($seconds){
        $t = round($seconds);
        return sprintf('%d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
    }
 }
?>