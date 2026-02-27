<?php

namespace App\Helpers;

use App\Models\GlobalService;
use App\Models\IpWhitelist;
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
                $string = $ts . strtoupper($hash).rand(1,9);
            } else {
                $string = $ts . strtoupper($hash).rand(1,9);
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
}
