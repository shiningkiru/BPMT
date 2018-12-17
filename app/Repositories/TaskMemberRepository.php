<?php
namespace App\Repositories;

use App\TaskMember;
use App\Repositories\Master\Repository;
use Illuminate\Database\Eloquent\Model;

class TaskMemberRepository extends Repository {
    
    // Constructor to bind model to repo
    public function __construct()
    {
        parent::__construct(new TaskMember());
    }

    public function findByUserAndTask($user_id, $task_id){
        $taskMember = $this->model->where('task_identification', '=', $task_id)->where('member_identification', '=', $user_id)->first();
        if(!$taskMember instanceof TaskMember)
            return null;
        return $taskMember;
    }
}