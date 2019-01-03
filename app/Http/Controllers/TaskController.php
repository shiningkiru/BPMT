<?php

namespace App\Http\Controllers;
use App\User;
use Response;
use App\Tasks;
use App\Sprint;
use App\Milestones;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use App\Repositories\TasksRepository;
use App\Http\Requests\TaskFormRequest;

class TaskController extends Controller
{
    /**
     * @SWG\Post(
     *      path="/v1/task",
     *      operationId="create-task",
     *      tags={"Task"},
     *      summary="Task creation",
     *      description="Returns created Task",
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
     *          name="taskName",
     *          description="Task name",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="description",
     *          description="description of the Task",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="startDate",
     *          description="start datetime",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="endDate",
     *          description="End Datetime",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="estimatedHours",
     *          description="Estimated Hours",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="takenHours",
     *          description="Taken Hours",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="takenHours",
     *          description="Taken Hours",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="Task Status('created', 'assigned', 'onhold', 'inprogress','completed', 'cancelled',' failed')",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="priority",
     *          description="Task Priority('critical', 'high', 'medium', 'low')",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="sprint_id",
     *          description="Id of the sprint",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dependent_task_id",
     *          description="Dependent Task ID",
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
     * Returns created  Task
     */
    public function create(TaskFormRequest $request)
    {
        $helper = new HelperFunctions();
        $id=$request->id;
        if(empty($id))
            $task=new Tasks();
        else
            $task=Tasks::find($id);
        $oldTask = clone $task;
        $task->taskName=$request->taskName;
        $task->description=$request->description;
        $startDate=new \Datetime($request->startDate);
        $task->startDate=$startDate->format('Y-m-d 00:00:00');
        $endDate=new \Datetime($request->endDate);
        $task->endDate=$endDate->format('Y-m-d 00:00:00');
        $task->estimatedHours=$helper->timeConversion($request->estimatedHours);
        $task->takenHours=$helper->timeConversion($request->takenHours ?? 00);
        $task->status=$request->status;
        $task->priority=$request->priority;
        $task->sprint_id=$request->sprint_id;
        $task->dependent_task_id=$request->dependent_task_id;        
        
        //estimated hour calculation
        $sprint=Sprint::find($request->sprint_id);
        $taskTotal = Tasks::where('sprint_id','=',$sprint->id)->selectRaw('estimatedHours')->get();
        $total=0;
        foreach($taskTotal as $tsk){
            $total = $total + (int)$helper->timeToSec($tsk['estimatedHours']);
        }
        $totalSeconds =$total;
        $estimatedHours=$helper->timeToSec($request->estimatedHours);
        $oldEstimatedHours=$helper->timeToSec($oldTask->estimatedHours ?? 00);
        $sprintEstimatedHour = $helper->timeToSec($sprint->estimatedHours);
        $total = (int)$totalSeconds + (int)$estimatedHours - (int)$oldEstimatedHours; 
        
        if($total > $sprintEstimatedHour){
            return Response::json(['errors'=>['estimatedHours'=>['Estimated limit crossed']]], 422);
        }
        //estimated hour calculation end

        $task->save();

        if($oldTask->status != $task->status){
            $this->updateProjectProgress($task);
        }
        return $task;
    }

