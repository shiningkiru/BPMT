<?php

namespace App\Http\Controllers;
use App\Tasks;
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
     *          name="milestone_id",
     *          description="Id of the Milestone",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="task_assigned_to",
     *          description="Id of the User",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="task_assigned_by",
     *          description="Id of the User",
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
        $task=new Tasks();
        $task->taskName=$request->taskName;
        $task->description=$request->description;
        $task->startDate=new \Datetime($request->startDate);
        $task->endDate=new \Datetime($request->endDate);
        $task->estimatedHours=$request->estimatedHours;
        $task->takenHours=$request->takenHours;
        $task->status=$request->status;
        $task->priority=$request->priority;
        $task->sprint_id=$request->sprint_id;
        $task->task_assigned_to=$request->task_assigned_to;
        $task->task_assigned_by=$request->task_assigned_by;
        $task->dependent_task_id=$request->dependent_task_id;
        $task->save();
        return $task;
    }

      /**
     * @SWG\Put(
     *      path="/v1/task/{id}",
     *      operationId="Update-task",
     *      tags={"Task"},
     *      summary="Task Updation",
     *      description="Returns Updated Task",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the Task",
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
     *          name="milestone_id",
     *          description="Id of the Milestone",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="task_assigned_to",
     *          description="Id of the User",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="task_assigned_by",
     *          description="Id of the User",
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
     * Returns Updated  Task
     */
    public function update(TaskFormRequest $request, $id)
    {
        $task = Tasks::find($id);
        $task->taskName=$request->taskName;
        $task->description=$request->description;
        $task->startDate=new \Datetime($request->startDate);
        $task->endDate=new \Datetime($request->endDate);
        $task->estimatedHours=$request->estimatedHours;
        $task->takenHours=$request->takenHours;
        $task->status=$request->status;
        $task->priority=$request->priority;
        $task->sprint_id=$request->sprint_id;
        $task->task_assigned_to=$request->task_assigned_to;
        $task->task_assigned_by=$request->task_assigned_by;
        $task->dependent_task_id=$request->dependent_task_id;
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
}
