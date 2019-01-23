<?php

namespace App;

use App\User;
use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    use LogTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company', 'customerNumber', 'streetNo', 'postCode', 'city', 'country', 'officeTel', 'branch', 'homepage', 'email', 'details', 'status'
    ];
 
    /**
     * Get the comments for the blog post.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'customer_project_id');
    }
 
    /**
     * Get the comments for the blog post.
     */
    public function contacts()
    {
        return $this->hasMany(Contacts::class, 'contact_customer_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
      return $this->belongsTo(Company::class, 'customer_company_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsible_person()
    {
      return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
