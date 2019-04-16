<?php

namespace App\Http\Controllers;

use App\User;
use App\Tasks;
use Validator;
use App\Project;
use App\GlobalTask;
use App\WorkTimeTrack;
use App\WeekValidation;
use Illuminate\Http\Request;
use App\WeekValidationProject;
use App\Helpers\HelperFunctions;

class MyTaskController extends Controller
{
    public function getMyTask(Request $request) {

        $helper = new HelperFunctions();
        $valid = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'week_validation_id' => 'nullable|exists:week_validations,id',
            'weekNumber' => 'nullable|integer|between:1,53',
            'year' => 'nullable|integer|between:2017,2030',
            'approvalType' => 'required|in:project-lead,team-lead,my-task,admin',
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);
        $teamLead=false;

        //declarations
        $user = \Auth::user();
        $currentUser = clone $user;
        $userSubmission="submit";

        if($request->approvalType == 'project-lead' || $request->approvalType == 'team-lead' || $request->approvalType == 'admin') {
            if(!empty($request->user_id)){
                $user = User::find($request->user_id);
            }
        }
    
        if(empty($request->weekNumber) || empty($request->year)){
            $week = $helper->getYearWeekNumber(new \Datetime());
            $weekNumber=$week['week'];
            $year = $week['year'];
        }else {
            $weekNumber = $request->weekNumber;
            $year = $request->year;
        }

        if(!empty($request->week_validation_id)){
            $weekValidation = WeekValidation::find($request->week_validation_id);
            $weekNumber = $weekValidation->weekNumber;
            $year = $weekValidation->entryYear;
            $user = $weekValidation->user;
        }


        $columnTotals=[];
        $globalTasks=[];

        if($currentUser->id == $user->team_lead && $request->approvalType == 'team-lead'){
            $teamLead =true;
        }

        //find start and end date
        $timeGap = $helper->getStartAndEndDateByWeekNumber($weekNumber, $year);
        $weekValidation = WeekValidation::where('weekNumber', '=', $weekNumber)->where('entryYear', '=', $year)->where('user_id', '=', $user->id)->first();
        if(!($weekValidation instanceof WeekValidation)){
            $weekValidation= new WeekValidation();
            $dateGap=$helper->getStartAndEndDateByWeekNumber($weekNumber, $year);
            $weekValidation->weekNumber=$weekNumber;
            $weekValidation->entryYear=$year;
            $weekValidation->user_id=$user->id;
            $weekValidation->status="entried";
            $weekValidation->startDate = $dateGap[0];
            $weekValidation->endDate = $dateGap[1];
            $weekValidation->save();
            $weekValidation=WeekValidation::find($weekValidation->id);
        }

        if($weekValidation->status != 'entried'){
            $userSubmission="none";
        }

