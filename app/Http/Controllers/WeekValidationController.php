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
                if($weekValidation->status=='entried'):
                    $weekValidation->status='requested';
                    $weekValidation->save();
                    
                    $projectRepository = new ProjectRepository();
                    $projects=$projectRepository->findWeeklyWorkingProjects(new \Datetime($weekValidation->startDate),new \Datetime($weekValidation->endDate), $user->id);
                    foreach($projects as $project){
                        $management=array_merge($management, [['id'=>$project->project_lead_id]]);
                    }
                    
                    foreach($management as $manager){
                        $notification = new Notification();
                        $notification->title = $user->firstName." has submitted Time Tracks for the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear;
                        $notification->notificationType="time-track";
                        $notification->linkId = $weekValidation->id;
                        $notification->from_user_id = $user->id;
                        $notification->to_user_id=$manager['id'];
                        $notification->save();
                        Event::fire(new NotificationFired($manager['id']));
                    }

                endif;
            });
            return $weekValidation;
        }catch(\Exception $e){
            return response()->json(['errors'=>['server'=>[$e->getMessage()]]], 422);
        }
    }
}
