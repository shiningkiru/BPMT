<?php
namespace App\Repositories;

use Event;
use App\Contacts;
use App\Events\NotificationFired;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class ContactRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Contacts());
    }

    public function getCustomers($customerId=null,$status="all"){
        $query = $this->model->orderBy('id','DESC');
        if($customerId!= null){
            $query=$query->where('contact_customer_id','=',$customerId);
        }
        if($status!="all"){
            $query=$query->where('status','=',$status);
        }
        return $query;
    }
}
?>