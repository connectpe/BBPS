<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebHookUrl extends Model
{
    protected $fillable = ['user_id', 'url','updated_by'];
}
