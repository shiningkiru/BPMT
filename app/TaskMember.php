<?php

namespace App;

use App\User;
use App\Tasks;
use App\WorkTimeTrack;
use Illuminate\Database\Eloquent\Model;

class TaskMember extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'estimatedHours','takenHours'
    ];

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
      return $this->belongsTo(Tasks::class, 'task_identification');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
      return $this->belongsTo(User::class, 'member_identification');
    }

    /**
     * Get the comments for the blog post.
     */
    public function workTrack()
    {
        return $this->hasMany(WorkTimeTrack::class, 'task_member_identification');
    }
}
