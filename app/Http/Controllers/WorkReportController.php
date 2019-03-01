<?php

namespace App\Http\Controllers;

use App\Project;
use App\TaskMember;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;

class WorkReportController extends Controller
{
    public function departmentPerformance(Request $request) {
        $projectId = null;
        $milestoneId = null;
        $sprintId = null;
        $taskId = null;
        if(!empty($request->projectId)){
            $projectId = $request->projectId;
        }else if(!empty($request->milestoneId)){
            $milestoneId = $request->milestoneId;
        }else if(!empty($request->sprintId)){
            $sprintId = $request->sprintId;
        }else if(!empty($request->taskId)){
            $taskId = $request->taskId;
        }
        $helper = new HelperFunctions();
        $result=[];
        $taskMembers=$this->getTaskMembersDepartmentWise();
        foreach($taskMembers as $key => $taskMember){
            $estimatedTotal=0;
            $takenTotal=0;
            foreach($taskMembers[$key] as $member){
                $estimatedTotal+=$helper->timeToSec(( empty($member->estimatedHours))?"00:00:00":$member->estimatedHours ?? "00:00:00");
                $takenTotal+=$helper->timeToSec($member->takenHours ?? "00:00:00");
            }
            $result[$key]['takenHours']=$helper->secToTime($takenTotal);
            $result[$key]['estimatedHours']=$helper->secToTime($estimatedTotal);
        }
        return $result;
    }

    public function getTaskMembersDepartmentWise($taskId=null, $sprintId=null, $milestoneId=null, $projectId=null) {
        $user=\Auth::user();
        $taskMembers = TaskMember::leftJoin('users','users.id','=','task_members.member_identification')
                                    ->leftJoin('branch_departments', 'branch_departments.id', '=', 'users.branch_dept_id')
                                    ->leftJoin('mass_parameters', 'mass_parameters.id', '=', 'branch_departments.dept_id')
                                    ->where('users.company_id','=',$user->company_id)
                                    ->select('task_members.id', 'task_members.estimatedHours', 'task_members.takenHours', 'task_members.takenHours','mass_parameters.title as dept');
        if($taskId != null){
            $taskMembers->where('task_identification',$taskId);
        }else if($sprintId != null) {
            $taskMembers=$taskMembers->leftJoin('tasks','tasks.id','=','task_identification')
                                        ->where('tasks.sprint_id',$sprintId);
        }else if($milestoneId != null) {
            $taskMembers=$taskMembers->leftJoin('tasks','tasks.id','=','task_identification')
                                     ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                                     ->where('sprints.milestone_id',$milestoneId);
        }else if($projectId != null) {
            $taskMembers=$taskMembers->leftJoin('tasks','tasks.id','=','task_identification')
                                     ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                                     ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
                                     ->where('milestones.project_milestone_id',$projectId);
        }

        $taskMembers=$taskMembers->get();
        return $taskMembers->groupBy('dept');
    }
}
