<?php
namespace App\Repositories;

use App\Todo;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class TodoRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new Todo());
    }
}