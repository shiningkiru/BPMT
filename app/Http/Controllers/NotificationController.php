<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use App\Repositories\NotificationRepository;
use App\Http\Controllers\Master\MasterController;

class NotificationController extends MasterController
{
   public function __construct(Notification $notification)
   {
        parent::__construct(new NotificationRepository($notification));
        $this->validateRules = [
            'description' => 'required'
        ];
   }


}
