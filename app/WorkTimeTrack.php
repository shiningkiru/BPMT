<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkTimeTrack extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description','takenHour'
    ];

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taskMember()
    {
      return $this->belongsTo(TaskMember::class, 'task_member_identification');
    }
}
