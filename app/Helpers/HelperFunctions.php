<?php
namespace App\Helpers;

use App\User;

class HelperFunctions{
    public function getLastEmployeeId(){
        
        $user = User::max('employeeId');
        return $user+1;
    }

    public function getRoles(){
        $roles = [
            'admin', 
            'management', 
            'hr', 
            'team-lead', 
            'project-lead', 
            'employee'
        ];
        return $roles;
    }

    public function getModels(){
        $modules = [
            'project', 
            'milestone', 
            'sprint', 
            'task', 
            'client', 
            'user', 
            'task_work_log', 
            'settings', 
            'access_previlages'
        ];
        return $modules;
    }
}
?>