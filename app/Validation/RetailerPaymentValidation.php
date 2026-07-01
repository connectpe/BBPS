<?php

namespace App\Validation;

use Illuminate\Support\Facades\Validator;

class RetailerPaymentValidation
{
    public static function validate($data)
    {
        $messages = [
            'connectionNumber.required' => 'Connection number is required.',
            'connectionNumber.regex' => 'Connection number must be a 10-digit number.',

            'operator.required' => 'Operator is required.',

            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 1.',

            'requestId.required' => 'Request ID is required.',
            'requestId.unique' => 'This Request ID has already been used.',

            'customerMobile.required' => 'Customer mobile number is required.',
            'customerMobile.regex' => 'Customer mobile number must be a 10-digit number.',

            'remitterName.required' => 'Remitter name is required.',
            'remitterName.max' => 'Remitter name cannot exceed 100 characters.',

            'paymentRefID.required' => 'Payment reference ID is required.',
            'paymentRefID.unique' => 'This Payment Reference ID has already been used.',

            'paymentAccountInfo.required' => 'Payment account information is required.',
        ];

        $validator = Validator::make($data, [
            'connectionNumber' => 'required|string|regex:/^[0-9]{10}$/',
            'operator' => 'required',
            'circle' => '',
            'amount' => 'required|numeric|min:1',
            'requestId' => 'required|string|unique:recharge_orders,request_id',
            'customerMobile' => 'required|string|regex:/^[0-9]{10}$/',
            'agentId' => 'required|string',
            'remitterName' => 'required|string|max:100',
            'paymentRefID' => 'required|string|unique:recharge_orders,payment_ref_id',
            'paymentMode' => 'required|string',
            'paymentAccountInfo' => 'required|string|max:100',
        ], $messages);

        return $validator;
    }
}
