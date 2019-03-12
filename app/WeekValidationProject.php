<?php

namespace App;

use App\Project;
use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class WeekValidationProject extends Model
{
    use LogTrait;

    public $timestamps = false;
    
    protected $fillable = [
        'status', 'accept_time'
    ];

    
    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function week_validation()
    {
      return $this->belongsTo(WeekValidation::class, 'week_validation_id');
    }

    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
