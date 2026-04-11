<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociatedPartner extends Model
{
    protected $fillable = ['name','referell_url','logo','priority','status','updated_by'];
}
