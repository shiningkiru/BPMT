<?php

namespace App;

use App\Customer;
use App\WeekValidation;
use App\Traits\LogTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use LogTrait;
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
     * Get the comments for the blog post.
     */
    public function weekValidation()
    {
        return $this->hasMany(WeekValidation::class, 'user_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function handlingCustomer()
    {
        return $this->hasMany(Customer::class, 'responsible_user_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function weekValidationAccepted()
    {
        return $this->hasMany(WeekValidation::class, 'accepted_user_id');
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

    /**
     * Get the comments for the blog post.
     */
    public function notificationSent()
    {
        return $this->hasMany(User::class, 'from_user_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function notificationReceived()
    {
        return $this->hasMany(User::class, 'to_user_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function todos()
    {
        return $this->hasMany(Todo::class, 'to_do_resp_user');
    }
}
