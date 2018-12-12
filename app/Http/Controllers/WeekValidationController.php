<?php

namespace App\Http\Controllers;

use Event;
use App\User;
use App\Notification;
use App\WeekValidation;
use Illuminate\Http\Request;
use App\Events\NotificationFired;
use App\Repositories\UserRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\WeekValidationRepository;
use App\Http\Controllers\Master\MasterController;

class WeekValidationController extends MasterController
{
    private $userRepository;

    public function __construct()
    {
         parent::__construct(new WeekValidationRepository());
         $this->userRepository= new UserRepository();
    }

    public function submitWeeklyPtt(Request $request){
        $user = \Auth::user();
        $valid = $this->model->validateRules($request->all(), [
            'week_validation' => 'required:exists:week_validations,id'
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

        $weekValidation = $this->model->show($request->week_validation);
        $management = $this->userRepository->findByRole('management')->toArray();
        if(sizeof($management) == 0){
            return response()->json(['errors'=>['system'=>['Please assign management position']]], 422);
        }
        try{
            \DB::transaction(function () use ($weekValidation, $management, $user) {
                $oldStatus=$weekValidation->status;
                if($oldStatus=='entried' || $oldStatus=='reassigned'):
                    $weekValidation->status='requested';
                    $weekValidation->request_time=new \Datetime();
                    $weekValidation->save();
                    
                    $projectRepository = new ProjectRepository();
                    $projects=$projectRepository->findWeeklyWorkingProjects(new \Datetime($weekValidation->startDate),new \Datetime($weekValidation->endDate), $user->id);
                    foreach($projects as $project){
                        $management=array_merge($management, [['id'=>$project->project_lead_id]]);
                    }
                    $message="submited Time Tracks";
                    if($oldStatus == 'reassigned')$message='re requested for Time Tracks submission';
                    
                    foreach($management as $manager){
                        $notification = new Notification();
                        $notification->title = $user->firstName." has ".$message." of the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear;
                        $notification->notificationType="time-track";
                        $notification->linkId = $weekValidation->id;
                        $notification->from_user_id = $user->id;
                        $notification->to_user_id=$manager['id'];
                        $notification->save();
                        Event::fire(new NotificationFired($manager['id']));
                    }

                else:
                    return response()->json(['errors'=>['condition'=>["You can't do this."]]], 422);
                endif;
            });
            return $weekValidation;
        }catch(\Exception $e){
            return response()->json(['errors'=>['server'=>[$e->getMessage()]]], 422);
        }
    }

    public function approveWeeklyPtt(Request $request){
        $user = \Auth::user();
        $valid = $this->model->validateRules($request->all(), [
            'week_validation' => 'required:exists:week_validations,id'
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

        $weekValidation = $this->model->show($request->week_validation);
        
        try{
            \DB::transaction(function () use ($weekValidation, $user) {
                if($weekValidation->status=='requested'):
                    $weekValidation->status='accepted';
                    $weekValidation->accept_time=new \Datetime();
                    $weekValidation->save();
                    
                    // $projectRepository = new ProjectRepository();
                    // $projects=$projectRepository->findWeeklyWorkingProjects(new \Datetime($weekValidation->startDate),new \Datetime($weekValidation->endDate), $user->id);
                    // $targetUsers=[['id'=>$weekValidation->user_id]];
                    // foreach($projects as $project){
                    //     $targetUsers=array_merge($targetUsers, [['id'=>$project->project_lead_id]]);
                    // }
                    
                    $notification = new Notification();
                    $notification->title = $user->firstName." has approved Time Tracks for the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear;
                    $notification->notificationType="time-track-approve";
                    $notification->linkId = $weekValidation->startDate;
                    $notification->from_user_id = $user->id;
                    $notification->to_user_id=$weekValidation->user_id;
                    $notification->save();
                    Event::fire(new NotificationFired($weekValidation->user_id));
                else:
                    return response()->json(['errors'=>['condition'=>["You can't do this."]]], 422);
                endif;
            });
            return $weekValidation;
        }catch(\Exception $e){
            return response()->json(['errors'=>['server'=>[$e->getMessage()]]], 422);
        }
    }

    public function resendWeeklyPtt(Request $request){
        $user = \Auth::user();
        $valid = $this->model->validateRules($request->all(), [
            'week_validation' => 'required:exists:week_validations,id'
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

        $weekValidation = $this->model->show($request->week_validation);
        
        try{
            \DB::transaction(function () use ($weekValidation, $user) {
                if($weekValidation->status=='requested'):
                    $weekValidation->status='reassigned';
                    $weekValidation->save();
                    
                    // $projectRepository = new ProjectRepository();
                    // $projects=$projectRepository->findWeeklyWorkingProjects(new \Datetime($weekValidation->startDate),new \Datetime($weekValidation->endDate), $user->id);
                    // $targetUsers=[['id'=>$weekValidation->user_id]];
                    // foreach($projects as $project){
                    //     $targetUsers=array_merge($targetUsers, [['id'=>$project->project_lead_id]]);
                    // }
                    
                    $notification = new Notification();
                    $notification->title = $user->firstName." has resent Time Tracks for the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear.". Please verify";
                    $notification->notificationType="time-track-reject";
                    $notification->linkId = $weekValidation->startDate;
                    $notification->from_user_id = $user->id;
                    $notification->to_user_id=$weekValidation->user_id;
                    $notification->save();
                    Event::fire(new NotificationFired($weekValidation->user_id));
                else:
                    return response()->json(['errors'=>['condition'=>["You can't do this."]]], 422);
                endif;
            });
            return $weekValidation;
        }catch(\Exception $e){
            return response()->json(['errors'=>['server'=>[$e->getMessage()]]], 422);
        }
    }
}
