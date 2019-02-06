<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $fillable = [
        'dateFor', 'endDate', 'status', 'details', 'fullDay', 'linkId', 'relatedTo'
    ];


    


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsible()
    {
      return $this->belongsTo(User::class, 'to_do_resp_user');
    }
}
