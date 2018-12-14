<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entryTime', 'message', 'targetObjects', 'module', 'linkId', 'objBefore', 'objAfter'
    ];


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entry_by()
    {
      return $this->belongsTo(User::class, 'entry_by');
    }
}