        if(($request->approvalType != 'project-lead' && $request->approvalType == 'team-lead') || $request->approvalType == 'my-task'|| $request->approvalType == 'admin') {

            //global task system
            $globalTasks = GlobalTask::leftJoin('global_task_users','global_task_users.global_task_id', '=', 'global_tasks.id')
                                        ->where('global_task_users.user_id', '=', $user->id)
                                        ->where('global_tasks.isActive','=',true)
                                        ->select('global_tasks.id', 'global_tasks.projectCode', 'global_tasks.title', 'global_tasks.description', 'global_task_users.id as guserId')
                                        ->get();
            foreach($globalTasks as $gtasks){
                $workTrack=[];
                $totalSeconds = 0;
                if($weekValidation instanceof WeekValidation){
                    $tracks = WorkTimeTrack::where('global_task_user_id', '=', $gtasks->guserId)
                                            ->where('work_time_tracks.week_number', '=', $weekValidation->id)
                                            ->get();
                    foreach($tracks as $track){
                        $date = new \Datetime($track->dateOfEntry);
                        $workTrack[$date->format('m-d-Y')]=$track;
                        $totalSeconds = $totalSeconds + $helper->timeToSec($track->takenHours);
                        $columnTotals[$date->format('m-d-Y')]=($columnTotals[$date->format('m-d-Y')] ?? 0) + $helper->timeToSec($track->takenHours);
                    }
                }
                $gtasks['workTrackTotal']=$helper->secToTime($totalSeconds);
                $gtasks['workTrack']=$workTrack;
            }
        }
        $result['gtask'] = $globalTasks;
        // return $user->id;
        //find the project in which tasks assigned which comes in middle of this date
        $projects = Project::leftJoin('milestones', 'projects.id', '=', 'milestones.project_milestone_id')
                            ->leftJoin('sprints', 'milestones.id', '=', 'sprints.milestone_id')
                            ->leftJoin('tasks', 'sprints.id', '=', 'tasks.sprint_id')
                            ->leftJoin('task_members', 'tasks.id', '=', 'task_members.task_identification')
                            ->where('task_members.member_identification', '=', $user->id)
                            ->where(function($query) use ($timeGap) {
                                $query->whereBetween('tasks.startDate', $timeGap)
                                    ->orWhereBetween('tasks.endDate', $timeGap)
                                    ->orWhere(function($query) use ($timeGap){
                                        $query->where('tasks.startDate' , '<=', $timeGap[0])
                                            ->where('tasks.endDate' , '>=', $timeGap[0]);
                                    })
                                    ->orWhere(function($query) use ($timeGap){
                                        $query->where('tasks.startDate' , '<=', $timeGap[1])
                                            ->where('tasks.endDate' , '>=', $timeGap[1]);
                                    });
                                    
                            });
        
        if($request->approvalType == 'project-lead' && ($currentUser->roles == 'project-lead' || $currentUser->roles == 'management')) {
            $projects = $projects->where('project_lead_id', '=', $currentUser->id);
        }

        
        $projects = $projects->select('projects.*')
                            ->distinct('projects.*')
                            ->get();
        //find the current tasks which are available in each project
        $projectLeadSubmission =true;
        $teamLeadSubmission=true;

