<?php

namespace App\Helpers;

use App\Models\GlobalService;
use App\Models\OauthUser;
use App\Models\MobikwikToken;
use App\Models\UserRooting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class CommonHelper
{

    public static function validateClient(string $clientId, string $clientSecret): array
    {
        $credential = OauthUser::where('client_id', $clientId)->where('is_active', '1')->first();

        // dd($credential);

        if (!$credential) {
            return [
                'status'  => false,
                'message' => 'Invalid client_id',
            ];
        }

        if (!$credential->verifyClientSecret($clientSecret)) {
            return [
                'status'  => false,
                'message' => 'Invalid client_secret',
            ];
        }

        $serviceSlug = GlobalService::where('id', $credential->service_id)->select('id as service')->first();

        if (!$serviceSlug) {
            return [
                'status'  => false,
                'message' => 'Service not found',
            ];
        }

        $credential->service = $serviceSlug->service;
        return [
            'status'   => true,
            'user_id'  => $credential->user_id,
            'service'  => $serviceSlug->service,
        ];
    }

    public static function checkAuthUser()
    {

        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        } else {
            return redirect()->route('home');
        }
    }

    public static function generateTransactionId()
    {
        return 'TXN' . time() . rand(1000, 9999);
    }

    public static function generatePaymentRefId()
    {
        return 'PAY' . time() . rand(1000, 9999);
    }

    public static function generateConnectPeTransactionId()
    {
        return 'CON' . time() . rand(10000, 99999);
    }

    public static function isTokenPresent()
    {
        try {
            $data =  MobikwikToken::whereDate('created_at', today())->select('token')->first();
            $token = '';
            if (!$data) {
                $mobikwikHelper = new MobiKwikHelper();
                $token = $mobikwikHelper->generateMobikwikToken();
            } else {
                $token = $data->token;
            }
            return $token;
        } catch (\Exception $e) {
            Log::error('Mobikwik Token Present Exception', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
        }
    }


    public static function getUserRouteUsingUserId($userId = '', $service_id, $area)
    {
        $data['slug'] = "no_route_found";
        $data['status'] = false;

        $userRooting = UserRooting::select('provider_slug')->where('user_id', $userId)
            ->where('service_id ', $service_id)->first();

        if (isset($userRooting)) {
            $data['slug'] = $userRooting;
            $data['status'] = true;
        }
        return $data;
    }
}
