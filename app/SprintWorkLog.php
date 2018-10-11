<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SprintWorkLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'startDate','endDate', 'totalHours', 'work'
    ];

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
    public function sprint_handled_by()
    {
      return $this->belongsTo(User::class, 'sprint_handled_user');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sprint_moved_to()
    {
      return $this->belongsTo(User::class, 'sprint_next_user');
    }
}
