<?php

namespace App;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use LogTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branchName', 'branchCode', 'address', 'longitude', 'latitude'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function branch_departments()
    {
        return $this->hasMany(BranchDepartment::class, 'branches_id');
    }


    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
      return $this->belongsTo(Company::class, 'br_company_id');
    }

}
