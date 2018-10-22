<?php

namespace App\Http\Controllers;

use App\TaskMember;
use Illuminate\Http\Request;
use App\Http\Requests\TaskMemberRequest;

class TaskMemberController extends Controller
{
    public function addMember(TaskMemberRequest $request){
        $taskMember=new TaskMember();
        $taskMember->estimatedHour=$request->estimatedHour;
        $taskMember->takenHour=$request->takenHour;
        $taskMember->task_identification=$request->task_id;
        $taskMember->member_identification=$request->member_id;
        $taskMember->save();
        return $taskMember;
    }
}
