<?php
namespace App\Helpers;
use App\User;
use App\Sprint;
use App\Project;
use App\Customer;
use App\Milestones;
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
            'customer', 
            'calendar', 
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
                $localstatus = "edit";
                if(empty($id)){
                    $team = $teamRepository->findByUserAndProject($user_id, $project_id);
                    if($team == null){
                        $team=new ProjectTeam();
                        $localstatus = "new";
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
                if($localstatus == "new"){
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

    public function getMonthStartEndDate($date){
        $date=new \Datetime($date);
        $first_day_this_month = $date->format('Y-m-01'); // hard-coded '01' for first day
        $last_day_this_month  = $date->format('Y-m-t');
        $gap[0]=new \Datetime($first_day_this_month);
        $gap[1]=new \Datetime($last_day_this_month);
        return $gap;
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

    public function getCustomerNumber(){
        $digits = 5;
        $prefix = "CUST-";
        $lastRecord=Customer::orderby('id', 'desc')->first();
        $currentCode = (($lastRecord)?(explode("-", $lastRecord->customerNumber))[1]:0) + 1;
        $flag = true;
        $number=$prefix."00001";
        while($flag){
            $number = $prefix. str_pad($currentCode, $digits, "0", STR_PAD_LEFT);
            $check = Customer::where('customerNumber','=',$number)->first();
            if(!($check instanceof Customer)){
                $flag=false;
            }
        }
        return $number;
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
        $timeHMS = explode(":",$time);
        $size=sizeof($timeHMS);
        if($size == 1){
            return str_pad($time,2,"0",STR_PAD_LEFT).":00:00";
        }else if($size == 2){
            return str_pad($timeHMS[0],2,"0",STR_PAD_LEFT).":".str_pad($timeHMS[1],2,"0",STR_PAD_LEFT).":00";
        }
        return str_pad($timeHMS[0],2,"0",STR_PAD_LEFT).":".str_pad($timeHMS[1],2,"0",STR_PAD_LEFT).":".str_pad($timeHMS[2],2,"0",STR_PAD_LEFT);
    }

    public function secToTime($seconds){
        $t = round($seconds);
        return sprintf('%d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
    }

    public function sprintTakenHourUpdate($sprintId){
        $sprint = Sprint::find($sprintId);
        $tasks = $sprint->tasks;
        $total = 0;

        foreach($tasks as $task){
            $total+=$this->timeToSec($task->takenHours ?? 00);
        }
        $sprint->takenHours=$this->timeConversion($this->secToTime($total));
        $sprint->save();
        $this->milestoneTakenHoursUpdate($sprint->milestone_id);
    }

    public function milestoneTakenHoursUpdate($milestoneId){
        $milestone = Milestones::find($milestoneId);
        $sprints=$milestone->sprints;
        $total=0;
        foreach($sprints as $sprint){
            $total+=$this->timeToSec($sprint->takenHours ?? 00);
        }
        $milestone->takenHours=$this->timeConversion($this->secToTime($total));
        $milestone->save();
        $this->projectTakenHoursUpdate($milestone->project_milestone_id);
    }

    public function projectTakenHoursUpdate($projectId){
        $project = Project::find($projectId);
        $milestones=$project->milestones;
        $total=0;
        foreach($milestones as $milestone){
            $total+=$this->timeToSec($milestone->takenHours ?? 00);
        }
        $project->takenHours=$this->timeConversion($this->secToTime($total));
        $project->save();
    }
}

?>