<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PayoutBalanceDebitAndStatusUpdateJob implements ShouldQueue
{
    use Queueable;
    private $connectpeId;
    private $orderId;
    private $providerId;
    private $call;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->connectpeId = '';
        $this->orderId = '';
        $this->providerId = '';
        $this->call = '';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
