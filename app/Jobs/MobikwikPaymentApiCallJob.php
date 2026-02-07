<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Helpers\MobiKwikHelper;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MobikwikPaymentApiCallJob implements ShouldQueue
{
    use Queueable;

    private $payload, $endpoint, $token, $types;

    public function __construct($payload, $endpoint, $token, $types)
    {
        $this->payload = $payload;
        $this->types = $types;
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    public function handle()
    {
        try {
            $types = $this->types;
            $payload = $this->payload;
            $endpoint = $this->endpoint;
            $token = $this->token;

            switch ('mobikwik') {
                case 'mobikwik':
                    $mobikwik = new MobiKwikHelper;
                    $requestTransfer = $mobikwik->sendRequest($endpoint, $payload, $token);
                    $transactionStatus = 'pending';
                    if (isset($requestTransfer['data']) && !empty($requestTransfer['data']) && $requestTransfer['data']['status'] === 'SUCCESS') {
                        Transaction::where('payment_ref_id', $transactionStatus['data']['txId'])->update([
                            'status' => $transactionStatus['data']['status'],
                            'opRefNo' => $transactionStatus['data']['reference_number '],
                            'discountprice' => $transactionStatus['data']['discountprice']
                        ]);

                        return response()->json([
                            'status' => true,
                            'message' => 'Recharge Successfully !'
                        ]);
                    } else {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Transaction failed',
                            'response' => $requestTransfer
                        ]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Mobikwik Payment API Call Job Error: ' . $e->getMessage());
        }
    }
}
