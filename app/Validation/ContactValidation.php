<?php

namespace App\Validation;

use Illuminate\Support\Facades\Validator;

class ContactValidation
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    private function validation($key)
    {
        $validation = [
            'name' => ['required', 'string', 'min:2', 'regex:/^([A-Za-z .()]+)$/'],
            'lastname' => ['nullable', 'string', 'min:2', 'regex:/^([A-Za-z .()]+)$/'],
            'email' => ['required', 'email'],
            'mobile' => ['required', 'digits:10'],

            'accountType' => ['required', 'in:bank_account,vpa,card'],
            'type' => ['required', 'in:vendor,customer,employee,self'],

            'accountNumber' => ['numeric', 'digits_between:8,20'],
            'ifsc' => ['string', 'size:11', 'regex:/^[A-Za-z]{4}0[A-Z0-9]{6}$/'],
            'bankName' => ['string', 'min:3'],

            'vpa' => ['string', 'min:3'],

            'cardNumber' => ['numeric', 'digits_between:10,16'],

            'referenceId' => ['required', 'string', 'min:5']
        ];

        return $validation[$key] ?? [];
    }

    public function addContact()
    {
        $validations = [
            'firstName'   => $this->validation('name'),
            'lastName'    => $this->validation('lastname'),
            'email'       => $this->validation('email'),
            'mobile'      => $this->validation('mobile'),
            'type'        => $this->validation('type'),
            'accountType' => $this->validation('accountType'),
            'referenceId' => $this->validation('referenceId'),
        ];

        // Dynamic validation based on accountType
        if ($this->data->accountType == 'bank_account') {
            $validations['accountNumber'] = array_merge(['required'], $this->validation('accountNumber'));
            $validations['ifsc'] = array_merge(['required'], $this->validation('ifsc'));
            $validations['bankName'] = array_merge(['required'], $this->validation('bankName'));
        }

        if ($this->data->accountType == 'vpa') {
            $validations['vpa'] = array_merge(['required'], $this->validation('vpa'));
        }

        if ($this->data->accountType == 'card') {
            $validations['cardNumber'] = array_merge(['required'], $this->validation('cardNumber'));
        }

        return Validator::make($this->data->all(), $validations);
    }
}
