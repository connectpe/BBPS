<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVerificationResponse extends Model
{
    protected $fillable = ['user_id','document_type', 'verification_response'];

    protected $casts = ['verification_response' => 'array'];
}
