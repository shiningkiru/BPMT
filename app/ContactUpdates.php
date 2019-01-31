<?php

namespace App;

use App\Contacts;
use Illuminate\Database\Eloquent\Model;

class ContactUpdates extends Model
{
    protected $fillable = [
        'details'
    ];
    
    public function contact()
    {
      return $this->belongsTo(Contacts::class, 'contact_id');
    }
}
