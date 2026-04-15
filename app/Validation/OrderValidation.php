<?php

namespace App\Validation;

use Illuminate\Support\Facades\Validator;

class OrderValidation
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    private function validation($key)
    {
        $validation = [

           
            'amount' => ['required','numeric','min:1'],

          
            'paymentPurpose' => ['required','string','min:3'],

          
            'paymentMode' => ['required','in:IMPS,NEFT,RTGS,UPI,imps,neft,rtgs,upi'],

           
            'contactId' => ['required','string','exists:contacts,contact_id'],

        
            'required' => ['required'],

           
            'string' => ['nullable','string','max:255'],
        ];

        return $validation[$key] ?? [];
    }

    public function addOrder()
    {
        $validations = [
            'amount'        => $this->validation('amount'),
            'purpose'       => $this->validation('paymentPurpose'),
            'mode'          => $this->validation('paymentMode'),
            'contactId'     => $this->validation('contactId'),
            'clientRefId'   => ['required','string','min:5'],
            'udf1'          => $this->validation('string'),
            'udf2'          => $this->validation('string'),
        ];

        return Validator::make($this->data->all(), $validations);
    }
}