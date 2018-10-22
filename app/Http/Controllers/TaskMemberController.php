<?php

namespace App\Http\Controllers;

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
        $taskMember->estimatedHour=$request->estimatedHour;
        $taskMember->task_identification=$request->task_id;
        $taskMember->member_identification=$request->member_id;
        $taskMember->save();
        return $taskMember;
    }

    public function getAssignedMembers($id){
        $members = User::leftJoin('task_members','task_members.member_identification','=','users.id')->where('task_members.task_identification','=',$id)->get();
    }

    public function removeMember($id){
        $taskMember=TaskMember::find($id);
        $taskMember->delete();
        return $taskMember;
    }
}
