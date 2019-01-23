<?php

namespace App\Providers;

use App\User;
use Validator;
use App\Sprint;
use App\Project;
use App\Customer;
use App\Milestones;
use App\MassParameter;
use App\Repositories\SprintRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Repositories\MilestoneRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
        Validator::extend('phone_number', function($attribute, $value, $parameters)
        {
            return preg_match('/^[0-9]{10,}+$/', $value);
        }, 'Provide valid :phone_number');

        Validator::replacer('phone_number', function($message, $attribute, $rule, $parameters){
            return str_replace(':phone_number', ucfirst(preg_replace('/([A-Z])/', ' $1', $attribute)), $message);
        });


        Validator::extend('time_format', function($attribute, $value, $parameters)
        {
            return preg_match('/^\d+(:\d{2})?(:\d{2})?$/', $value);
        }, 'Provide valid :time_format');

        Validator::replacer('time_format', function($message, $attribute, $rule, $parameters){
            return str_replace(':time_format', ucfirst(preg_replace('/([A-Z])/', ' $1', $attribute)), $message);
        });

        Validator::extend('department', function($attribute, $value, $parameters)
        {
            $mass=MassParameter::where('id','=',$value)->where('type','=','department')->get();
            if(sizeof($mass)>0)
                return true;
            return false;
        }, 'Provide valid department id');

        Validator::extend('designation', function($attribute, $value, $parameters)
        {
            $mass=MassParameter::where('id','=',$value)->where('type','=','designation')->get();
            if(sizeof($mass)>0)
                return true;
            return false;
        }, 'Provide valid designation id'); 

        Validator::extend('custom_password', function($attribute, $value, $parameters)
        {
            if($parameters[0] == null)
                if(empty($value))
                    return false;
                else
                    return true;
            else
                return true;
        }, 'Password field is required');

        Validator::extend('token', function($attribute, $value, $parameters)
        { 
            $user=User::where('email','=',$parameters[0])->where('reset_token','=',urldecode($value))->first();
            if(!($user instanceof User))
                return false;
            else
                return true;
        }, 'Invalide link');


        Validator::extend('milestone_number', function($attribute, $value, $parameters)
        { 
            $milestoneRepository = new MilestoneRepository();
            $activeMilestone = $milestoneRepository->findActiveMilestone($value)->first();
            if($activeMilestone instanceof Milestones){
                if(!empty($parameters[0]) ){
                    if($activeMilestone->id != $parameters[0])
                        return false;
                }else{
                    return false;
                }
            }
            return true;
        }, 'Can not add another milestone before completing previous one.');


        Validator::extend('sprint_number', function($attribute, $value, $parameters)
        { 
            $sprintRepository = new SprintRepository();
            $activeSprint = $sprintRepository->findActiveSprint($value)->first();
            if($activeSprint instanceof Sprint){
                if(!empty($parameters[0]) ){
                    if($activeSprint->id != $parameters[0])
                        return false;
                }else{
                    return false;
                }
            }
            return true;
        }, 'Can not add another sprint before completing previous one.');


        Validator::extend('todo_link_id', function($attribute, $value, $parameters)
        { 
            $relatedTo = $parameters[0];
            if($relatedTo == 'customer'){
                $customer = Customer::find($value);
                if(!($customer) instanceof Customer)
                    return false;
            }elseif($relatedTo == 'project'){
                $project = Project::find($value);
                if(!($project) instanceof Project)
                    return false;
            }

            return true;

        }, 'Invalid link id provided');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
