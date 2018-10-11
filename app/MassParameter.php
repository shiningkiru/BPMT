<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MassParameter extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'type'
    ];


    /**
     * Get the comments for the blog post.
     */
    public function branch_departments()
    {
        return $this->hasMany(BranchDepartment::class, 'dept_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
      return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'designation_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function project_team()
    {
        return $this->hasMany(ProjectTeam::class, 'team_role_id');
    }
}
