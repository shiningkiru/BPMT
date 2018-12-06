<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//version 1 apis
Route::prefix('v1')->group(function () {
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::delete('user/{id}', 'AuthController@deleteUser');
        Route::get('current-user', 'AuthController@user');
        Route::post('auth/logout', 'AuthController@logout');
        Route::get('user', 'UserController@getAllUsers');
        Route::get('user/admin', 'UserController@adminShow');
        Route::get('user/hr', 'UserController@hrShow');
        Route::get('user/management', 'UserController@managementShow');
        Route::get('user/team-lead', 'UserController@teamleadShow');
        Route::get('user/project-lead', 'UserController@projectleadShow');
        Route::get('user/employee', 'UserController@employeeShow');
        Route::post('user/reset-password', 'UserController@resetPassword');
        Route::get('user/{id}', 'AuthController@singleUser');
       
              
    });
        Route::get('user/show-email/{email}', 'UserController@show');
        Route::get('user/filter/{id}', 'UserController@designationFilter');    


    Route::middleware('jwt.refresh')->get('/token/refresh', 'AuthController@refresh');
    
    Route::post('user', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('forgot-password', 'AuthController@forgotVerification');
    Route::post('forgot-password/reset', 'AuthController@forgotPasswordReset');


    //company routes
    Route::post('company', 'CompanyController@create');
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('company/{id}', 'CompanyController@show');
        Route::get('company', 'CompanyController@index');
        Route::delete('company/{id}', 'CompanyController@delete');
    });

    //designation routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('designation/{id}', 'DesignationController@show');
        Route::get('designation', 'DesignationController@index');
        Route::post('designation', 'DesignationController@create');
        Route::delete('designation/{id}', 'DesignationController@delete');
    });

    //department routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('department/{id}/users', 'DepartmentController@departmentUser');
        Route::get('department/{id}', 'DepartmentController@show');
        Route::get('department', 'DepartmentController@index');
        Route::post('department', 'DepartmentController@create');
        Route::delete('department/{id}', 'DepartmentController@delete');
    });

    //branch routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('branch/{id}', 'BranchController@show');
        Route::get('branch', 'BranchController@index');
        Route::post('branch', 'BranchController@create');
        Route::delete('branch/{id}', 'BranchController@delete');
    });
 
    //client routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('client/{id}', 'ClientController@show');
        Route::get('client', 'ClientController@index');
        Route::post('client', 'ClientController@create');
        Route::delete('client/{id}', 'ClientController@delete');
    });

     //Project routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('project/task-chart-list', 'ProjectController@taskChartList');
        Route::post('project', 'ProjectController@create');
        Route::get('project', 'ProjectController@index');
        Route::get('project/assigned', 'ProjectController@assignedPrjects');
        Route::get('project/{id}', 'ProjectController@show');
        Route::get('project/by-client/{id}', 'ProjectController@byClient');
    
        Route::delete('project/{id}', 'ProjectController@delete');
        Route::post('project/search-project', 'ProjectController@searchproject');
        Route::post('project-team', 'ProjectTeamController@create');
        Route::delete('project-team/{id}', 'ProjectTeamController@delete');
        Route::get('project-team/delete/{id}/{prid}', 'ProjectTeamController@deletebyUserAndProject');
        Route::get('project-team/{id}', 'ProjectTeamController@show');
        Route::get('project-team/members/{id}', 'ProjectTeamController@teamMembers');
        Route::get('project-team/total-team-members/{id}', 'ProjectTeamController@TotalteamMembers');
    });
   
    
    //Location routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('location/{id}', 'LocationController@show');
        Route::get('location', 'LocationController@index');
        Route::post('location', 'LocationController@create');
        Route::delete('location/{id}', 'LocationController@delete');
    });

    //Milestone routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('milestone/{id}', 'MilestonesController@show');
        Route::get('milestone/by-project/{id}', 'MilestonesController@index');
        Route::post('milestone', 'MilestonesController@create');
        Route::delete('milestone/{id}', 'MilestonesController@delete');
        Route::get('milestone/total-milestones/{id}', 'MilestonesController@totalMilestones');
    });

    //Sprint routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('sprint/{id}', 'SprintController@show');
        Route::get('sprint/by-milestone/{id}', 'SprintController@index');
        Route::post('sprint', 'SprintController@create');
        Route::delete('sprint/{id}', 'SprintController@delete');
        Route::get('sprint/total-sprints/{id}', 'SprintController@totalSprints');
     });

    //Task routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('task/{id}', 'TaskController@show');
        Route::get('task/by-sprints/{id}', 'TaskController@index');
        Route::post('task', 'TaskController@create');
        Route::delete('task/{id}', 'TaskController@delete');
        Route::get('task/chart/{id}/{status}', 'TaskController@showChart');
        Route::get('task/total-tasks/{id}', 'TaskController@totalTasks');
    });

    //Task member routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('task-member/current-assigned-tasks', 'TaskMemberController@getCurrentAssignedTasks');
        Route::get('task-member/all-assigned-tasks', 'TaskMemberController@getAllAssignedTasks');
        Route::get('task-member/{id}', 'TaskMemberController@getAssignedMembers');
        Route::post('task-member', 'TaskMemberController@addMember');
        Route::delete('task-member/{id}', 'TaskMemberController@removeMember');
    });

    //Task member routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::post('time-track/add-time', 'WorkTrackController@addMyTime');
        Route::get('time-track/get-by-task', 'WorkTrackController@getTaskLogs');
        Route::get('time-track/get-by-task-and-member', 'WorkTrackController@getTaskMemberLogs');
        Route::post('time-track/get-logs-by-week', 'WorkTrackController@getLogsByWeekAccordingUser');
        Route::post('time-track/get-logs-by-week/single-user', 'WorkTrackController@getLogsByWeekAccordingLoggedInUser');
        Route::post('task-member/current-assigned-tasks/project', 'WorkTrackController@getCurrentAssignedTasksOnProject');
        Route::post('task-member/current-assigned-tasks', 'WorkTrackController@getCurrentAssignedTasks');
        Route::post('task-member/all-assigned-tasks/project', 'WorkTrackController@getAllAssignedTasksOnProject');
        Route::post('task-member/all-assigned-tasks', 'WorkTrackController@getAllAssignedTasks');
        Route::post('task-member/user-bow', 'WorkTrackController@getMyWeeklyBow');
    });

    //Access previlege routes
    Route::get('access-previlege/roles', 'AccessPrevilegesController@getRoles');
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::post('access-previlege', 'AccessPrevilegesController@updatePrevilages');
        Route::get('access-previlege', 'AccessPrevilegesController@getAllAccessPrevileges');
        Route::get('access-previlege/user', 'AccessPrevilegesController@getAccessForUser');
    });

    //Document Manager routes
    Route::group(['middleware' => 'jwt.auth'], function(){
        Route::get('document-manager/{id}', 'DocumentManagerController@show');
        Route::get('document-manager', 'DocumentManagerController@index');
        Route::post('document-manager', 'DocumentManagerController@create');
        Route::delete('document-manager/{id}', 'DocumentManagerController@delete');
        Route::get('document-manager/download-file/{id}', 'DocumentManagerController@downloadFile');
    });

    
    Route::post('notification', 'NotificationController@create');
    Route::put('notification/{id}', 'NotificationController@update');
    Route::get('notification', 'NotificationController@index');
    Route::get('notification/{id}', 'NotificationController@get');

});