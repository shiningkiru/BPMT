<?php

namespace App;

use App\WeekValidation;
use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class WorkTimeTrack extends Model
{
    use LogTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description','takenHours','dateOfEntry','isUpdated'
    ];

    
    public function taskMember()
    {
      return $this->belongsTo(TaskMember::class, 'task_member_identification');
    }
    
    
    public function weekNumber()
    {
        return $this->hasMany(WeekValidation::class, 'weekNumber');
    }
}
