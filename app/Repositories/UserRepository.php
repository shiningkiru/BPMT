<?php
namespace App\Repositories;

use App\User;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new User());
    }

    public function findByRole($role){
        return $this->model->where('roles','=',$role)->get();
    }
}