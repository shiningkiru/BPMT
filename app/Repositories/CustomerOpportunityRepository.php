<?php
namespace App\Repositories;

use Event;
use App\CustomerOpportunity;
use App\Events\NotificationFired;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class CustomerOpportunityRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new CustomerOpportunity());
    }
}
?>