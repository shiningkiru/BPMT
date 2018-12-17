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

    public function logger($message, $targeted_number_of_objects, $module, Model $object=null, $oldObject){
        // dump($object);
        // dump($object->getChanges());
        // dump($object->getOriginal('budget'));
        
        if($processable=false):
            $log = new ActivityLog();
            $log->entryTime = new \Datetime();
            $log->message = $message;
            $log->targetObjects = $targeted_number_of_objects;
            $log->module = $module;
            $log->linkId = $object->id;
            $log->objBefore = json_encode($object);
            $log->objAfter = json_encode($object);
            $log->entry_by = \Auth::user()->id;
            try{
                $log->save();
            }catch(\Exception $e){dd($e);
                dd($e);
            }
        endif;
    }
}