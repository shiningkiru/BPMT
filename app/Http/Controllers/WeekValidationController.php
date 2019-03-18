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
        return \DB::transaction(function () use ($request) {
            $helper = new HelperFunctions();
            $notificationRepository = new NotificationRepository();
            $user = \Auth::user();
            $valid = $this->model->validateRules($request->all(), [
                'weekNumber' => 'required|integer|between:1,53',
                'year' => 'required|integer|between:2017,2030'
            ]);
            if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

            if($user->roles == 'admin'){
                return response()->json(['errors'=>['system'=>['You can\'t submit PTT as admin.']]], 422);
            }
            $weekValidation = WeekValidation::where('weekNumber', '=', $request->weekNumber)->where('entryYear', '=', $request->year)->where('user_id', '=', $user->id)->first();
            if(!($weekValidation instanceof WeekValidation)){
                $weekValidation= new WeekValidation();
                $dateGap=$helper->getStartAndEndDateByWeekNumber($request->weekNumber, $request->year);
                $weekValidation->weekNumber=$request->weekNumber;
                $weekValidation->entryYear=$request->year;
                $weekValidation->user_id=$user->id;
                $weekValidation->startDate = $dateGap[0];
                $weekValidation->endDate = $dateGap[1];
                $weekValidation->save();
            }

            if($weekValidation->status != 'entried') {
                return response()->json(['errors'=>['system'=>['You can\'t submit PTT twice.']]], 422);
            }


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
        
            $weekProjects = $weekValidation->week_projects;
            $weekValidation->status='requested';
            if(sizeof($weekProjects) > 0){
                foreach($weekProjects as $wproject){
                    $project_lead = $wproject->project->project_lead;
                    if($project_lead instanceof User){
                        $message = $user->firstName." ". $user->lastName. " submitted PTT for the week ".$request->weekNumber."/".$request->year;
                        $notificationRepository->sendNotification($user, $project_lead, $message, "time-track-project-approval", $user->id.'///'.$request->weekNumber.'///'.$request->year);
                    }
                    $wproject->status="requested";
                    $wproject->save();
                }
            }else {
                $teamLead = $user->dept_team_lead;
                if($teamLead instanceof User){
                    $message = $user->firstName." ". $user->lastName. " submitted PTT for the week ".$request->weekNumber."/".$request->year;
                    $notificationRepository->sendNotification($user, $teamLead, $message, "time-track-final-approval", $user->id.'///'.$request->weekNumber.'///'.$request->year);
                }else {
                    return response()->json(['errors'=>['team_lead'=>['You have no team leads. Check your management flow.']]], 422);
                }
            }
            $weekValidation->save();
            return $weekValidation;
        });
    }

    public function projectLeadApproveWeeklyPtt(Request $request){
        $user = \Auth::user();
        $valid = $this->model->validateRules($request->all(), [
            'week_validation' => 'required:exists:week_validations,id'
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);

        $weekValidation = $this->model->show($request->week_validation);
        return \DB::transaction(function() use ($weekValidation, $user){
            try {
                $notificationRepository = new NotificationRepository();
                $allCompleted=true;
                foreach($weekValidation->week_projects as $wproject){
                    if($wproject->project->project_lead_id == $user->id){
                        $wproject->status="accepted";
                        $wproject->accept_time = new \Datetime();
                        $wproject->accepted_user_id = $user->id;
                        $wproject->save();
                        $message = $user->firstName." ".$user->lastName." has approved ptt related to project ".$wproject->project->projectName." for the week ".$weekValidation->weekNumber."/".$weekValidation->entryYear;
                        $notificationRepository->sendNotification($user, $weekValidation->user, $message, 'ptt-project-lead-approved', $weekValidation->user_id.'///'.$weekValidation->weekNumber.'///'.$weekValidation->entryYear);
                    }else {
                        if($wproject->status != "accepted"){
                            $allCompleted=false;
                        }
                    }
                }
                if($allCompleted){
                    if($weekValidation->user->dept_team_lead instanceof User){
                        $message = "All projects of ".$weekValidation->user->firstName." ".$weekValidation->user->lastName."'s PTT ".$weekValidation->weekNumber."/".$weekValidation->entryYear." has been approved";
                        $notificationRepository->sendNotification($user, $weekValidation->user->dept_team_lead, $message, 'ptt-project-lead-approval-complete', $weekValidation->user_id.'///'.$weekValidation->weekNumber.'///'.$weekValidation->entryYear);
                    }
                }
                return $weekValidation;
            }catch(\Exception $e){
                return response()->json(['errors'=>['approval'=>[$e->getMessage()]]], 422);
            }
        });

        return $weekValidation;
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
