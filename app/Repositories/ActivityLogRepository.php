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

    public static function logger($message, $targeted_number_of_objects, $module, Model $objBefore=null, Model $objAfter=null, $linkId=null){
        $processable = false;
        if($targeted_number_of_objects == 2 && $objBefore!=null && $objAfter=null){
            $objAfterArray = $objAfter->toArray();
            $objBeforeArray = $objBefore->toArray();
            $objBefore = array_diff($objBeforeArray, $objAfterArray);
            $objAfter = array_diff($objAfterArray, $objBeforeArray);
            $processable = true;
        }else if($targeted_number_of_objects == 1 && $objBefore!=null){
            $processable = true;
            $objBefore = $objBefore->toArray();
            $objAfter = [];
        }else if($targeted_number_of_objects == 0){
            $processable = true;
            $objBefore = [];
            $objAfter = [];
        }
        if($processable):
            $data = [
                'entry_by' => \Auth::user()->id,
                'entryTime' => new \Datetime(),
                'message' => $message,
                'targetObjects' => $targeted_number_of_objects,
                'module' => $module,
                'linkId' => $linkId,
                'objBefore' => json_encode($objBefore),
                'objAfter' => json_encode($objAfter)
            ];
            dd($data);
            try{
                self::create($data);
            }catch(\Exception $e){
                dd("hello");
            }
        endif;
    }
}