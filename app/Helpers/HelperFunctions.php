<?php
namespace App\Helpers;

use App\User;

class HelperFunctions{
    public function getLastEmployeeId(){
        
        $user = User::max('employeeId');
        return $user+1;
    }
}
?>