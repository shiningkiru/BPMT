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
use App\Helpers\HelperFunctions;

class MyTaskController extends Controller
{
    public function getMyTask(Request $request) {

        $helper = new HelperFunctions();
        $valid = Validator::make($request->all(), [
            'week_validation_id' => 'nullable|exists:week_validations,id',
            'week_number' => 'nullable|integer|between:1,53',
            'year' => 'nullable|integer|between:2017,2030'
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

        //declarations
        $user = \Auth::user();
        $currentUser = clone $user;

        if(empty($request->week_number) || empty($request->year)){
            $week = $helper->getYearWeekNumber(new \Datetime());
            $weekNumber=$week['week'];
            $year = $week['year'];
        }else {
            $weekNumber = $request->week_number;
            $year = $request->year;
        }

        if(!empty($request->week_validation_id)){
            $weekValidation = WeekValidation::find($request->week_validation_id);
            $weekNumber = $weekValidation->weekNumber;
            $year = $weekValidation->entryYear;
            $user = $weekValidation->user;
        }

        if($currentUser->roles == 'management'){
            
        }


        $columnTotals=[];

        //find start and end date
        $timeGap = $helper->getStartAndEndDateByWeekNumber($weekNumber, $year);
        $weekValidation = WeekValidation::where('weekNumber', '=', $weekNumber)->where('entryYear', '=', $year)->where('user_id', '=', $user->id)->first();


        //global task system
        $globalTasks = GlobalTask::leftJoin('global_task_users','global_task_users.global_task_id', '=', 'global_tasks.id')
                                    ->where('global_task_users.user_id', '=', $user->id)
                                    ->where('global_tasks.isActive','=',true)
                                    ->select('global_tasks.id', 'global_tasks.title', 'global_tasks.description', 'global_task_users.id as guserId')
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

        $result['gtask'] = $globalTasks;
        
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
                                    
                            })
                            ->select('projects.*')
                            ->distinct('projects.*')
                            ->get();

        //find the current tasks which are available in each project
        foreach($projects as $project){
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
                            ->select('tasks.*')
                            ->distinct('tasks.*')
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

        $result['dates'] = $helper->getDateRange($timeGap[0], $timeGap[1]);
        $result['weekValidation'] = $weekValidation;
        $result['projects'] = $projects;
        $result['columnTotals'] = $columnTotals;
        $result['grandTotal'] = $helper->secToTime($grandTotal);
        $result['weekNumber']=$weekNumber;
        $result['year']=$year;
        return $result;
    }
}
