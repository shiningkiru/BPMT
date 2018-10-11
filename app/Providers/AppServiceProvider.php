<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Validator;

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
