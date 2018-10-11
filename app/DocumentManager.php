<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentManager extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'fileUrl', 'description', 'documentType'
    ];


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
      return $this->belongsTo(Project::class, 'doc_project_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tasks()
    {
      return $this->belongsTo(Tasks::class, 'doc_task_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function milestone()
    {
      return $this->belongsTo(Milestones::class, 'doc_milestone_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sprint()
    {
      return $this->belongsTo(Sprint::class, 'doc_sprint_id');
    }
}