        foreach($projects as $project){
            $weekValidationProject = WeekValidationProject::leftJoin('users', 'users.id', '=', 'week_validation_projects.accepted_user_id')
                                                            ->where('project_id', '=', $project->id)
                                                            ->where('week_validation_id', '=', $weekValidation->id)
                                                            ->select('week_validation_projects.id', 'week_validation_projects.status', 'week_validation_projects.accept_time', 'users.id as acceptedUserId', 'users.firstName', 'users.lastName')
                                                            ->first();
            if($weekValidationProject instanceof WeekValidationProject){
                if($weekValidationProject->status == 'accepted' || $weekValidationProject->status == 'entried'){
                    $projectLeadSubmission=false;
                }

                if($weekValidationProject->status== "plead-reassigned"){
                    $projectLeadSubmission=false;
                    $teamLeadSubmission=false;
                    $userSubmission="resubmit";
                }
                
                if($weekValidationProject->status== "reassigned"){
                    $projectLeadSubmission=true;
                }

                if($weekValidationProject->status != 'accepted') {
                    $teamLeadSubmission=false;
                }
            }
            $project['weekValidationProject'] = $weekValidationProject;
            $tasks = Tasks::leftJoin('sprints', 'sprints.id', '=', 'tasks.sprint_id')
                            ->leftJoin('milestones', 'milestones.id', '=', 'sprints.milestone_id')
                            ->leftJoin('task_members', 'tasks.id', '=', 'task_members.task_identification')
                            ->where('milestones.project_milestone_id', '=', $project->id)
                            ->where('task_members.member_identification', '=', $user->id)
                            ->where(function($query) use ($timeGap) {
                                $query->whereBetween('tasks.startDate', $timeGap)
                                    ->orWhereBetween('tasks.endDate', $timeGap)
                                    ->orWhere(function($query) use ($timeGap){
                                        $query->where('tasks.startDate' , '<=', $timeGap[0])
                                            ->where('tasks.endDate' , '>=', $timeGap[0]);
                                    })
                                    ->orWhere(function($query) use ($timeGap){
                                        $query->where('tasks.startDate' , '<=', $timeGap[1])
                                            ->where('tasks.endDate' , '>=', $timeGap[1]);
                                    });
                                    
                            })
                            ->select('tasks.*','sprints.sprintTitle', 'task_members.estimatedHours as assignedHours', 'task_members.takenHours as youTaken')
                            ->distinct('tasks.*','sprints.sprintTitle', 'task_members.estimatedHours', 'task_members.takenHours')
                            ->get();
            
            //find current week validations
            $taskCount = sizeof($tasks);
            foreach($tasks as $task){
                $workTrack = [];
                $totalSeconds = 0;
                if($weekValidation instanceof WeekValidation) {
                    $tracks = WorkTimeTrack::leftJoin('task_members', 'work_time_tracks.task_member_identification', '=', 'task_members.id')
                                                ->where('task_members.task_identification', '=', $task->id)
                                                ->where('week_number', '=', $weekValidation->id)
                                                ->select('work_time_tracks.*')
                                                ->get();
                    foreach($tracks as $track){
                        $date = new \Datetime($track->dateOfEntry);
                        $workTrack[$date->format('m-d-Y')]=$track;
                        $totalSeconds = $totalSeconds + $helper->timeToSec($track->takenHours);
                        $columnTotals[$date->format('m-d-Y')]=($columnTotals[$date->format('m-d-Y')] ?? 0) + $helper->timeToSec($track->takenHours);
                    }
                }
                $task['workTrackTotal']=$helper->secToTime($totalSeconds);
                $task['workTrack'] = $workTrack;
            }
            $tasks[0]['taskCount'] = $taskCount;
            $project['tasks'] = $tasks;

        }
        $grandTotal=0;
        foreach($columnTotals as $key => $total){
            $grandTotal+=$total;
            $columnTotals[$key] = $helper->secToTime($total);
        }

        if(($weekValidation->status != 'requested' && $weekValidation->status != 'reassigned') || $request->approvalType != 'team-lead'){
            $teamLeadSubmission=false;
        }

        if($weekValidation->status == 'reassigned' || $weekValidation->status == 'accepted') {
            $projectLeadSubmission=false;
        }

        if($request->approvalType == 'admin' || $request->approvalType == 'my-task' ){
            $teamLead=false;
            $teamLeadSubmission=false;
            $projectLeadSubmission=false;
        } else if( $request->approvalType == 'team-lead') {
            $projectLeadSubmission = false;
        }else if( $request->approvalType == 'project-lead') {
            $teamLeadSubmission = false;
        }

