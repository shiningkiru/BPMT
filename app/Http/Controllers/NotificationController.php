<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use App\Events\NotificationFired;
use Illuminate\Pagination\Paginator;
use App\Repositories\NotificationRepository;
use App\Http\Controllers\Master\MasterController;

class NotificationController extends MasterController
{
   public function __construct()
   {
        parent::__construct(new NotificationRepository());
        $this->validationRules = [
            'title' => 'required',
            'notificationType' => 'required|string',
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
        ];
   }

   public function getMyNotification(Request $request){
       $user=\Auth::user();
        if($request->limitType == 'limited')
            return $this->model->getReceivedNotification($user->id, $request->limitType)->get(); 
        else{
            $currentPage = $request->pageNumber;
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });
            return $this->model->getReceivedNotification($user->id, $request->limitType)->paginate(20); 
        }
   }




}
