<?php

namespace App\Http\Controllers;

use App\User;
use App\Tasks;
use App\TaskMember;
use Illuminate\Http\Request;
use App\Http\Requests\TaskMemberRequest;

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
    public function addMember(TaskMemberRequest $request){
        $id=$request->id;
        if(empty($id)){
            $taskMember=new TaskMember();
            $taskMember->task_identification=$request->task_id;
            $taskMember->member_identification=$request->member_id;
        }else{
            $taskMember=TaskMember::find($id);
        } 
            

        $taskMember->estimatedHours=$request->estimatedHour;
        $taskMember->save();
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

   /**
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
     */
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
    }

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
        $taskMember->delete();
        return $taskMember;
    }
}