        $userDetail = User::leftJoin('mass_parameters as designation_t', 'designation_t.id', 'users.designation_id')
                        ->leftJoin('branch_departments', 'branch_departments.id', '=', 'users.branch_dept_id')
                        ->leftJoin('branches', 'branches.id', '=', 'branch_departments.branches_id')
                        ->leftJoin('mass_parameters as department_tb', 'department_tb.id', '=', 'branch_departments.dept_id')
                        ->leftJoin('users as team_lead', 'team_lead.id', '=', 'users.team_lead')
                        ->where('users.id', '=', $user->id)
                        ->select('users.id', 'users.email', 'users.employeeId', 'users.profilePic', \DB::raw('CONCAT(users.firstName, " ", users.lastName) as fullName'), 'users.mobileNumber', 'designation_t.title as designation', 'department_tb.title as department', 'branches.branchName', \DB::raw('CONCAT(team_lead.firstName, " ", team_lead.lastName) as leadName'))
                        ->distinct('users.id', 'users.firstName', 'users.lastName', 'users.email', 'users.employeeId', 'users.profilePic', 'users.mobileNumber', 'designation_t.title', 'department_tb.title', 'branches.branchName', 'team_lead.firstName','team_lead.lastName')
                        ->first();
        $result['dates'] = $helper->getDateRange($timeGap[0], $timeGap[1]);
        $result['weekValidation'] = $weekValidation;
        $result['projects'] = $projects;
        $result['projectLeadSubmission']=$projectLeadSubmission;
        $result['teamLeadSubmission']=$teamLeadSubmission;
        $result['teamLead']=$teamLead;
        $result['columnTotals'] = $columnTotals;
        $result['grandTotal'] = $helper->secToTime($grandTotal);
        $result['weekNumber']=$weekNumber;
        $result['year']=$year;
        $result["userSubmission"]=$userSubmission;
        $result['user']=$userDetail;
        return $result;
    }

    public function getProjectLeadSubmittedPttUsers(Request $request){
        $user = \Auth::user();

        $users = User::leftJoin('project_teams', 'users.id', '=', 'project_teams.team_user_id')
                        ->leftJoin('projects', 'projects.id', '=', 'project_teams.team_project_id')
                        ->where('projects.project_lead_id', '=', $user->id)
                        ->where('users.id', '<>', $user->id)
                        ->select('users.id', \DB::raw('CONCAT(users.firstName, " ", users.lastName) as fullName'))
                        ->distinct('users.id', 'users.firstName', 'users.lastName')
                        ->get();
        foreach($users as $usr) {
            $weekValidations = WeekValidation::leftJoin('week_validation_projects', 'week_validation_projects.week_validation_id', '=', 'week_validations.id')
                                                ->leftJoin('projects', 'projects.id', '=', 'week_validation_projects.project_id')
                                                ->where('week_validations.status','=', 'requested')
                                                ->where(function($query) {
                                                    $query->where('week_validation_projects.status', '=' ,'requested')
                                                            ->orWhere('week_validation_projects.status', '=' ,'reassigned');
                                                })
                                                ->where('projects.project_lead_id', '=', $user->id)
                                                ->where('week_validations.user_id', '=', $usr->id)
                                                ->select('week_validations.*')
                                                ->distinct('week_validations.*')
                                                ->orderBy('week_validations.weekNumber', 'DESC')
                                                ->orderBy('week_validations.entryYear', 'DESC')
                                                ->get();
            $usr['weekValidation'] = $weekValidations;
        }
        return $users;
    }

    public function getTeamLeadSubmittedPttUsers(Request $request){
        $user = \Auth::user();

        $users = User::where('users.team_lead', '=', $user->id)
                        ->where('users.id', '<>', $user->id)
                        ->select('users.id', 'users.email', \DB::raw('CONCAT(users.firstName, " ", users.lastName) as fullName'))
                        ->distinct('users.id', 'users.firstName', 'users.lastName')
                        ->get();
        foreach($users as $usr) {

            //if error occurs on team lead approval list then this query might be changed
            $weekValidations = WeekValidation::leftJoin('week_validation_projects', 'week_validation_projects.week_validation_id', '=', 'week_validations.id')
                                                ->where('week_validations.status','=', 'requested')
                                                ->whereNotIn('week_validations.id', function($query) {
                                                    $query->select('week_validation_projects.week_validation_id')
                                                            ->from('week_validation_projects')
                                                            ->where('week_validation_projects.status', '<>', 'accepted');
                                                })
                                                ->where('week_validations.user_id', '=', $usr->id)
                                                ->select('week_validations.*')
                                                ->distinct('week_validations.*')
                                                ->orderBy('week_validations.weekNumber', 'DESC')
                                                ->orderBy('week_validations.entryYear', 'DESC')
                                                ->get();
            $usr['weekValidation'] = $weekValidations;
        }
        return $users;
    }
}
