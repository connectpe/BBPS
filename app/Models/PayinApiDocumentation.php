<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayinApiDocumentation extends Model
{
    protected $fillable = ['authorization', 'request_header', 'generate_payment_response', 'generate_payment_description', 'check_status_response', 'check_status_description', 'callback_examples_response', 'callback_examples_description', 'updated_by'];
}