    public function changeSprint(Request $request) {
        $taskRepository = new TasksRepository();
        $valid = $taskRepository->validateRules($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'sprint_id' => 'required|exists:sprints,id'
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

        $task = $taskRepository->show($request->task_id);
        $task->sprint_id = $request->sprint_id;
        $task->save();
        return $task;

    }

    public function updateProjectProgress($task){
        $mileStone = Milestones::leftJoin('sprints','milestones.id', '=', 'sprints.milestone_id')->where('sprints.id','=',$task->sprint_id)->select('milestones.*')->first();
        $taskLogs = Tasks::leftJoin('sprints', 'sprints.id', '=', 'tasks.sprint_id')->where('sprints.milestone_id','=',$mileStone->id)->selectRaw('SUM(IF(tasks.status = "created", 1, 0)) as Created , SUM(IF(tasks.status = "assigned", 1, 0)) as Assigned, SUM(IF(tasks.status = "onhold", 1, 0)) as OnHold, SUM(IF(tasks.status = "inprogress", 1, 0)) as InProgress, SUM(IF(tasks.status = "completed", 1, 0)) as Completed, SUM(IF(tasks.status = "cancelled", 1, 0)) as Cancelled, SUM(IF(tasks.status = "failed", 1, 0)) as Failed, count(tasks.id) as Total')->first();
        $pending = (int) $taskLogs->Created + (int) $taskLogs->Assigned + (int)$taskLogs->OnHold + $taskLogs->InProgress + (int)$taskLogs->Failed;
        $completed = (int) $taskLogs->Completed + (int) $taskLogs->Cancelled ;
        $total = (int) $taskLogs->Total;
        $progress = 100 * $completed / $total;
        $mileStone=Milestones::find($mileStone->id);
        $mileStone->progress = $progress;
        $mileStone->save();
        return $mileStone;
    }

   /**
     * @SWG\Get(
     *      path="/v1/task/{id}",
     *      operationId="single Task",
     *      tags={"Task"},
     *      summary="Task details",
     *      description="Returns Task details",
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
     * Returns list of Task
     */
    public function show($id)
    {
        $task = Tasks::find($id);
        return $task;
    }

     /**
     * @SWG\Get(
     *      path="/v1/task/by-sprints/{id}",
     *      operationId="Task-List-from-sprints",
     *      tags={"Task"},
     *      summary="Task List from sprints",
     *      description="Returns Task List from sprints",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Sprint Id",
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
     * Returns list Task List from sprints
     */
    public function index($id)
    {
        $task = Tasks::where('sprint_id','=',$id)->get();
        return $task;
    }

    public function showUsers($id)
    {
        $tasks = Tasks::where('sprint_id','=',$id)->select('tasks.id','tasks.taskName', 'tasks.description', 'tasks.startDate', 'tasks.endDate', 'tasks.priority', 'tasks.status')->get();
        foreach($tasks as $task){
            $users = User::leftJoin('task_members', 'task_members.member_identification','=', 'users.id')->where('task_members.task_identification', '=', $task->id)->select( 'users.id', 'users.profilePic', 'users.firstName')->get();
            $task['users']=$users;
        }
        return $tasks;
    }

    /**
    * @SWG\Delete(
    *      path="/v1/task/{id}",
    *      operationId="delete Task",
    *      tags={"Task"},
    *      summary="Delete a Task",
    *      description="Delete a Task",
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
    * Deletes a single Task
    */
    public function delete($id){
        $task = Tasks::find($id);
        $task->delete();
        return $task;
    }


       /**
     * @SWG\Get(
     *      path="/v1/task/total-tasks/{id}",
     *      operationId="total-tasks",
     *      tags={"Task"},
     *      summary="Total Number of Tasks created",
     *      description="Returns Total Number of Tasks created",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Project Id",
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
     * Returns list of Total Number of Tasks created and completed
     */
    public function totalTasks($id){
        $tasks = Tasks::leftjoin('sprints','sprints.id','=','sprint_id')
        ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
        ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
        ->where('projects.id','=',$id)
        ->selectRaw('COUNT(tasks.id) as total_tasks, SUM(IF(tasks.status="completed",1,0)) as completed_tasks')
        ->get();
        return $tasks[0];
    }

    public function showChart($id, $status)
    {
        if($status=='none')
        {
        $chartData=[];
        $chart=Tasks::leftJoin('sprints','sprints.id','=','sprint_id')
        ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
        ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
        ->select('tasks.id','tasks.estimatedHours', 'tasks.takenHours','tasks.taskName','tasks.sprint_id')
        ->where('projects.id','=',$id)->get();
        $chartData['list']=$chart;
       return $chartData;
        }else {
       $chartData=[];
       $chart=Tasks::leftJoin('sprints','sprints.id','=','sprint_id')
       ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
       ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
       ->select('tasks.id','tasks.estimatedHours', 'tasks.takenHours','tasks.taskName','tasks.sprint_id')
       ->where('projects.id','=',$id)
       ->where('tasks.status','=', $status)->get();
       $chartData['list']=$chart;
       return $chartData;
    }
}

public function directProjectChart($id)
{
    $projectData=[];
    $chart=Tasks::leftJoin('sprints','sprints.id','=','sprint_id')
    ->leftJoin('milestones','milestones.id','=','sprints.milestone_id')
    ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
    ->leftJoin('task_members','task_members.task_identification','=','tasks.id')
    ->leftJoin('users','task_members.member_identification','=','users.id')
    ->select('tasks.id', 'tasks.takenHours','tasks.taskName','tasks.sprint_id','users.firstName')
    ->where('projects.id','=',$id)->get();
    $projectData['projectList']=$chart;
   return $projectData;
}


public function getSprintEstimatedHoursTotal(Sprint $id){
    $helper = new HelperFunctions();
    $taskTotal = Tasks::where('sprint_id','=',$id->id)->selectRaw('estimatedHours')->get();
    $total=0;
    foreach($taskTotal as $tsk){
        $total = $total + (int)$helper->timeToSec($tsk['estimatedHours']);
    }
    
    $sprintEstimatedHours = $helper->timeToSec($id->estimatedHours);
    $result['remaining'] =$helper->secToTime($sprintEstimatedHours - $total);
    $result['totalUsed'] =$helper->secToTime($total);        
    $result['sprintHours'] =$id->estimatedHours;

    return $result;
}
}