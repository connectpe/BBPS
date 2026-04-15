<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\HashHelper;

class ContactController extends Controller
{
    public function createContact(Request $request)
    {
        // dd($request->all(), $request->header());
        $header      = $request->header();
        $client_id = $request->getUser();
        $client_secret = hash('sha512', $request->getPassword());
        // dd($client_id, $client_secret);
        // $userSaltKey = CommonHelper::getUserSalt($request["auth_data"]['user_id']);

        //making hash
        $hash = HashHelper::generate(HashHelper::CREATE_CONTACT, $client_id, $client_secret, $request->all());
        // dd($hash);
        //Storage::put('contactSignatureCreate'.time().'.txt', print_r($hash, true));
        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';
        dd($signature);
        $aaray     = ['user_id' => $request["auth_data"]['user_id'], 'rafifintech' => $hash, 'client' => $signature];
        //Storage::put('contactSignatureStore'.$request["auth_data"]['user_id'].'_'.time().'.txt', print_r($aaray, true));

        $userId    = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];

        $validation = new Validations($request);

        $validator = $validation->addContact();

        $validator->after(function ($validator) use ($request, $userId, $hash, $signature) {

            $User = User::where('id', $userId)->where('is_active', '1')->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'Your account is has been blocked');
            }
            $Contact = Contact::where('reference', $request->referenceId)->count();
            if ($Contact) {
                $validator->errors()->add('referenceId', 'The reference id has already been taken.');
            }

            if ($request->accountType == 'vpa') {
                if (empty($request->vpa)) {
                    $validator->errors()->add('vpa', 'The vpa field is required.');
                } else {
                    if (Contact::where('account_type', 'vpa')->where('vpa_address', $request->vpa)->where('user_id', $userId)->count()) {
                        $validator->errors()->add('vpa', 'The vpa has already been taken.');
                    }
                }
            } else if ($request->accountType == 'card') {
                if (empty($request->cardNumber)) {
                    $validator->errors()->add('cardNumber', 'The card number field is required.');
                } else {
                    if (Contact::where('account_type', 'card')->where('card_number', $request->cardNumber)->where('user_id', $userId)->count()) {
                        $validator->errors()->add('cardNumber', 'The card number has already been taken.');
                    }
                }
            } else {
                if (empty($request->accountNumber) || empty($request->ifsc) || empty($request->bankName)) {
                    $validator->errors()->add('accountNumber', 'The account number and ifsc and bank name field is required.');
                } else {
                    if (Contact::where('account_type', 'bank_account')->where(['account_number' => $request->accountNumber, 'account_ifsc' => $request->ifsc])->where('user_id', $userId)->count()) {
                        $contact = Contact::select('contact_id')->where('account_type', 'bank_account')->where(['account_number' => $request->accountNumber, 'account_ifsc' => $request->ifsc])->where('user_id', $userId)->first();
                        // $validator->errors()->add('accountNumber', 'The account number has already been taken.');
                        //$validator->errors()->add('ifsc', 'The ifsc has already been taken.');
                        return response()->json(["code" => "0x0203", "status" => "MISSING_PARAMETER", "message" => "The account number has already been taken.", "contactId" => $contact->contact_id]);
                    }
                }
            }
        });

        if ($validator->fails()) {
            $message = json_decode(json_encode($validator->errors()), true);
            if (isset($message['accountNumber']) && isset($message['ifsc'])) {
                if (isset($request->accountNumber) && isset($request->ifsc)) {
                    $contact = Contact::select('contact_id')->where('account_type', 'bank_account')->where(['account_number' => $request->accountNumber, 'account_ifsc' => $request->ifsc])->where('user_id', $userId)->first();
                    if (isset($contact)) {
                        $errors = $validator->errors()->first();
                        return ResponseHelper::missing($errors);
                    }
                }
            } else if (isset($message['vpa'])) {
                if (isset($request->vpa)) {
                    $contact = Contact::where('account_type', 'vpa')->where('vpa_address', $request->vpa)->where('user_id', $userId)->first();
                    if (isset($contact)) {
                        $errors = $validator->errors()->first();
                        return ResponseHelper::missing($errors);
                    }
                }
            }

            $errors = $validator->errors()->first();
            return ResponseHelper::missing($errors);
        } else {
            $contact               = new Contact;
            $contact->contact_id   = CommonHelper::getRandomString2('cont');
            $contact->user_id      = $userId;
            $contact->first_name   = self::removeSpecialChar($request->firstName);
            $contact->last_name    = self::removeSpecialChar($request->lastName);
            $contact->email        = $request->email;
            $contact->phone        = $request->mobile;
            $contact->type         = $request->type;
            $contact->reference    = $request->referenceId;
            $contact->account_type = $request->accountType;
            $contact->bank_name    = $request->bankName;

            if ($request->accountType == 'vpa') {
                $contact->vpa_address = $request->vpa;
            } elseif ($request->accountType == 'card') {
                $contact->card_number = $request->cardNumber;
            } else {
                $contact->account_number = $request->accountNumber;
                $contact->account_ifsc   = CommonHelper::case($request->ifsc, 'u');
            }

            $contact->save();
            $contactInfo = Contact::select(self::columnSelectResponse($request->accountType))->where('contact_id', $contact->contact_id)->first();

            return ResponseHelper::success('Contact created successfully', $contactInfo, '200');
        }
        return ResponseHelper::failed('Record not created successfully');
    }
}
