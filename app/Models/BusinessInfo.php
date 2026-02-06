<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInfo extends Model
{
    protected $fillable = [
        'user_id',
        'business_category_id',
        'business_name',
        'gst_number',
        'business_pan_number',
        'business_email',
        'business_phone',
        'business_document',
        'address',
        'city',
        'state',
        'pincode',
        'business_pan_name',
        'pan_number',
        'pan_owner_name',
        'aadhar_number',
        'aadhar_name',
        'bank_id',
        'pancard_image',
        'aadhar_front_image',
        'aadhar_back_image',
        'business_type',
        'cin_no',
        'is_kyc'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }
}
