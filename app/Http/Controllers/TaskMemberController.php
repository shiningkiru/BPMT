<?php

namespace App\Http\Controllers;
use App\User;
use Response;
use App\Tasks;
use Validator;
use App\TaskMember;
use App\WorkTimeTrack;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use Illuminate\Pagination\Paginator;
use App\Repositories\TasksRepository;
use App\Http\Requests\TaskMemberRequest;
use App\Repositories\NotificationRepository;

class TaskMemberController extends Controller
{

  /**
   * @SWG\Post(
   *      path="/v1/task-member",
   *      operationId="assign-task-member",
   *      tags={"Task"},
   *      summary="Task member assign",
   *      description="Returns Task Member",
   *      @SWG\Parameter(
   *          name="Authorization",
   *          description="authorization header",
   *          required=true,
   *          type="string",
   *          in="header"
   *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the task at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
   *      @SWG\Parameter(
   *          name="estimatedHour",
   *          description="estimatedHour",
   *          required=true,
   *          type="number",
   *          in="formData"
   *      ),
   *      @SWG\Parameter(
   *          name="task_id",
   *          description="Task id",
   *          required=true,
   *          type="number",
   *          in="formData"
   *      ),
   *      @SWG\Parameter(
   *          name="member_id",
   *          description="Member id",
   *          required=true,
   *          type="number",
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
   * Returns Updated  Task member
   */
    public function addMember(TaskMemberRequest $request) {
        $helper = new HelperFunctions();
        $id=$request->id;
        if(empty($id)){
            $taskMember=new TaskMember();
            $taskMember->task_identification=$request->task_id;
            $taskMember->member_identification=$request->member_id;
        }else{
            $taskMember=TaskMember::find($id);
        } 
        $oldTaskMember = clone $taskMember;
        $taskMember->estimatedHours=$helper->timeConversion($request->estimatedHour);

        
        //estimated hour calculation
        $task=Tasks::find($request->task_id);
        $taskMemberTotal = TaskMember::where('task_identification','=',$task->id)->selectRaw('SUM(TIME_TO_SEC(estimatedHours)) as total')->groupBy('task_members.task_identification')->first();
        
        $totalSeconds = ($taskMemberTotal->total ?? 00);
        $estimatedHours=$helper->timeToSec($request->estimatedHour);
        $oldEstimatedHours=$helper->timeToSec($oldTaskMember->estimatedHours ?? 00);
        $taskEstimatedHour = $helper->timeToSec($task->estimatedHours);
        $total = (int)$totalSeconds + (int)$estimatedHours - (int)$oldEstimatedHours; 
        
        if($total > $taskEstimatedHour){
            return Response::json(['errors'=>['estimatedHours'=>['Estimated limit crossed']]], 422);
        }
        //estimated hour calculation end

        try{
            \DB::transaction(function() use ($helper, $request, $taskMember, $task){
                $taskMember->save();
                $notificationRepository = new NotificationRepository();
                $message = substr($task->taskName, 0, 50)." task is assigned to you";
                $notificationRepository->sendNotification(\Auth::user(), User::find($taskMember->member_identification), $message, "task-assign", $taskMember->id);
            });
        }catch(\Exception $e){
            
            return Response::json(['errors'=>['taskMember'=>[$e->getMessage()]]], 422);
        }
        return $taskMember;
    }

  /**
     * @SWG\Get(
     *      path="/v1/task-member/{id}",
     *      operationId="task member get",
     *      tags={"Task"},
     *      summary="Task members list",
     *      description="Returns Task members list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Task Id",
     *          required=true,
     *          type="number",
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns member list of Task
     */
    public function getAssignedMembers($id){
        $members = User::leftJoin('task_members','task_members.member_identification','=','users.id')->select('users.id as userId', 'users.employeeId','firstName','lastName','email','mobileNumber','profilePic','roles','task_members.id as taskMemberId','task_members.estimatedHours','task_members.takenHours', 'task_members.task_identification as taskId')->where('task_members.task_identification','=',$id)->get();
        return $members;
    }

   /*
     * @SWG\Get(
     *      path="/v1/task-member/current-assigned-tasks",
     *      operationId="task current assigned get",
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
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns current Assigned task list
     
    public function getCurrentAssignedTasks(){
        $user = \Auth::user();
        $id=$user->id;
        $tasks = Tasks::leftJoin('task_members','task_members.task_identification','=','tasks.id')
                    ->select('tasks.id as taskId', 'tasks.taskName', 'tasks.description', 'tasks.startDate as taskStartDate', 'tasks.endDate as taskEndDate', 'tasks.estimatedHours as taskEstimatedHours', 'tasks.takenHours as taskTakenHours', 'tasks.status as taskStatus', 'tasks.priority as taskPriority', 'task_members.estimatedHours as hoursAssigned', 'task_members.takenHours as hoursUsed')
                    ->where('task_members.member_identification','=',$id)
                    ->where(function($q){
                        $q->where('tasks.status', '=', "created")
                            ->orWhere('tasks.status', '=', "assigned")
                            ->orWhere('tasks.status', '=', "onhold")
                            ->orWhere('tasks.status', '=', "inprogress");
                    })
                    ->orderBy('task_members.created_at', 'DESC')
                    ->get();
        return $tasks;
    }*/

