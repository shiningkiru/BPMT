<?php

namespace App\Http\Controllers;

use App\User;
use Response;
use Validators;
use App\GlobalTask;
use App\WorkTimeTrack;
use App\GlobalTaskUser;
use App\WeekValidation;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use App\Http\Requests\GlobalTaskRequest;
use App\Repositories\WeekValidationRepository;
use App\Http\Requests\GlobalTaskWorkTrackRequest;

class GlobalTaskController extends Controller
{
    public function getGlobalTasks(Request $request) {
        return GlobalTask::all();
    }

    public function singleTask(Request $request, $id) {
        return GlobalTask::find($id);
    }

    public function addTask(GlobalTaskRequest $request) {
        $helper = new HelperFunctions();
        if(empty($request->id)){
            $task = new GlobalTask();
        }else {
            $task = GlobalTask::find($request->id);
        }

        $task->title = $request->title;
        $task->projectCode = $request->projectCode;
        $task->description = $request->description;
        $task->isActive = ($request->isActive=='active')?true:false;

        try {
            $task->save();
            $helper->synchGlobalTaskMembers($task);
            return $task;
        }catch(\Exception $e){
            return Response::json(['errors'=>['serverError'=>['Task Update failed']]], 422);
        }
    }

    public function deleteTask(Request $request, $id) {
        try {
            $task = GlobalTask::find($id);
            $task->delete();
        }catch(\Exception $e){
            return Response::json(['errors'=>['serverError'=>['Task delete failed']]], 422);
        }
    }


    public function addTime(GlobalTaskWorkTrackRequest $request){
        //declaations
        return \DB::transaction(function() use ($request){
                
            $user = \Auth::user();

            if($user->roles == 'admin') {
                return \Response::json(['errors'=>['time'=>['Access denied!']]], 422);
            }
            if(!empty($request->user_id)){
                $currentUser = User::find($request->user_id);
            }else {
                $currentUser=$user;
            }

            $task=GlobalTask::find($request->task_id);  
            $helper=new HelperFunctions();
            $date=new \Datetime($request->entryDate);
            if($date > new \Datetime()){
                return \Response::json(['errors'=>['time'=>['Cant add time for future dates.']]], 422);
            }
            $weekDetails=$helper->getYearWeekNumber($date);
            $globalTaskUser=GlobalTaskUser::where('global_task_id','=',$request->task_id)->where('user_id','=',$currentUser->id)->first();
            $workTrack=WorkTimeTrack::where('dateOfEntry','=',$date)->where('global_task_user_id','=',$globalTaskUser->id)->first();
            
            $weekValidationRepository = new WeekValidationRepository();
            $weekValidation= $weekValidationRepository->getWeekValidation($currentUser->id, $weekDetails['week'], $weekDetails['year'])->first();
            if(!($weekValidation instanceof WeekValidation)){
                $weekValidation= new WeekValidation();
                $dateGap=$helper->getStartAndEndDate($date);
                $weekValidation->weekNumber=$weekDetails['week'];
                $weekValidation->entryYear=$weekDetails['year'];
                $weekValidation->user_id=$currentUser->id;
                $weekValidation->startDate = $dateGap[0];
                $weekValidation->endDate = $dateGap[1];
                $weekValidation->save();
                $weekValidation=WeekValidation::find($weekValidation->id);
            }

            //condition might need to be changed
            if(($weekValidation->status != 'entried' && $weekValidation->status != 'reassigned' && $currentUser->team_lead != $user->id)) {
                return Response::json(['errors'=>['time'=>['PTT is already processed']]], 422);
            }else if($currentUser->team_lead == $user->id && $weekValidation->status != 'requested'){
                return Response::json(['errors'=>['time'=>['PTT is not yet submitted.']]], 422);
            }

            if(!($workTrack instanceof WorkTimeTrack)){
                $workTrack=new WorkTimeTrack();
                $workTrack->dateOfEntry=$date;
                $workTrack->global_task_user_id=$globalTaskUser->id;
                $workTrack->week_number=$weekValidation->id;
            }
            if($helper->timeToSec($helper->timeConversion($request->takenHours)) > 32400){
                return Response::json(['errors'=>['time'=>['Per day work time crossed']]], 422);
            }
            $workTrack->takenHours=$helper->timeConversion($request->takenHours);
            $workTrack->description=$request->description ?? "NA";
            try {
                $workTrack->save();
                return $workTrack;
            }catch(\Exception $e) {
                return $e;
            }
        });
    }
}
