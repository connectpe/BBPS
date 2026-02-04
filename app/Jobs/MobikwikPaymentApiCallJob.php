<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Helpers\MobiKwikHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MobikwikPaymentApiCallJob implements ShouldQueue
{
    use Queueable;

    private $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        try {

            $mobikwikHelper = new \App\Helpers\MobiKwikHelper();
            $token = CommonHelper::isTokenPresent();

            $response = $mobikwikHelper->sendRequest(
                '/recharge/v3/retailerPayment',
                $this->payload,
                $token,
            );
        } catch (\Exception $e) {
            \Log::error('Mobikwik Payment API Call Job Error: ' . $e->getMessage());
        }
    }
}
