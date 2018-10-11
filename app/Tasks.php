<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'taskName', 'description', 'startDate', 'endDate', 'estimatedHours', 'takenHours', 'status', 'priority'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function depended_tasks()
    {
        return $this->hasMany(Tasks::class, 'dependent_task_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function depended_parent_task()
    {
      return $this->belongsTo(Tasks::class, 'dependent_task_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sprint()
    {
      return $this->belongsTo(Sprint::class, 'sprint_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_working()
    {
      return $this->belongsTo(User::class, 'task_assigned_to');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_assigned_by()
    {
      return $this->belongsTo(User::class, 'task_assigned_by');
    }

    /**
     * Get the comments for the blog post.
     */
    public function sprints()
    {
        return $this->hasMany(Sprint::class, 'task_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function documents()
    {
        return $this->hasMany(DocumentManager::class, 'doc_task_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function activity_log()
    {
        return $this->hasMany(ActivityLog::class, 'activity_tasks_id');
    }
}
