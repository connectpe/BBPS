<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\HashHelper;
use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Validation\OrderValidation;
use App\Models\User;
use App\Models\Order;


class PayoutOrderController extends Controller
{

    public function createOrder(Request $request)
    {
        Log::info("Calling Function createOrder");

        // Headers
        $headers = $request->header();

        // Credentials
        $client_id     = $request->getUser();
        $client_secret = hash('sha512', $request->getPassword());

        // Generate hash
        $hash = HashHelper::generate(
            HashHelper::CREATE_ORDER,
            $client_id,
            $client_secret,
            $request->all()
        );

        // Signature (FIXED)
        $signature = $headers['signature'][0] ?? '';

        // If mismatch → reject
        // if (!hash_equals($hash, $signature)) {
        //     return ApiResponseHelper::failed('Unauthorized: Invalid signature');
        // }

        // Auth Data
        $userId    = $request->input('auth_data.user_id');
        $serviceId = $request->input('auth_data.service_id');
        // dd($userId,$serviceId);
        // Check service active
        $status = CommonHelper::isGlobalServiceActive($serviceId);
        if (! $status['status']) {
            return response()->json([
                'status'  => false,
                'message' => $status['message']
            ]);
        }

        // Validation
        $validation = new OrderValidation($request);
        $validator  = $validation->addOrder();

        $validator->after(function ($validator) use ($request, $userId) {

            // Decimal validation
            if (strpos($request->amount, ".") !== false) {
                if (strlen(strrchr($request->amount, '.')) - 1 > 2) {
                    $validator->errors()->add('amount', 'Only 2 decimal allowed in amount');
                }
            }

            // User check
            $user = User::where('id', $userId)
                ->where('status', '1')
                ->first();

            if (! $user) {
                $validator->errors()->add('userId', 'User Account disabled');
            } elseif ($request->amount > $user->transaction_amount) {
                $validator->errors()->add('amount', 'Insufficient funds.');
            }

            // Contact validation
            $contact = DB::table('contacts')
                ->select('account_type')
                ->where('contact_id', $request->contactId)
                ->where('user_id', $userId)
                ->first();

            if (! $contact) {
                $validator->errors()->add('contactId', 'Invalid contactId');
            } else {
                $mode = strtolower($request->mode);

                if ($contact->account_type === 'bank_account' && !in_array($mode, ['neft', 'imps', 'rtgs'])) {
                    $validator->errors()->add('mode', 'Allowed: imps, neft, rtgs');
                }

                if ($contact->account_type === 'vpa' && $mode !== 'upi') {
                    $validator->errors()->add('mode', 'Allowed: upi');
                }

                if ($contact->account_type === 'card' && $mode !== 'card') {
                    $validator->errors()->add('mode', 'Allowed: card');
                }
            }

            // Duplicate check (OPTIMIZED)
            if (Order::where('client_ref_id', $request->clientRefId)->exists()) {
                $validator->errors()->add('clientRefId', 'Client Ref Id already exists');
            }
        });

        // Validation fail
        if ($validator->fails()) {
            return ApiResponseHelper::missing($validator->errors()->first());
        }

        // Main Logic
        $data = $request->all();
        // dd($data['amount']);
        $getModeId = CommonHelper::getModeId($data['mode'], $serviceId);
        if (! $getModeId['status']) {
            return ApiResponseHelper::failed($getModeId['message'], []);
        }

        $modeConfig = $getModeId['data'];

        // Amount range check
        if ($data['amount'] < $modeConfig->min_order_value || $data['amount'] > $modeConfig->max_order_value) {
            return ApiResponseHelper::failed(
                "{$data['amount']} is not in allowed range for {$data['mode']}",
                []
            );
        }

        // Generate order
        $modeId     = $modeConfig->mode_id;
        $orderRefId = CommonHelper::getRandomString2('REF', false);

        $provider = CommonHelper::getProviderSlug($userId, $serviceId);

        if (!$provider['status']) {
            return response([
                'status' => false,
                'message' => $provider['message'],
            ]);
        }

        $providerId = $provider['provider_id'];

        // Charges
        $charges = TransactionHelper::getFeesAndTaxes($data['amount'], $serviceId, $userId);

        if (! $charges['status']) {
            return ApiResponseHelper::failed($charges['message'], []);
        }

        // Agent Info
        $data['agent'] = [
            'ip'        => $headers['cf-connecting-ip'][0] ?? $request->ip(),
            'userAgent' => $headers['user-agent'][0] ?? ''
        ];

        $data['charges'] = $charges;

        // Create Order
        $orderCreate = TransactionHelper::createTransactionAndOrder(
            $orderRefId,
            $userId,
            $serviceId,
            $modeId,
            $providerId,
            $data
        );

        if (! $orderCreate['status']) {
            return ApiResponseHelper::failed($orderCreate['message'], []);
        }

        // Dispatch Job
        dispatch(
            new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob(
                $orderRefId,
                $userId,
                'balance_debit'
            )
        )->onQueue('payout_debit_queue');

        // Response
        return ApiResponseHelper::success(
            'Order accepted successfully',
            [
                'clientRefId' => $data['clientRefId'],
                'orderRefId'  => $orderRefId,
                'status'      => 'queued'
            ],
            200
        );
    }
}