    /**
     * @SWG\Get(
     *      path="/v1/task-member/all-assigned-tasks",
     *      operationId="task All assigned get",
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
    public function getAllAssignedTasks(){
        $user = \Auth::user();
        $id=$user->id;
        $tasks = Tasks::leftJoin('task_members','task_members.task_identification','=','tasks.id')
                        ->select('tasks.id as taskId', 'tasks.taskName', 'tasks.description', 'tasks.startDate as taskStartDate', 'tasks.endDate as taskEndDate', 'tasks.estimatedHours as taskEstimatedHours', 'tasks.takenHours as taskTakenHours', 'tasks.status as taskStatus', 'tasks.priority as taskPriority', 'task_members.estimatedHours as hoursAssigned', 'task_members.takenHours as hoursUsed')
                        ->where('task_members.member_identification','=',$id)
                        ->orderBy('task_members.created_at', 'DESC')
                        ->get();
        return $tasks;
    }
    

    /**
    * @SWG\Delete(
    *      path="/v1/task-member/{id}",
    *      operationId="delete Task member",
    *      tags={"Task"},
    *      summary="Delete a Task",
    *      description="Delete a Task member",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Parameter(
    *          name="id",
    *          description="Task member Id",
    *          required=true,
    *          type="number",
    *          in="path"
    *      ),
    *      @SWG\Response(
    *          response=200,
    *          description="successful operation"
    *       ),
    *       @SWG\Response(response=500, description="Internal server error"),
    *       @SWG\Response(response=400, description="Bad request"),
    *     )
    *
    * Deletes a single Task
    */
    public function removeMember($id){
        $taskMember=TaskMember::find($id);
        $workTracks = WorkTimeTrack::where('task_member_identification','=',$taskMember->id)->get();
        if(sizeof($workTracks) > 0)
            return response()->json(['errors'=>'Can not delete value added employee'], 422);
        $taskMember->delete();
        return $taskMember;
    }

    public function employeeWorkReport(Request $request){
        $helper = new HelperFunctions();
        $taskRepository = new TasksRepository();
        // $valid = $taskRepository->validateRules($request->all(), [
        //     'dateOfEntry' => 'required|date',
        //     'endDate' => 'required|date'
        // ]);
        // if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);
        // $startDate = new \Datetime($request->dateOfEntry);
        // $endDate = new \Datetime($request->endDate);

        $startDate= date('Y-m-d',strtotime($request->get('dateOfEntry'))); 
        $endDate= date('Y-m-d',strtotime($request->get('endDate')));

        $currentPage = $request->pageNumber;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });
        $taskList = Tasks::leftJoin('task_members','tasks.id','=','task_members.task_identification')
                            ->leftJoin('work_time_tracks','work_time_tracks.task_member_identification','=','task_members.id')
                            ->leftJoin('sprints','sprints.id','=','tasks.sprint_id')
                            ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
                            ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
                            ->leftJoin('users','task_members.member_identification','=','users.id')
                            ->selectRaw('projects.projectName,users.id as userId, users.firstName, users.lastName, users.profilePic, tasks.taskName, tasks.status, task_members.estimatedHours, task_members.takenHours, task_members.id as taskMemberId')
                            ->groupBy('projects.projectName', 'tasks.taskName','users.id');      
        if(!empty($request->get('projectName'))){
            $taskList = $taskList->where('projects.projectName','LIKE','%'.$request->get('projectName')."%");
        }
        if(!empty($request->get('firstName'))){
            $taskList = $taskList->where('users.firstName','LIKE','%'.$request->firstName."%");
        }
        if (!empty($request->get('dateOfEntry')) && !empty($request->get('endDate')))
        $taskList->WhereBetween('work_time_tracks.dateOfEntry', [$startDate,$endDate]);
        if (!empty($request->get('dateOfEntry')) && empty($request->get('endDate')))
        $taskList->where('work_time_tracks.dateOfEntry','=',$startDate);

        $taskList=$taskList->paginate(20);
        foreach($taskList as $task){
            $WorkTimeTrack = WorkTimeTrack::where('task_member_identification','=',$task->taskMemberId)
                                ->WhereBetween('work_time_tracks.dateOfEntry', [$startDate,$endDate])
                                ->selectRaw('work_time_tracks.takenHours')
                                ->get();
            $total=0;
            foreach($WorkTimeTrack as $track){
                $total += $helper->timeToSec($track->takenHours);
            }
            $task['workedHours']=$helper->timeConversion($helper->secToTime($total));
        }
        return $taskList;   
    }
}
