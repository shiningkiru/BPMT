<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'mobileNumber', 'secondaryMobileNumber', 'email', 'secondaryEmail', 'profilePic', 'address', 'longitude', 'latitude'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function company()
    {
        return $this->hasMany(CompanyClients::class, 'company_client_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'client_project_id');
    }
}
