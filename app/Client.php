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
        'name', 'mobileNumber', 'secondaryMobileNumber', 'email', 'secondaryEmail', 'profilePic', 'address', 'longitude', 'latitude', 'status'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'client_project_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
      return $this->belongsTo(Company::class, 'client_company_id');
    }
}
