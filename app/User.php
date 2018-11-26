<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employeeId', 'firstName', 'lastName', 'email', 'mobileNumber', 'password', 'reset_token', 'address', 'profilePic', 'dob', 'doj', 'salary', 'bloodGroup', 'relievingDate', 'isActive', 'roles'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'reset_token',
    ];


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch_departments()
    {
      return $this->belongsTo(BranchDepartment::class, 'branch_dept_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function designation()
    {
      return $this->belongsTo(MassParameter::class, 'designation_id');
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
    public function project_team()
    {
        return $this->hasMany(ProjectTeam::class, 'team_user_id');
    }


    /**
     * user assigns the work to others
     */
    public function leading_project()
    {
        return $this->hasMany(Project::class, 'project_lead_id');
    }


    /**
     * user assigns the work to others
     */
    public function activity_logs()
    {
        return $this->hasMany(ActivityLog::class, 'entry_by');
    }

    /**
     * Get the comments for the blog post.
     */
    public function taskMember()
    {
        return $this->hasMany(TaskMember::class, 'member_identification');
    }
}
