<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'mobileNumber', 'logo', 'address', 'longitude', 'latitude'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function branches()
    {
        return $this->hasMany(Branch::class, 'br_company_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function mass_parameters()
    {
        return $this->hasMany(MassParameter::class, 'ms_company_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'client_company_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'project_company_id');
    }
}
