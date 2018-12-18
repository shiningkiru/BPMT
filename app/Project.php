<?php

namespace App;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use LogTrait;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projectName', 'description', 'projectCode', 'projectCategory', 'startDate', 'endDate', 'budget', 'status'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function locations()
    {
        return $this->hasMany(Location::class, 'project_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
      return $this->belongsTo(Company::class, 'project_company_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
      return $this->belongsTo(Client::class, 'client_project_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project_lead()
    {
      return $this->belongsTo(Client::class, 'project_lead_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function project_team()
    {
        return $this->hasMany(ProjectTeam::class, 'team_project_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function milestones()
    {
        return $this->hasMany(Milestones::class, 'project_milestone_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function documents()
    {
        return $this->hasMany(DocumentManager::class, 'doc_project_id');
    }
}
