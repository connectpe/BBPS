<?php

namespace App\Http\Middleware;

use App\Models\GlobalService;
use App\Models\OauthUser;
use App\Models\IpWhitelist;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header    = $request->header();
        $body      = $request->getContent();
        $ip        = isset($header['cf-connecting-ip']) ? $header['cf-connecting-ip'] : $request->ip();

        if ($header['content-type'][0] != 'application/json') {
            $res["code"]    = "0x0201";
            $res["message"] = "Invalid content type";
            $res["status"]  = "FAILURE";
            return response()->json($res, 401);
        }
        
        if (isset($header)) {

            if (isset($header['php-auth-user'][0]) && isset($header['php-auth-pw'][0])) {
                $hash = hash('sha512', $header['php-auth-pw'][0]);
                $key  = $header['php-auth-user'][0];

                $service = Request::segment(2);

                $service = GlobalService::where('slug', $service)->first();

                if (isset($service)) {

                    $oAuthClient = OauthUser::select('user_id', 'service_id', 'client_key', 'client_secret')->where([['client_key', $key], ['service_id', $service->id], ['is_active', '1']])->first();

                    if (isset($oAuthClient)) {

                        $request['auth_data'] = ['user_id' => $oAuthClient->user_id, 'service_id' => $service->id, 'service_name' => $service];
                        $checkHash            = hash_equals($hash, $oAuthClient->client_secret);

                        if ($checkHash) {
                            if (User::where('id', '=', $oAuthClient->user_id)->where('status', '1')->count()) {

                                $GlobalConfig = GlobalService::where('slug', $service->slug)->first();

                                if ($GlobalConfig->is_active == '1') {
                                    $isPayoutServiceEnable = true;
                                } else {
                                    $isPayoutServiceEnable = false;
                                }

                                if ($isPayoutServiceEnable) {

                                    $ipAddress = IpWhitelist::where([['ip', $ip], ['service_id', $service->id], ['user_id', $oAuthClient->user_id], ['is_active', '1']])->first();

                                    if (!$ipAddress) {
                                        $res["code"]    = "0x0201";
                                        $res["message"] = "Unauthorized IP used.";
                                        $res["status"]  = "FAILURE";
                                        $res["ip"]      = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                                        return response()->json($res, 401);
                                    }
                                }
                            } else {
                                $message   = "";
                                $userCheck = User::where('id', '=', $oAuthClient->user_id)->first();

                                if (isset($userCheck) && ! empty($userCheck)) {
                                    if ($userCheck->is_active == '0') {
                                        $message = "Your account is initiate. Please contact  to your Account Coordinator";
                                    } else if ($userCheck->is_active == '2') {
                                        $message = "Your account is inactive. Please contact  to your Account Coordinator";
                                    } else if ($userCheck->is_active == '3') {
                                        $message = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : "Your account is pending. Please contact  to your Account Coordinator";
                                    } else if ($userCheck->is_active == '4') {
                                        $message = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : "Your account is suspended. Please contact  to your Account Coordinator";
                                    }
                                } else {
                                    $message = isset($GlobalConfig->attribute_5) ? $GlobalConfig->attribute_5 : "Your account dose not exits. Please contact  to your Account Coordinator";
                                }
                                $res["code"]    = "0x0201";
                                $res["message"] = $message;
                                $res["status"]  = "FAILURE";
                                return response()->json($res, 401);
                            }
                        } else {
                            $res["code"]    = "0x0201";
                            $res["message"] = "Credentials doesn't match our records.";
                            $res["status"]  = "FAILURE";
                            return response()->json($res, 401);
                        }
                    } else {
                        $res["code"]    = "0x0201";
                        $res["message"] = "Invalid credentials used";
                        $res["status"]  = "FAILURE";
                        return response()->json($res, 401);
                    }
                } else {
                    $res["code"]    = "0x0201";
                    $res["message"] = "Unauthorized service request";
                    $res["status"]  = "FAILURE";
                    return response()->json($res, 401);
                }
            } else {
                $res["code"]    = "0x0201";
                $res["message"] = "Invalid authorization";
                $res["status"]  = "FAILURE";
                return response()->json($res, 401);
            }
        } else {
            $res["code"]    = "0x0201";
            $res["message"] = "Authorization failure";
            $res["status"]  = "FAILURE";
            return response()->json($res, 401);
        }
    }
}
