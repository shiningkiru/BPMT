<?php

namespace App;

use App\Sprint;
use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class Milestones extends Model
{
    use LogTrait;
    
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
     * Get the comments for the blog post.
     */
    public function sprints()
    {
        return $this->hasMany(Sprint::class, 'milestone_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function documents()
    {
        return $this->hasMany(DocumentManager::class, 'doc_milestone_id');
    }
}
