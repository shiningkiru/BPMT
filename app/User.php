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
        'employeeId', 'firstName', 'lastName', 'email', 'mobileNumber', 'password', 'address', 'profilePic', 'dob', 'doj', 'salary', 'bloodGroup', 'relievingDate', 'isActive',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
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
    public function department_hod()
    {
        return $this->hasMany(BranchDepartment::class, 'hod_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function project_team()
    {
        return $this->hasMany(ProjectTeam::class, 'team_user_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function milestones()
    {
        return $this->hasMany(Milestones::class, 'milestone_assigned_to');
    }


    /**
     * user has to do the work
     */
    public function tasks_taken()
    {
        return $this->hasMany(Tasks::class, 'task_assigned_to');
    }


    /**
     * user assigns the work to others
     */
    public function tasks_assigned()
    {
        return $this->hasMany(Tasks::class, 'task_assigned_by');
    }


    /**
     * user asigned sprint to others
     */
    public function assigned_sprints()
    {
        return $this->hasMany(Sprint::class, 'sprint_assigned_by');
    }


    /**
     * user assigns the work to others
     */
    public function handled_sprints()
    {
        return $this->hasMany(Sprint::class, 'sprint_handled_by');
    }


    /**
     * Sprint work log user previous handling the project
     */
    public function sprint_handled_by()
    {
        return $this->hasMany(SprintWorkLog::class, 'sprint_handled_user');
    }


    /**
     * sprint work log user who next handles the project
     */
    public function sprint_moved_to()
    {
        return $this->hasMany(SprintWorkLog::class, 'sprint_next_user');
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
}
