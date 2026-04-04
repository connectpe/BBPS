<?php

namespace App\Helpers;

use App\Models\GlobalService;
use App\Models\IpWhitelist;
use App\Models\OauthUser;
use App\Models\MobikwikToken;
use App\Models\UserRooting;
use App\Models\UserService;
use App\Models\DefaultProvider;
use Illuminate\Support\Facades\Auth;
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

        // $credential->service = $serviceSlug->service;
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
        }
        //  else {
        //     return redirect()->route('home');
        // }
    }

    public static function generateTransactionId()
    {
        return 'TXN' . time() . rand(100, 999);
    }

    public static function generatePaymentRefId()
    {
        return 'PAY' . time() . rand(100, 999);
    }

    public static function generateConnectPeTransactionId()
    {
        return "CPE" . time() . rand(100, 999);
    }

    public static function isTokenPresent()
    {
        try {
            $data =  MobikwikToken::whereDate('created_at', today())->select('token')->first();
            $token = '';
            if (!$data) {
                $mobikwikHelper = new MobiKwikHelper();
                $token = $mobikwikHelper->generateToken();
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

    public static function getUserIdUsingKeyAndSecret($header)
    {
        $hash = hash('sha512', $header['php-auth-pw'][0]);
        $key = $header['php-auth-user'][0];
        $OauthClient = OauthUser::select('user_id')->where(['client_key' => $key, 'client_secret' => $hash])->first();
        return $OauthClient->user_id;
    }

    public static function getUserIdAndServiceIdUsingKeyAndSecret($header)
    {
        if (!isset($header['php-auth-user'][0]) || !isset($header['php-auth-pw'][0])) {
            return [
                'status' => false,
                'message' => 'Authorization headers missing'
            ];
        }

        $key = $header['php-auth-user'][0];
        $password = hash('sha512', $header['php-auth-pw'][0]);

        $OauthClient = OauthUser::select('user_id', 'service_id')
            ->where([
                'client_id' => $key,
                'client_secret' => $password
            ])->first();

        if (!$OauthClient) {
            return [
                'status' => false,
                'message' => 'Invalid clientId Or Secret Key'
            ];
        }
        return [
            'status' => true,
            'user_id' => $OauthClient->user_id ?? null,
            'service_id' => $OauthClient->service_id ?? null
        ];
    }

    public static function isGlobalServiceActive($serviceId)
    {
        $service = GlobalService::where('id', $serviceId)->first();

        if (!$service) {
            return [
                'status' => false,
                'message' => 'Service not found'
            ];
        }

        if ($service->is_active != 1) {
            return [
                'status' => false,
                'message' => 'Global Service is currently inactive'
            ];
        }

        if ($service->is_activation_allowed != 1) {
            return [
                'status' => false,
                'message' => 'Global service activation is inactive'
            ];
        }

        return [
            'status' => true,
            'message' => 'Global service is active'
        ];
    }

    public static function isUserServiceActiveUsingUserIdAndServiceId($userId, $serviceId)
    {
        $userService = UserService::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->first();

        if (!$userService) {
            return [
                'status' => false,
                'message' => 'Service not assigned to this user'
            ];
        }

        if ($userService->status !== 'approved') {
            return [
                'status' => false,
                'message' => 'Service is not approved for this user'
            ];
        }

        if ($userService->is_active != 1) {
            return [
                'status' => false,
                'message' => 'Service is inactive for this user'
            ];
        }

        if ($userService->is_api_enable != 1) {
            return [
                'status' => false,
                'message' => 'API access is disabled for this service'
            ];
        }

        return [
            'status' => true,
            'message' => 'User service is active'
        ];
    }

    public static function getProviderSlug($userId, $serviceId)
    {
        $userRooting = UserRooting::select('provider_slug')->where('user_id', $userId)
            ->where('service_id', $serviceId)->first();

        if ($userRooting) {
            return [
                'provider_slug' => $userRooting->provider_slug
            ];
        } else {

            $defaultProvider = DefaultProvider::select('provider_slug')->where('service_id', $serviceId)->first();
            return [
                'provider_slug' => $defaultProvider->provider_slug
            ];
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


    //     public static function isIpWhitelisted($userId, $serviceId, $ipAddress): array{
    //         try{
    //             $exists = IpWhitelist::where('user_id', $userId)->where('service_id', $serviceId)
    //             ->where('ip_address', $ipAddress) ->where('is_active', 1)->where('is_deleted', 0)
    //             ->exists();
    //             if ($exists) {
    //                 return [
    //                     'status' => true,
    //                     'message' => 'IP is whitelisted.'
    //                 ];
    //             } else {
    //                 return [
    //                     'status' => false,
    //                     'message' => 'IP is not whitelisted.'
    //                 ];
    //             }

    //         } catch (\Throwable $e) {
    //             Log::error('IP Whitelist Check Error', [
    //                 'error' => $e->getMessage(),
    //                 'file'  => $e->getFile(),
    //                 'line'  => $e->getLine(),
    //             ]);
    //             return [
    //                 'status'  => false,
    //                 'message' => 'Unable to verify IP whitelist.'
    //             ];
    //         }
    //     }


    public static function checkIpWhiteList($userId, $serviceId, $ip)
    {
        try {
            if (empty($userId) && empty($serviceId) && empty($ip)) {
                return false;
            }

            $status = false;

            $data = IpWhitelist::where(['user_id' => $userId, 'service_id' => $serviceId, 'ip_address' => $ip])->count();

            if ($data > 0) {
                $status = true;
            }

            return $status;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public static function getRandomString2($prefix = '', $separator = true, $length = 2)
    {
        $ts = hrtime(true);

        $base_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $str_shuffle = substr(str_shuffle($base_str), 0, $length);
        $str_md5 = substr(md5($str_shuffle), 0, $length);
        $hash = substr(sha1($str_md5), 0, $length);

        if ($prefix) {
            if ($separator) {
                $string = $ts . strtoupper($hash) . rand(1, 9);
            } else {
                $string = $ts . strtoupper($hash) . rand(1, 9);
            }
        } else {
            $string = $hash . $ts;
        }
        return $string;
    }

    public static function getRandomString($prefix = '', $separator = true, $length = 5)
    {
        $ts = hrtime(true);

        $base_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $str_shuffle = substr(str_shuffle($base_str), 0, $length);
        $str_md5 = substr(md5($str_shuffle), 0, $length);
        $hash = substr(sha1($str_md5), 0, $length);

        if ($prefix) {
            if ($separator) {
                $string = $ts . strtoupper($hash);
            } else {
                $string = $ts . strtoupper($hash);
            }
        } else {
            $string = $hash . $ts;
        }
        return $string;
    }

    public static function case($text, $type = '')
    {
        if ($type == 'l') {
            return strtolower($text);
        } elseif ($type == 'u') {
            return strtoupper($text);
        } elseif ($type == 'uw') {
            return ucwords($text);
        } else {
            return ucfirst($text);
        }
    }

    public static function checkUserServiceActivate($userId)
    {
        return UserService::where('user_id', $userId)->where('status', 'approved')->where('is_active', '1')->exists();
    }
}
