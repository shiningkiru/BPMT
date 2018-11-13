<?php

namespace App\Http\Controllers;

use App\User;
use App\Tasks;
use App\Project;
use App\TaskMember;
use App\WorkTimeTrack;
use Illuminate\Http\Request;
use App\Http\Requests\WorkTrackRequest;

class WorkTrackController extends Controller
{
    /**
    * @SWG\Post(
    *      path="/v1/time-track/add-time",
    *      operationId="add-my-time",
    *      tags={"Time Track"},
    *      summary="add my work time",
    *      description="add my work time",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Parameter(
    *          name="task_id",
    *          description="id of the task",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Parameter(
    *          name="user_id",
    *          description="id of the user",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Parameter(
    *          name="entryDate",
    *          description="date of entry",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Parameter(
    *          name="takenHours",
    *          description="taken hours",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Parameter(
    *          name="description",
    *          description="description",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Response(
    *          response=200,
    *          description="successful operation"
    *       ),
    *       @SWG\Response(response=500, description="Internal server error"),
    *       @SWG\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns success message
    */
    public function addMyTime(WorkTrackRequest $request){
        $task=Tasks::find($request->task_id); 
        $date=new \Datetime($request->entryDate);
        $taskMember=TaskMember::where('task_identification','=',$request->task_id)->where('member_identification','=',$request->user_id)->first();
        $workTrack=WorkTimeTrack::where('dateOfEntry','=',$date)->where('task_member_identification','=',$taskMember->id)->first();
         if(!($workTrack instanceof WorkTimeTrack)){
            $workTrack=new WorkTimeTrack();
            $workTrack->dateOfEntry=$date;
            $workTrack->task_member_identification=$taskMember->id;
        }else{
            $task->takenHours=$task->takenHours - (float)$workTrack->takenHours;
            $taskMember->takenHours=$taskMember->takenHours - (float)$workTrack->takenHours;
        }
        $workTrack->takenHours=$request->takenHours;
        $workTrack->description=$request->description;
        $task->takenHours=$task->takenHours + (float)$request->takenHours;
        $taskMember->takenHours=$taskMember->takenHours + (float)$request->takenHours;
        $workTrack->save();
        $taskMember->save();
        $task->save();
        return $workTrack;
    }

    /**
     * @SWG\Get(
     *      path="/v1/time-track/get-by-task",
     *      operationId="admin-get-by-task",
     *      tags={"Time Track"},
     *      summary="Get by task",
     *      description="Get by task",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="task_id",
     *          description="task id",
     *          required=true,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns Get by task
     */
    public function getTaskLogs(Request $request){
        $workTracks=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->where('task_members.task_identification','=',$request->task_id)->get();
        return $workTracks;
    }

    
    /**
     * @SWG\Get(
     *      path="/v1/time-track/get-by-task-and-member",
     *      operationId="admin-get-by-task-member",
     *      tags={"Time Track"},
     *      summary="Get by task",
     *      description="Get by task",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="task_id",
     *          description="task id",
     *          required=true,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="user_id",
     *          description="user id",
     *          required=true,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns Get by task
     */
    public function getTaskMemberLogs(Request $request){
        $taskMember=TaskMember::where('task_identification','=',$request->task_id)->where('member_identification','=',$request->user_id)->first();
        $workTracks=WorkTimeTrack::where('task_member_identification','=',$taskMember->id)->get();
        return $workTracks;
    }

    /**
     * @SWG\Post(
     *      path="/v1/time-track/get-logs-by-week",
     *      operationId="get-logs-by-week",
     *      tags={"Time Track"},
     *      summary="Get Logs by week according to user",
     *      description="Get Logs by week according to user",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="dateFromWeek",
     *          description="date in dd-mm-yyyy format",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="task_id",
     *          description="Id of the task",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns Get Logs by week according to user
     */
    public function getLogsByWeekAccordingUser(Request $request){
        $ddate = $request->dateFromWeek;
        $taskId = $request->task_id;
        $date = new \DateTime($ddate);
        $year=$date->format("Y");
        $week = $date->format("W")-1;
        $dateGap=$this->getStartAndEndDate($week,$year);
        $members = User::leftJoin('task_members','task_members.member_identification','=','users.id')->select('users.id as userId', 'users.employeeId','firstName','lastName','email','mobileNumber','profilePic','roles')->where('task_members.task_identification','=',$taskId)->get();
        $memberLogs['data']=[];
        $fromTime=strtotime($dateGap[0]->format('d-m-Y'));
        $toTime=strtotime($dateGap[1]->format('d-m-Y'));
        $dates=[];
        while($fromTime <= $toTime){
            $dates[]=date("m-d-Y",$fromTime);
            $fromTime=strtotime(date('d-m-Y', strtotime('+1 day', $fromTime)));
        }
        $memberLogs['dates']=$dates;
        foreach($members as $member){
            $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')->where('task_identification','=',$taskId)->where('member_identification','=',$member->userId)->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])->get();
            $member['trackRecords']=$logs;
            if(sizeof($logs)>0)
                $memberLogs['data'][]=$member;
        }
        return $memberLogs;
    }

