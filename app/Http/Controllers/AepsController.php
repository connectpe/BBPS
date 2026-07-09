<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf;
use Illuminate\Support\Facades\Auth;

class AepsController extends Controller
{

    protected $baseUrl;
    protected $token;


    public function __construct()
    {
        $this->baseUrl = config('app.aeps_keys.AEPS_BASE_URL');
        $this->token = config('app.aeps_keys.AEPS_TOKEN');
    }

    /**
     * Generic API caller.
     */
    private function callApi(string $method,  string $url, array $headers = [],  array $options = []): Response
    {
        return Http::withHeaders($headers)
            ->timeout(30)
            ->send($method, $url, $options);
    }

    public function aepsServices()
    {
        return view('AepsServices.aeps-services');
    }

    public function userOnboard()
    {
        $companyType = [];
        $body = [
            'token' => $this->token
        ];

        $user = Auth::user()->load(['business', 'bankDetails']);

        $url = $this->baseUrl . 'api/maeps/companytype';
        $response = $this->callApi(
            'POST',
            $url,
            [
                'Accept' => 'application/json',
            ],
            [
                'json' => $body
            ]
        );

        $response =  $response->json();

        if (isset($response['status']) && $response['status'] === 'TXN' && isset($response['data']['companyTypes'])) {
            $companyType = $response['data']['companyTypes'];
        }

        return view('AepsServices.user-onboard', compact('companyType', 'user'));
    }

    public function aepsUserOnboard(Request $request)
    {
        $body = [
            'token' => $this->token,
            'transactionType' => 'useronboard',
            'merchantFName' => $request->merchantFName,
            'merchantMName' => $request->merchantMName,
            'merchantLName' => $request->merchantLName,
            'merchantPhoneNumber' => $request->merchantPhoneNumber,
            'merchantAadhar' => $request->merchantAadhar,
            'userPan' => $request->userPan,
            'companyType' => $request->companyType,
            'companyBankAccountNumber' => $request->companyBankAccountNumber,
            'bankIfscCode' => $request->bankIfscCode,
            'merchantState' => $request->merchantState,
            'merchantCityName' => $request->merchantCityName,
            'merchantDistrictName' => $request->merchantDistrictName,
            'merchantPinCode' => $request->merchantPinCode,
            'merchantAddress' => $request->merchantAddress,
        ];


        $url = $this->baseUrl . 'api/maeps/transaction';
        $response = $this->callApi(
            'POST',
            $url,
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            [
                'json' => $body
            ]
        );

        $response =  $response->json();
        dd($response);
        if (isset($response['status']) && $response['status'] === 'ERR') {
            return redirect()->back()->withInput()->with('error', $response['message']);
        }


        if (isset($response['status']) && $response['status'] === 'TXN' && isset($response['data']['companyTypes'])) {
            $companyType = $response['data']['companyTypes'];
        }
    }


    public function balanceEnquiry(Request $request)
    {
        $request->validate([
            'mobileNumber' => 'required',
            'adhaarNumber' => 'required',
            'iinno' => 'required',
            'txtPidData' => 'required'
        ]);

        $url = $this->baseUrl . 'api/maeps/transaction';
        $user = Auth::user();

        $payload = [
            'token' => $this->token,
            'transactionType' => 'BE',
            'merchantLoginId' =>  $user->aeps_merchant_login_Id,
            'merchantLoginPin' => $user->aeps_merchant_login_Pin,
            'mobileNumber' => $request->input('mobileNumber'),
            'adhaarNumber' => $request->input('adhaarNumber'),
            'iinno' => $request->input('iinno'),
            'txtPidData' => $request->input('txtPidData')
        ];
    }
}
