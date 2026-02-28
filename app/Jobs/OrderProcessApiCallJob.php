<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Exception;

class OrderProcessApiCallJob implements ShouldQueue
{
    use Queueable;
    private $provider;
    private $connectpeId;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->provider = '';
        $this->connectpeId = '';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{

            switch($this->provider){
            case 'mobikwik':
                \Log::info('txn success');
            
            default:
                \Log::info('txn failed');
                
        }

        }catch(\Exception $e){
            \Log::warning(['message'=>$e->getMessage()]);
           
        }
    }
}
