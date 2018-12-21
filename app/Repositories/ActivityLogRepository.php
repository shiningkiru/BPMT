<?php
namespace App\Repositories;

use App\ActivityLog;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class ActivityLogRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new ActivityLog());
    }

    public function log($message, $targeted_number_of_objects, $module, $object=null, $changes=null){
        $log = new ActivityLog();
        $log->entryTime = new \Datetime();
        $log->message = $message;
        $log->targetObjects = $targeted_number_of_objects;
        $log->module = $module;
        $log->linkId = $object['id'];
        $log->original = json_encode($object);
        $log->changes = json_encode($changes);
        $log->entry_by = \Auth::user()->id;
        try{
            $log->save();
        }catch(\Exception $e){
            
        }
    }

    public function getLogs(\Datetime $fromDate = null, \Datetime $toDate = null, $user_id = null){
        $logs = $this->model->leftJoin('users','entry_by','=','users.id');
        if($fromDate != null && $toDate != null){
            $logs = $logs->whereBetween('entryTime',[$fromDate, $toDate]);
        }
        if($fromDate != null && $toDate == null){
            $logs = $logs->where('entryTime','=', $fromDate);
        }
        if($user_id != null){
            $logs = $logs->where('entry_by', '=', $user_id);
        }
        return $logs->orderBy('entryTime','DESC');
    }
}