<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;

class CallbackController extends Controller
{
public function handle(Request $request,$type)
    {
        // Log the incoming request for debugging
        Log::info('Callback received', ['request' => $request->all()]);
        if(empty($request->all())){
            Log::warning('Empty callback received');
            return response()->json(['message' => 'Empty callback received'], 400);
        }

        $data = $request->all();

        $txnId = $data['payload']['order_id'] ?? null;

        switch($type){
            case 'nsdl-callback':

                $isTxnFound = Transaction::where(['transaction_id'=>$txnId, 'status'=>'initiate'])->first();

                if (!$isTxnFound) {
                    Log::warning('Transaction not found or not in initiate status', ['transaction_id' => $txnId]);
                    return response()->json(['message' => 'Transaction not found or not in initiate status'], 400);
                }

                $isTxnFound->update([
                    'status' => $data['payload']['status'] ?? 'failed',
                    // 'response' => json_encode($data)
                ]);

                
            break;

            default:
            return response()->json([
                'status' => false,
                'message' => 'Invalid callback type'
            ]);
        }
        
        
    }
}
