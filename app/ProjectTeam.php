<?php

namespace App;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class ProjectTeam extends Model
{
  use LogTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status'
    ];


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
      return $this->belongsTo(Project::class, 'team_project_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
      return $this->belongsTo(User::class, 'team_user_id');
    }
}
