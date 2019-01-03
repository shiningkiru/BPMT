<?php

namespace App;

use App\User;
use App\WorkTimeTrack;
use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class WeekValidation extends Model
{
  use LogTrait;
    protected $fillable = [
        'weekNumber', 'entryYear', 'status', 'startDate', 'endDate', 'request_time', 'accept_time'
    ];

    
    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function work_tracks()
    {
      return $this->belongsTo(WorkTimeTrack::class, 'weekNumber');
    }

    
    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
      return $this->belongsTo(User::class, 'user_id');
    }

    
    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accepted_user()
    {
      return $this->belongsTo(User::class, 'accepted_user_id');
    }
}
