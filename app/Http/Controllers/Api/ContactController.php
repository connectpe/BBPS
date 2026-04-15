<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponseHelper;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\HashHelper;
use App\Models\Contact;
use App\Models\User;
use App\Validation\ContactValidation;


class ContactController extends Controller
{

    public function createContact(Request $request)
    {
        // Get credentials
        $client_id = $request->getUser();
        $client_secret = hash('sha512', $request->getPassword());

        // Only required params (IMPORTANT for signature)
        $params = $request->only([
            'firstName',
            'lastName',
            'email',
            'mobile',
            'type',
            'accountType',
            'accountNumber',
            'bankName',
            'ifsc',
            'vpa',
            'cardNumber',
            'referenceId'
        ]);

        // Generate signature
        $hash = HashHelper::generate(
            HashHelper::CREATE_CONTACT,
            $client_id,
            $client_secret,
            $params
        );
        
        // Get client signature
        $signature = $request->header('signature');

        // If mismatch → reject
        if (!hash_equals($hash, $signature)) {
            return ApiResponseHelper::failed('Unauthorized: Invalid signature');
        }

        $userId = $request["auth_data"]['user_id'];

        // Validation
        $validation = new ContactValidation($request);
        $validator = $validation->addContact();

        $validator->after(function ($validator) use ($request, $userId) {

            // User check
            $user = User::where('id', $userId)
                ->where('status', '1')
                ->first();

            if (!$user) {
                $validator->errors()->add('userId', 'Your account has been inactive.');
                return;
            }

            // Reference check
            if (Contact::where('reference_id', $request->referenceId)->exists()) {
                $validator->errors()->add('referenceId', 'Reference ID already exists.');
            }

            // Account type logic
            switch ($request->accountType) {

                case 'vpa':
                    if (empty($request->vpa)) {
                        $validator->errors()->add('vpa', 'VPA is required.');
                        return;
                    }

                    if (Contact::where('account_type', 'vpa')
                        ->where('vpa_address', $request->vpa)
                        ->where('user_id', $userId)
                        ->exists()
                    ) {

                        $validator->errors()->add('vpa', 'VPA already exists.');
                    }
                    break;

                case 'card':
                    if (empty($request->cardNumber)) {
                        $validator->errors()->add('cardNumber', 'Card number is required.');
                        return;
                    }

                    if (Contact::where('account_type', 'card')
                        ->where('card_number', $request->cardNumber)
                        ->where('user_id', $userId)
                        ->exists()
                    ) {

                        $validator->errors()->add('cardNumber', 'Card number already exists.');
                    }
                    break;

                case 'bank_account':
                default:
                    if (empty($request->accountNumber) || empty($request->ifsc) || empty($request->bankName)) {
                        $validator->errors()->add('accountNumber', 'Account number, IFSC and bank name are required.');
                        return;
                    }

                    if (Contact::where('account_type', 'bank_account')
                        ->where('account_number', $request->accountNumber)
                        ->where('account_ifsc', $request->ifsc)
                        ->where('user_id', $userId)
                        ->exists()
                    ) {

                        $validator->errors()->add('accountNumber', 'Bank account already exists.');
                    }
                    break;
            }
        });

        // Validation failed
        if ($validator->fails()) {
            return ApiResponseHelper::missing($validator->errors()->first());
        }

        // Save contact
        $contact = new Contact;
        $contact->contact_id   = CommonHelper::getRandomString2('cont');
        $contact->user_id      = $userId;
        $contact->first_name   = self::removeSpecialChar($request->firstName);
        $contact->last_name    = self::removeSpecialChar($request->lastName);
        $contact->email        = $request->email;
        $contact->phone        = $request->mobile;
        $contact->type         = $request->type;
        $contact->reference_id = $request->referenceId;
        $contact->account_type = $request->accountType;
        $contact->bank_name    = $request->bankName;

        if ($request->accountType == 'vpa') {
            $contact->vpa_address = $request->vpa;
        } elseif ($request->accountType == 'card') {
            $contact->card_number = $request->cardNumber;
        } else {
            $contact->account_number = $request->accountNumber;
            $contact->account_ifsc   = CommonHelper::caseConversion($request->ifsc, 'u');
        }

        $contact->save();

        $contactInfo = Contact::select(
            self::columnSelectResponse($request->accountType)
        )->where('contact_id', $contact->contact_id)->first();

        return ApiResponseHelper::success(
            'Contact created successfully',
            $contactInfo,
            '200'
        );
    }

    public static function columnSelectResponse($accountType)
    {
        $selectingColumn = [
            'contact_id as contactId',
            'first_name as firstName',
            'last_name as lastName',
            'email',
            'phone as mobile',
            'type',
            'account_type as accountType',
            'reference_id',
            'is_active as isActive'
        ];
        if ($accountType == 'vpa') {
            array_push($selectingColumn, 'vpa_address as vpa');
            return $selectingColumn;
        } elseif ($accountType == 'card') {
            array_push($selectingColumn, 'card_number as cardNumber');
            return $selectingColumn;
        } else {
            array_push($selectingColumn, 'account_number as accountNumber', 'account_ifsc as accountIFSC');
            return $selectingColumn;
        }
    }

    public static function removeSpecialChar($str)
    {
        $res = preg_replace('/[0-9\@\.\;\(\)]+/', '', $str);
        $res = ltrim($res);
        $res = rtrim($res);
        return $res;
    }
}
