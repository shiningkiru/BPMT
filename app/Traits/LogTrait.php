<?php
namespace App\Traits;

use App\User;
use App\Repositories\ActivityLogRepository;

trait LogTrait {

    protected static function boot()
    {
        parent::boot();

        static::created(function($module){
            $user = \Auth::user();
            if($user instanceof User){
                $logger = new ActivityLogRepository();
                $logger->log($user->firstName. " created new entry for ".$module->getTable(), 1, $module->getTable(), $module);
            }
        });

        static::updating(function ($module) {
            $user = \Auth::user();
            if($user instanceof User){
                $logger = new ActivityLogRepository();
                $logger->log($user->firstName. ' updating '.$module->getTable(), 2, $module->getTable(), $module->getOriginal(), $module->getDirty());
            }
        });

        static::deleting(function ($module) {
            $user = \Auth::user();
            if($user instanceof User){
                $logger = new ActivityLogRepository();
                $logger->log($user->firstName. ' deleting '.$module->getTable(), 2, $module->getTable(), $module);
            }
        });
    }
}