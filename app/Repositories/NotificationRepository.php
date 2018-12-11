<?php
namespace App\Repositories;

use App\Notification;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class NotificationRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Notification());
    }

    public function getReceivedNotification($user_id, $limit){
        $notifications = $this->model->where('to_user_id', '=', $user_id)->orderBy('created_at','DESC');
        if($limit == 'limited')
            $notifications=$notifications->limit(5);
        return $notifications->get();
    }
}