<?php

namespace App\Helpers;
use Illuminate\Http\Request;
use App\Services\IDFC\IdfcPayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CommonHelper;

use App\Helpers\IdfcHelper;


class IdfcPayoutHelper
{
    
    protected $idfc;

    public function __construct(IdfcPayout $idfc)
    {
        $this->idfc = $idfc;
        $token = $this->idfc->generateJWT();
        // dd($token);
        // \Log::info('jwt token',[$token]);
        // dd($this->idfc->getBearerToken($this->idfc->generateJWT()));
    }

    public function fundTransfer($orderData)
    {
        try {
            $mode = CommonHelper::case($orderData['mode'], 'u');
            if (isset($orderData['account_ifsc']) && !empty($orderData['account_ifsc'])) {
            $ifsc = CommonHelper::case($orderData['account_ifsc'], 'u');
            } else {
                $ifsc = '';
            }
                if ($mode == 'UPI') {
                    $accountNo = $orderData['vpa_address'];
                } else {
                    $accountNo = $orderData['account_number'];
                }
        

            $jwtToken = $this->idfc->generateJWT();
            // \Log::info('Jwt token = ',[$jwtToken]);
            
            $bearerToken = $this->idfc->getBearerToken($jwtToken);
        
            $payeeName = $orderData['first_name'].' '.$orderData['last_name'];
                if (strlen($payeeName) >= 49) {
                $payeeName = substr($payeeName,0, 49);
                }
            $payeeName =  $this->special_character_remove($payeeName);
            
            
            if (isset($orderData['account_ifsc']) && !empty($orderData['account_ifsc'])) {
            $ifsc = CommonHelper::case($orderData['account_ifsc'], 'u');
            } else {
                $ifsc = '';
            }

            $payload = [
                "initiateAuthGenericFundTransferAPIReq" => [
                    "transactionID"        => $orderData['order_ref_id'],
                    "debitAccountNumber"   => "52110202213",
                    "creditAccountNumber"  =>  $accountNo,
                    "remitterName"         => "Vikas",
                    "currency"             => "INR",
                    "transactionType"      => $orderData['mode'],
                    "paymentDescription"   => null,
                    "beneficiaryIFSC"      => $ifsc,
                    "beneficiaryName"      =>  $payeeName,
                    "beneficiaryAddress"   => 'Lucknow' ,
                    "emailId"              => $orderData['email'] ?? "nikhil.kumar@groscope.com",
                    "mobileNo"             =>  "9999999999",
                    "amount"               => $request->amount ?? strval($orderData['amount'])
                ]
            ];
        
            $hexKey = "9da11d706c65496467234149536b6591a3616d706c65496468634139536b5627"; 
            $encryptedPayload = $this->idfc->encrypt($payload, $hexKey);

            
            $url = "https://apiext.payments.idfcfirstbank.com/paymenttxns/v1/fundTransfer";
            $response =  $this->idfc->apiRequest($encryptedPayload, $bearerToken, $url);
            
            // \Log::info('IDFC raw response = ',[$response]);
            
            
            $resp = json_decode($response, true);
            
            
        
            
            return $resp;

        } catch (\Exception $e) {
        
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function special_character_remove($string)
   {
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , ' ', $string);
        return trim($string, '-');
    }

    public function checkStatus($Order)
    {
        $jwtToken = $this->idfc->generateJWT();
        $bearerToken = $this->idfc->getBearerToken($jwtToken);
        
        $dateString = $request->created_at; 

       
        $timestamp = strtotime($dateString);
        
        
        $formattedDate = date('dmY', $timestamp);
        
        
        
       
        // str_replace($request)
        
        $paymentref = '';
        
       if($request->mode == 'IMPS'){
           $paymentref = 'TRASNACTIONID';
       }else{
           $paymentref = 'CORRECTIONID';
           
       }

        $payload = [
            "paymentTransactionStatusReq" => [
                "transactionType" => $Order['mode']?? "IMPS",
                "transactionReferenceNumber" =>$Order['order_ref_id']  ?? $request->txnId,
                "paymentReferenceNumber" => $paymentref ?? $request->utr,
                "transactionDate" => $formattedDate ?? $request->txndate,
                
            ]
        ];

        $hexKey = "9da11d706c65496467234149536b6591a3616d706c65496468634139536b5627";
        $encryptedPayload = $this->idfc->encrypt($payload, $hexKey);
        
        $url = "https://apiext.payments.idfcfirstbank.com/paymentenqs/v1/paymentTransactionStatus";

        return $this->idfc->apiRequest($encryptedPayload, $bearerToken,$url);
    }

    public function accountBalance($request)
    {
        // dd($request->all());
        $jwtToken = $this->idfc->generateJWT();
        $bearerToken = $this->idfc->getBearerToken($jwtToken);

        $payload = [
            "prefetchAccountReq" => [
                "accountNumber" => $request->account_number
            ]
        ];

        $hexKey = "9da11d706c65496467234149536b6591a3616d706c65496468634139536b5627";
        $encryptedPayload = $this->idfc->encrypt($payload, $hexKey);
        $url = "https://apiext.liab.idfcfirstbank.com/acctenq/v2/prefetchAccount";

        return $this->idfc->apiRequest($encryptedPayload, $bearerToken,$url);
    }

    public function accountStatement($request)
    {
        $jwtToken = $this->idfc->generateJWT();
        $bearerToken = $this->idfc->getBearerToken($jwtToken);

        $payload = [
            "getAccountStatementReq" => [
                "CBSTellerBranch"=>"",
                "CBSTellerID"=>"",
                "accountNumber" => $request->account_number,
                "fromDate" => $request->from_date,
                "toDate" => $request->to_date,
                "numberOfTransactions" => "100",
                "prompt" => ""
            ]
        ];
        
        // dd($payload);

        $hexKey = "9da11d706c65496467234149536b6591a3616d706c65496468634139536b5627". bin2hex(random_bytes(16));
        $encryptedPayload = $this->idfc->encrypt($payload, $hexKey);
        
        $url = "https://apiext.liab.idfcfirstbank.com/acctenq/v3/getAccountStatement";

        return $this->idfc->apiRequest($encryptedPayload, $bearerToken,$url);
    }

    public function beneValidation(Request $request)
    {
        $jwtToken = $this->idfc->generateJWT();
        $bearerToken = $this->idfc->getBearerToken($jwtToken);

        $payload = [
            "beneValidationReq" => [
                "remitterName" => $request->remitter_name,
                "remitterMobileNumber" => $request->mobile_no,
                "debtorAccountId" => $request->debit_account,
                "creditorAccountId" => $request->credit_account,
                "ifscCode" => $request->ifsc,
                "paymentDescription" => $request->description,
                "transactionReferenceNumber" => uniqid()
            ]
        ];

        $hexKey = "9da11d706c65496467234149536b6591a3616d706c65496468634139536b56276";
        $encryptedPayload = $this->idfc->encrypt($payload, $hexKey);

        return $this->idfc->apiRequest($encryptedPayload, $bearerToken);
    }
    
    
    
    
    
    
    
    
    
    public function clientFundTransfer(Request $request)
    {
        try {
            
            $clientKey = $request->header('Client-Key');
            if (!$clientKey || $clientKey !== 'CLIENT-API-KEY-12345') {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or missing Client-Key header'
                ], 403);
            }

          
            $validator = Validator::make($request->all(), [
                'account' => 'required|numeric',
                'mode' => 'required|string',
                'ifsc' => 'required|string|size:11',
                'name' => 'required|string',
                'address' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'mobile' => 'required|numeric|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            
            // $idfcController = new IDFCController();
            $response = $this->fundTransfer($request);

          
            $resp = json_decode($response, true);

            return response()->json([
                'status'  => $resp['initiateAuthGenericFundTransferAPIResp']['metaData']['status'],
                'time'    => $resp['initiateAuthGenericFundTransferAPIResp']['metaData']['time'] ,
                'message' => $resp['initiateAuthGenericFundTransferAPIResp']['metaData']['message'] ,
                'txnId'   => $resp['initiateAuthGenericFundTransferAPIResp']['resourceData']['transactionID'] ,
                'utr'     => $resp['initiateAuthGenericFundTransferAPIResp']['resourceData']['transactionReferenceNo'] ,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function clientcheckstatus(Request $request){
        try{
            $clientKey = $request->header('Client-Key');
            if (!$clientKey || $clientKey !== 'CLIENT-API-KEY-12345') {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or missing Client-Key header'
                ], 403);
            }
            
            $validator = Validator::make($request->all(),[
                'txnId' => 'required|string',
                'utr' => 'required|string',
                'txndate' => 'required|string',
                'mode' => 'required|string',
                
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $response = $this->checkStatus($request);
            $resp = json_decode($response, true);
            
            return response()->json([
                'status' => $resp['paymentTransactionStatusResp']['metaData']['status'],
                'message' => $resp['paymentTransactionStatusResp']['metaData']['message'],
                'account' => $resp['paymentTransactionStatusResp']['resourceData']['beneficiaryAccountNumber'],
                'name' => $resp['paymentTransactionStatusResp']['resourceData']['beneficiaryName'],
                'errorid' => $resp['paymentTransactionStatusResp']['resourceData']['errorId'],
                'errormessage' => $resp['paymentTransactionStatusResp']['resourceData']['errorMessage']
                
            ]);
            
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
            
        }
        
    }
}

