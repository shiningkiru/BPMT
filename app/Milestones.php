<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Milestones extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'startDate', 'endDate', 'estimatedHours', 'progress', 'status'
    ];


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
      return $this->belongsTo(Project::class, 'project_milestone_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
      return $this->belongsTo(User::class, 'milestone_assigned_to');
    }

    /**
     * Get the comments for the blog post.
     */
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'milestone_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function documents()
    {
        return $this->hasMany(DocumentManager::class, 'doc_milestone_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function activity_log()
    {
        return $this->hasMany(ActivityLog::class, 'activity_milestone_id');
    }
}
