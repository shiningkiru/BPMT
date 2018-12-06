<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable= [
        'title', 'description', 'linkId', 'urlLink', 'isRead', 'notificationType', 'firstDeletedUser'
    ];

    
    public function fromUser()
    {
      return $this->belongsTo(User::class, 'from_user_id');
    }
    
    public function toUser()
    {
      return $this->belongsTo(User::class, 'to_user_id');
    }
}
