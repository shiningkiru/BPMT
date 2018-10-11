<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyClients extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doj'
    ];

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
      return $this->belongsTo(Client::class, 'company_client_id');
    }

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
      return $this->belongsTo(Company::class, 'client_company_id');
    }
}
