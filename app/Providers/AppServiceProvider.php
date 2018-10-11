<?php

namespace App\Providers;

use App\User;
use Validator;
use App\MassParameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
