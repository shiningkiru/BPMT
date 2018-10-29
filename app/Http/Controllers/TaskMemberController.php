<?php

namespace App\Http\Controllers;

use App\User;
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
        $taskMember=new TaskMember();
        $taskMember->estimatedHours=$request->estimatedHour;
        $taskMember->task_identification=$request->task_id;
        $taskMember->member_identification=$request->member_id;
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
        $members = User::leftJoin('task_members','task_members.member_identification','=','users.id')->select('users.id as userId', 'users.employeeId','firstName','lastName','email','mobileNumber','profilePic','roles','task_members.id as taskMemberId')->where('task_members.task_identification','=',$id)->get();
        return $members;
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
