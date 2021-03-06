<?php

namespace App;

use App\Milestones;
use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    use LogTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sprintTitle', 'startDate', 'endDate', 'status', 'estimatedHours', 'takenHours', 'priority'
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
    public function milestone()
    {
      return $this->belongsTo(Milestones::class, 'milestone_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'sprint_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function documents()
    {
        return $this->hasMany(DocumentManager::class, 'doc_sprint_id');
    }
}
