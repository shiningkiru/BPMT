<?php

namespace App\Http\Controllers;

use App\Tasks;
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
        $taskMember=TaskMember::where('task_identification','=',$request->task_id)->where('member_identification','=',$request->user_id)->first();
        $workTrack=WorkTimeTrack::where('date','=',$request->entryDate)->where('task_member_identification','=',$taskMember->id)->first();
        if(!($workTrack instanceof WorkTimeTrack)){
            $workTrack=new WorkTimeTrack();
            $workTrack->dateOfEntry=$request->entryDate;
            $workTrack->task_member_identification=$taskMember->id;
        }else{
            $task->takenHours=$task->takenHours - (float)$workTrack->takenHours;
            $taskMember->takenHours=$taskMember->takenHours - (float)$workTrack->takenHours;
        }
        $workTrack->takenHours=$request->takenHours;
        $task->takenHours=$task->takenHours + (float)$request->takenHours;
        $taskMember->takenHours=$taskMember->takenHours + (float)$request->takenHours;
        $workTrack->save();
        $taskMember->save();
        $task->save();
        return $workTrack;
    }

    public function getTaskLogs(Request $request){
        $workTracks=WorkTimeTrack::leftJoin('task_members','task_members.id','=','task_member_identification')->where('task_members.task_identification','=',$request->task_id)->get();
        return $workTracks;
    }

    public function getTaskMemberLogs(Request $request){
        $taskMember=TaskMember::where('task_identification','=',$request->task_id)->where('member_identification','=',$request->user_id)->first();
        $workTracks=WorkTimeTrack::where('task_member_identification','=',$taskMember->id)->get();
        return $workTracks;
    }

}
