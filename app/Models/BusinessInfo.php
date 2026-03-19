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
        'individual_photo',
        'business_address_proof_image',
        'business_pan_image',
        'registration_certificate_image',
        'gst_registration_certificate_image',
        'inside_image',
        'outside_image',
        'signed_moa_image',
        'signed_aoa_image',
        'board_resoultion_image',
        'nsdl_declaration_image',
        'itr_filled',
        'itr_file_image',
        'itr_not_filed_reason',
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
        'is_kyc',
        'is_pan_verify',
        'is_cin_verify',
        'is_gstin_verify',
        'is_bank_details_verify',
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
