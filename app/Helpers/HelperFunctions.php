<?php
namespace App\Helpers;

use App\User;

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
}
?>