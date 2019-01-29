<?php

namespace App;

use App\ContactUpdates;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstName', 'lastName', 'designation', 'streetNo', 'postalCode', 'city', 'country', 'telephone', 'mobile', 'email', 'dateOfBirth', 'interests', 'status'
    ];

    /**
     * A message belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
      return $this->belongsTo(Customer::class, 'contact_customer_id');
    }


    public function updates()
    {
        return $this->hasMany(ContactUpdates::class, 'contact_id');
    }


}
