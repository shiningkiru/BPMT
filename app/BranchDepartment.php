<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BranchDepartment extends Model
{
    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branches()
    {
      return $this->belongsTo(Branch::class, 'branches_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function departments()
    {
      return $this->belongsTo(MassParameter::class, 'dept_id');
    }


    /**
     * Get the comments for the blog post.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'branch_dept_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hod()
    {
      return $this->belongsTo(User::class, 'hod_id');
    }
}
