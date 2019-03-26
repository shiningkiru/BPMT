<?php
namespace App\Repositories;

use Event;
use App\CustomerCalls;
use App\CustomerOpportunity;
use App\Events\NotificationFired;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class CustomerCallRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new CustomerCalls());
    }
}
?>