<?php
namespace App\Repositories;

use Event;
use App\User;
use App\Notification;
use App\Events\NotificationFired;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class NotificationRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Notification());
    }

    public function sendNotification(User $from_user, User $to_user, $message, $notification_type, $linkId=null){
        $notification = new Notification();
        $notification->title = $message;
        $notification->notificationType=$notification_type;
        $notification->linkId = $linkId;
        $notification->from_user_id = $from_user->id;
        $notification->to_user_id=$to_user->id;
        $notification->save();
        Event::fire(new NotificationFired($to_user->id));
    }

    public function getReceivedNotification($user_id, $limit){
        $notifications = $this->model->where('to_user_id', '=', $user_id)->orderBy('created_at','DESC');
        if($limit == 'limited')
            $notifications=$notifications->limit(5);
        return $notifications->get();
    }
}