    /**
     * @SWG\Post(
     *      path="/v1/time-track/get-logs-by-week/single-user",
     *      operationId="get-logs-by-week-single-user",
     *      tags={"Time Track"},
     *      summary="Get Logs by week according to logged in user",
     *      description="Get Logs by week according to logged in user",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="dateFromWeek",
     *          description="date in dd-mm-yyyy format",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="task_id",
     *          description="Id of the task",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns Get Logs by week according to user
     */
    public function getLogsByWeekAccordingLoggedInUser(Request $request){
        $taskId = $request->task_id;
        $user = \Auth::user();
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $year=$date->format("Y");
        $week = $date->format("W")-1;
        $dateGap=$this->getStartAndEndDate($week,$year);
        
        $fromTime=strtotime($dateGap[0]->format('d-m-Y'));
        $toTime=strtotime($dateGap[1]->format('d-m-Y'));
        $dates=[];
        while($fromTime <= $toTime){
            $dates[]=date("m-d-Y",$fromTime);
            $fromTime=strtotime(date('d-m-Y', strtotime('+1 day', $fromTime)));
        }

        $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')->where('task_identification','=',$taskId)->where('member_identification','=',$user->id)->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])->get();
        $user['trackRecords']=$logs;
        $user['dates']=$dates;
        return $user;
    }

    public function getStartAndEndDate($week, $year)
    {
        $time = strtotime("1 January ".$year, time());
        $day = date('w', $time);
        $time += ((7*$week)+1-$day)*24*3600;
        $timeGaps[0] = new \Datetime(date('Y-n-j', $time));
        $time += 6*24*3600;
        $timeGaps[1] = new \Datetime(date('Y-n-j', $time));
        return $timeGaps;
    }


    


    /**
     * @SWG\Post(
     *      path="/v1/task-member/current-assigned-tasks/project",
     *      operationId="task current assigned get project",
     *      tags={"Task"},
     *      summary="Assigned task list",
     *      description="Returns current Assigned task list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="projectId",
     *          description="projectId",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dateFromWeek",
     *          description="date in dd-mm-yyyy format",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns current Assigned task list
     */
    public function getCurrentAssignedTasksOnProject(Request $request){
        $user = \Auth::user();
        $id=$user->id;
        $projectId = $request->projectId;
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $year=$date->format("Y");
        $week = $date->format("W")-1;
        $dateGap=$this->getStartAndEndDate($week,$year);
        
        $fromTime=strtotime($dateGap[0]->format('d-m-Y'));
        $toTime=strtotime($dateGap[1]->format('d-m-Y'));
        $dates=[];
        while($fromTime <= $toTime){
            $dates[]=date("m-d-Y",$fromTime);
            $fromTime=strtotime(date('d-m-Y', strtotime('+1 day', $fromTime)));
        }

        $project = Project::find($projectId);

        $tasks = Tasks::leftJoin('task_members','task_members.task_identification','=','tasks.id')
                        ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                        ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
                        ->select('tasks.id as taskId', 'tasks.taskName', 'tasks.description', 'tasks.startDate as taskStartDate', 'tasks.endDate as taskEndDate', 'tasks.estimatedHours as taskEstimatedHours', 'tasks.takenHours as taskTakenHours', 'tasks.status as taskStatus', 'tasks.priority as taskPriority', 'task_members.estimatedHours as hoursAssigned', 'task_members.takenHours as hoursUsed')
                        ->where('task_members.member_identification','=',$id)
                        ->where('milestones.project_milestone_id','=',$projectId)
                        ->where(function($q){
                            $q->where('tasks.status', '=', "created")
                                ->orWhere('tasks.status', '=', "assigned")
                                ->orWhere('tasks.status', '=', "onhold")
                                ->orWhere('tasks.status', '=', "inprogress");
                        })
                        ->orderBy('task_members.created_at', 'DESC')
                        ->get();
        foreach($tasks as $task){
            $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')->where('task_identification','=',$task->taskId)->where('member_identification','=',$user->id)->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])->get();
            $task['timeTrack']=$logs;
        }  
        $project['tasks']=$tasks;
        $project['dates']=$dates;             
        return $project;
    }



    /**
     * @SWG\Post(
     *      path="/v1/task-member/all-assigned-tasks/project",
     *      operationId="task All assigned get projecta",
     *      tags={"Task"},
     *      summary="Assigned task list",
     *      description="Returns All Assigned task list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="projectId",
     *          description="projectId",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dateFromWeek",
     *          description="date in dd-mm-yyyy format",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns All Assigned task list
     */
    public function getAllAssignedTasksOnProject(Request $request){
        $user = \Auth::user();
        $id=$user->id;
        $projectId = $request->projectId;
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $year=$date->format("Y");
        $week = $date->format("W")-1;
        $dateGap=$this->getStartAndEndDate($week,$year);
        
        $fromTime=strtotime($dateGap[0]->format('d-m-Y'));
        $toTime=strtotime($dateGap[1]->format('d-m-Y'));
        $dates=[];
        while($fromTime <= $toTime){
            $dates[]=date("m-d-Y",$fromTime);
            $fromTime=strtotime(date('d-m-Y', strtotime('+1 day', $fromTime)));
        }

        $project = Project::find($projectId);

        $tasks = Tasks::leftJoin('task_members','task_members.task_identification','=','tasks.id')
                        ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                        ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
                        ->select('tasks.id as taskId', 'tasks.taskName', 'tasks.description', 'tasks.startDate as taskStartDate', 'tasks.endDate as taskEndDate', 'tasks.estimatedHours as taskEstimatedHours', 'tasks.takenHours as taskTakenHours', 'tasks.status as taskStatus', 'tasks.priority as taskPriority', 'task_members.estimatedHours as hoursAssigned', 'task_members.takenHours as hoursUsed')
                        ->where('task_members.member_identification','=',$id)
                        ->where('milestones.project_milestone_id','=',$projectId)
                        ->orderBy('task_members.created_at', 'DESC')
                        ->get();
        foreach($tasks as $task){
            $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')->where('task_identification','=',$task->taskId)->where('member_identification','=',$user->id)->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])->get();
            $task['timeTrack']=$logs;
        }  
        $project['tasks']=$tasks;
        $project['dates']=$dates;             
        return $project;
    }

}
