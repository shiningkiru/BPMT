<?php

namespace App\Http\Controllers;
use App\Tasks;
use App\Sprint;
use Illuminate\Http\Request;
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
        $id=$request->id;
        if(empty($id))
            $task=new Tasks();
        else
            $task=Tasks::find($id);
        $task->taskName=$request->taskName;
        $task->description=$request->description;
        $task->startDate=new \Datetime($request->startDate);
        $task->endDate=new \Datetime($request->endDate);
        $task->estimatedHours=$request->estimatedHours;
        $task->takenHours=$request->takenHours;
        $task->status=$request->status;
        $task->priority=$request->priority;
        $task->sprint_id=$request->sprint_id;
        $task->dependent_task_id=$request->dependent_task_id;
        
        $sprint=Sprint::find($request->sprint_id);
        $total = Tasks::where('sprint_id','=',$request->sprint_id)->first([
            \DB::raw('SUM(estimatedHours) as total')
        ]);  
        $total = $total->total + (float)$request->estimatedHours;          
        if($total > $sprint->estimatedHours){
            return Response::json(['error'=>['estimatedHours'=>'Estimated limit crossed']], 401);
        }
        $task->save();
        return $task;
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
}
