<?php

namespace App\Http\Controllers;

use Event;
use App\User;
use App\Notification;
use App\WeekValidation;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use App\Events\NotificationFired;
use App\Repositories\UserRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\NotificationRepository;
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
        $helper = new HelperFunctions();
        $user = \Auth::user();
        $valid = $this->model->validateRules($request->all(), [
            'week_validation' => 'required:exists:week_validations,id'
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

        $weekValidation = $this->model->show($request->week_validation);

        //after friday validation
        $currentWeekNumber = $helper->getYearWeekNumber(new \Datetime());
        if($weekValidation->entryYear == (int)$currentWeekNumber['year'] && $weekValidation->weekNumber == (int)$currentWeekNumber['week']){
            $dateGap=$helper->getStartAndEndDate(new \Datetime());
            $dates=$helper->getDateRange($dateGap[0], $dateGap[1]);
            $friday=explode("-",$dates[4]);
            $friday = strtotime($friday[1]."-".$friday[0]."-".$friday[2]);
            $thisDate = new \Datetime();
            $thisDate = strtotime($thisDate->format('d-m-Y'));
            if($thisDate < $friday){
                return response()->json(['errors'=>['system'=>['You can assign PTT only after friday.']]], 422);
            }
        }
        //end of after friday validation
        

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
                    $notificationRepository = new NotificationRepository();
                    $projects=$projectRepository->findWeeklyWorkingProjects(new \Datetime($weekValidation->startDate),new \Datetime($weekValidation->endDate), $user->id);
                    foreach($projects as $project){
                        $management=array_merge($management, [['id'=>$project->project_lead_id]]);
                    }
                    $message="submited Time Tracks";
                    if($oldStatus == 'reassigned')$message='re requested for Time Tracks submission';
                    
                    foreach($management as $manager){
                        $message = $user->firstName." has ".$message." of the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear;
                        $notificationRepository->sendNotification($user, User::find($manager['id']), $message, "time-track", $weekValidation->id);
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
                    
                    $notificationRepository = new NotificationRepository();
                    $message = $user->firstName." has approved Time Tracks for the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear;
                    $notificationRepository->sendNotification($user, User::find($weekValidation->user_id), $message, "time-track-approve", $weekValidation->startDate);
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
                    
                    $notificationRepository = new NotificationRepository();
                    $message = $user->firstName." has resent Time Tracks for the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear.". Please verify";
                    $notificationRepository->sendNotification($user, User::find($weekValidation->user_id), $message, "time-track-reject", $weekValidation->startDate);
                else:
                    return response()->json(['errors'=>['condition'=>["You can't do this."]]], 422);
                endif;
            });
            return $weekValidation;
        }catch(\Exception $e){
            return response()->json(['errors'=>['server'=>[$e->getMessage()]]], 422);
        }
    }

    public function getByUserYear(Request $request){
        return $this->model->getWeekValidation($request->user_id, null, $request->year)->orderBy('weekNumber','DESC')->get();
    }
}
