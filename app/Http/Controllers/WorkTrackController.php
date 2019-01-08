<?php

namespace App\Http\Controllers;

use App\User;
use App\Tasks;
use App\Project;
use App\TaskMember;
use App\WorkTimeTrack;
use App\WeekValidation;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use Illuminate\Pagination\Paginator;
use App\Repositories\TasksRepository;
use App\Http\Requests\WorkTrackRequest;
use App\Repositories\ProjectRepository;
use App\Repositories\WeekValidationRepository;

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
        $helper=new HelperFunctions();
        $date=new \Datetime($request->entryDate);
        $weekDetails=$helper->getYearWeekNumber($date);
        $taskMember=TaskMember::where('task_identification','=',$request->task_id)->where('member_identification','=',$request->user_id)->first();
        $workTrack=WorkTimeTrack::where('dateOfEntry','=',$date)->where('task_member_identification','=',$taskMember->id)->first();
        
        $takenHour = $helper->timeToSec($helper->timeConversion($request->takenHours) ?? 00);
        $taskTaken = $helper->timeToSec($helper->timeConversion($task->takenHours));
        $taskMemberTaken = $helper->timeToSec($helper->timeConversion((empty($taskMember->takenHours))?00:$taskMember->takenHours));
        
         if(!($workTrack instanceof WorkTimeTrack)){
            $workTrack=new WorkTimeTrack();
            $workTrack->dateOfEntry=$date;
            $workTrack->task_member_identification=$taskMember->id;

            $weekValidationRepository = new WeekValidationRepository();
            $weekValidation= $weekValidationRepository->getWeekValidation($request->user_id, $weekDetails['week'], $weekDetails['year'])->first();
            if(!($weekValidation instanceof WeekValidation)){
                $weekValidation= new WeekValidation();
                $dateGap=$helper->getStartAndEndDate($date);
                $weekValidation->weekNumber=$weekDetails['week'];
                $weekValidation->entryYear=$weekDetails['year'];
                $weekValidation->user_id=$request->user_id;
                $weekValidation->startDate = $dateGap[0];
                $weekValidation->endDate = $dateGap[1];
                $weekValidation->save();
            }
            $workTrack->week_number=$weekValidation->id;
        }else{
            $workTrackTaken = $helper->timeToSec($helper->timeConversion($workTrack->takenHours));
            $taskTaken-= $workTrackTaken;
            $taskMemberTaken-= $workTrackTaken;
            $task->takenHours=$helper->timeConversion($helper->secToTime($taskTaken)); 
            $taskMember->takenHours=$helper->timeConversion($helper->secToTime($taskMemberTaken ));
        }
        $workTrack->takenHours=$helper->timeConversion($request->takenHours);
        $workTrack->description=$request->description;
        
        $task->takenHours=$helper->timeConversion($helper->secToTime($taskTaken + $takenHour));
        $taskMember->takenHours=$helper->timeConversion($helper->secToTime($taskMemberTaken + $takenHour));
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
        $helper = new HelperFunctions();
        $taskId = $request->task_id;
        $date = new \DateTime($ddate);
        $dateGap=$helper->getStartAndEndDate($date);
        $members = User::leftJoin('task_members','task_members.member_identification','=','users.id')->select('users.id as userId', 'users.employeeId','firstName','lastName','email','mobileNumber','profilePic','roles')->where('task_members.task_identification','=',$taskId)->get();
        $memberLogs['data']=[];
        $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);
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
        $helper = new HelperFunctions();
        $user = \Auth::user();
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $dateGap=$helper->getStartAndEndDate($date);
        $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);

        $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')->where('task_identification','=',$taskId)->where('member_identification','=',$user->id)->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])->get();
        $user['trackRecords']=$logs;
        $user['dates']=$dates;
        return $user;
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
        $helper = new HelperFunctions();
        $id=$user->id;
        $projectId = $request->projectId;
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $dateGap=$helper->getStartAndEndDate($date);
        $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);

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
        $project['weekNumber']=$dateGap;          
        return $project;
    }
    


    /**
     * @SWG\Post(
     *      path="/v1/task-member/current-assigned-tasks",
     *      operationId="task current assigned ",
     *      tags={"Task"},
     *      summary="Assigned task list",
     *      description="Returns all current Assigned task list",
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
    public function getCurrentAssignedTasks(Request $request){
        $user = \Auth::user();
        $helper = new HelperFunctions();
        $id=$user->id;
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $dateGap=$helper->getStartAndEndDate($date);
        $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);
        $weekValidationRepository=new WeekValidationRepository();

        if($request->paginated == true){
            $currentPage = $request->pageNumber;
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });
        }
        
        $tasks = Tasks::leftJoin('task_members','task_members.task_identification','=','tasks.id')
                        ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                        ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
                        ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
                        ->select('projects.id as projectId', 'projects.projectName', 'tasks.id as taskId', 'tasks.taskName', 'tasks.description', 'tasks.startDate as taskStartDate', 'tasks.endDate as taskEndDate', 'tasks.estimatedHours as taskEstimatedHours', 'tasks.takenHours as taskTakenHours', 'tasks.status as taskStatus', 'tasks.priority as taskPriority', 'task_members.estimatedHours as hoursAssigned', 'task_members.takenHours as hoursUsed')
                        ->where('task_members.member_identification','=',$id)
                        ->where(function($q){
                            $q->where('tasks.status', '=', "created")
                                ->orWhere('tasks.status', '=', "assigned")
                                ->orWhere('tasks.status', '=', "onhold")
                                ->orWhere('tasks.status', '=', "inprogress");
                        })
                        ->orderBy('task_members.created_at', 'DESC');
        if($request->paginated == true){
            $tasks = $tasks->paginate(25);
        }else {
            $tasks = $tasks->get();
        }

        foreach($tasks as $task){
            $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')
                                ->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')
                                ->where('task_identification','=',$task->taskId)
                                ->where('member_identification','=',$user->id)
                                ->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])
                                ->distinct('work_time_tracks.id')
                                ->get();
            $task['timeTrack']=$logs;
        }  
        $project['tasks']=$tasks;
        $project['dates']=$dates;    
        $project['weekNumber']=$dateGap;    
        $project['weekValidation']=$weekValidationRepository->getWeekValidation($id, $dateGap[0]->format('W'), $dateGap[0]->format('Y'))->first();         
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
    public function getAllAssignedTasksOnProject(Request $request){dd(1);
        $user = \Auth::user();
        $id=$user->id;
        $helper = new HelperFunctions();
        $projectId = $request->projectId;
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $dateGap=$helper->getStartAndEndDate($date);
        $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);

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
            $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')
                                ->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')
                                ->where('task_identification','=',$task->taskId)
                                ->where('member_identification','=',$user->id)
                                ->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])
                                ->distinct('work_time_tracks.id')
                                ->get();
            $task['timeTrack']=$logs;
        }  

        

        // getWeekValidation($id, $week, $year);
        $project['tasks']=$tasks;
        $project['dates']=$dates;       
        return $project;
    }



    /**
     * @SWG\Post(
     *      path="/v1/task-member/all-assigned-tasks",
     *      operationId="task All assigned tasks",
     *      tags={"Task"},
     *      summary="Assigned tasks all",
     *      description="Returns All Assigned task lists",
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
    public function getAllAssignedTasks(Request $request){
        $user = \Auth::user();
        $id=$user->id;
        $helper = new HelperFunctions();
        $ddate = $request->dateFromWeek;
        $date = new \DateTime($ddate);
        $dateGap=$helper->getStartAndEndDate($date);
        $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);


        if($request->paginated == true){
            $currentPage = $request->pageNumber;
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });
        }

        $tasks = Tasks::leftJoin('task_members','task_members.task_identification','=','tasks.id')
                        ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                        ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
                        ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
                        ->select('projects.id as projectId', 'projects.projectName', 'tasks.id as taskId', 'tasks.taskName', 'tasks.description', 'tasks.startDate as taskStartDate', 'tasks.endDate as taskEndDate', 'tasks.estimatedHours as taskEstimatedHours', 'tasks.takenHours as taskTakenHours', 'tasks.status as taskStatus', 'tasks.priority as taskPriority', 'task_members.estimatedHours as hoursAssigned', 'task_members.takenHours as hoursUsed')
                        ->where('task_members.member_identification','=',$id)
                        ->orderBy('task_members.created_at', 'DESC');
        if($request->paginated == true){
            $tasks = $tasks->paginate(25);
        }else {
            $tasks = $tasks->get();
        }
        foreach($tasks as $task){
            $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')
                                ->select('work_time_tracks.id', 'work_time_tracks.description', 'work_time_tracks.takenHours','work_time_tracks.dateOfEntry','work_time_tracks.isUpdated')
                                ->where('task_identification','=',$task->taskId)
                                ->where('member_identification','=',$user->id)
                                ->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])
                                ->distinct('work_time_tracks.id')
                                ->get();
            $task['timeTrack']=$logs;
        }  
        $project['tasks']=$tasks;
        $project['dates']=$dates;          
        return $project;
    }

    public function getMyWeeklyPtt(Request $request){
        $user= \Auth::user();
        $helper = new HelperFunctions();
        $user_id = $request->user_id;
        if(empty($user_id))$user_id=$user->id;

        $project_lead_id=null;
        $ptt_type = $request->ptt_type;
        if($ptt_type == 'approve-ptt' && $user->roles == 'project-lead'){
            $project_lead_id = $user->id;
        }

        $dateOfWeek = $request->get('dateOfWeek');
        $date = new \DateTime($dateOfWeek);
        $dateGap=$helper->getStartAndEndDate($date);
        $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);

        //find the projects
        $projectRepository = new ProjectRepository();
        $projects=$projectRepository->findWeeklyWorkingProjects($dateGap[0], $dateGap[1], $user_id, $project_lead_id);

        //find the tasks on projects on which user worked
        foreach($projects as $project):
            $taskRepository = new TasksRepository();
            $tasks= $taskRepository->findWeeklyWorkingTasks($dateGap[0], $dateGap[1], $project->id, $user_id);
            foreach($tasks as $task): 
                $logs=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->selectRaw('work_time_tracks.id, work_time_tracks.description, work_time_tracks.takenHours, DATE_FORMAT(work_time_tracks.dateOfEntry,"%m-%d-%Y") as dateOfEntry, work_time_tracks.isUpdated')->where('task_identification','=',$task->id)->where('member_identification','=',$user_id)->whereBetween('dateOfEntry', [$dateGap[0], $dateGap[1]])->get();
                $data=[];
                foreach($logs as $log){
                    $data[$log->dateOfEntry]=$log;
                }
                $task['logs']=$data;
            endforeach;
            $project['tasks'] = $tasks;

        endforeach;
        $weekDetails=$helper->getYearWeekNumber(new \Datetime($dateOfWeek));
        $weekValidationRepository = new WeekValidationRepository();
        $weekValidation= $weekValidationRepository->getWeekValidation($user_id, $weekDetails['week'], $weekDetails['year'])->first();

        $res['projects']=$projects;
        $res['dates']=$dates;
        $res['week']=$weekDetails;
        $res['weekValidation']=$weekValidation;
        //show the logs of week selected
        return $res;
    }
}
