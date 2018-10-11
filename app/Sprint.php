<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sprintTitle', 'startDate', 'endDate', 'status', 'priority', 'type'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function depended_sprint()
    {
        return $this->hasMany(Sprint::class, 'dependent_sprint_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function depended_parent_sprint()
    {
      return $this->belongsTo(Sprint::class, 'dependent_sprint_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
      return $this->belongsTo(Tasks::class, 'task_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sprint_assigned()
    {
      return $this->belongsTo(User::class, 'sprint_assigned_by');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sprint_handled()
    {
      return $this->belongsTo(User::class, 'sprint_handled_by');
    }

    /**
     * Get the comments for the blog post.
     */
    public function sprint_work_log()
    {
        return $this->hasMany(SprintWorkLog::class, 'sprint_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function documents()
    {
        return $this->hasMany(DocumentManager::class, 'doc_sprint_id');
    }
}
