<?php
namespace App\Helpers;
use App\Project;
use App\User;
use App\ProjectTeam;

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
            'client', 
            'user', 
            'task_work_log', 
            'settings', 
            'access_previlages'
        ];
        return $modules;
    }

    public function updateProjectTeam($user, $project, $status, $id=null){
        try{
            if(empty($id))
                $team=new ProjectTeam();
            else
                $team=ProjectTeam::find($id);
            $team->team_user_id=$user;
            $team->team_project_id=$project;
            $team->status=$status;
            $team->save();
            return $team;
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
        return (sizeof(explode(":",$time)) == 1)?$time.":00":$time;
    }

    public function secToTime($seconds){
        $t = round($seconds);
        return sprintf('%d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
    }
 }
?>