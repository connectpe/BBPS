<?php

namespace App\Helpers;

use App\Models\GlobalService;
use App\Models\OauthUser;


class CommonHelper
{
    
    public static function validateClient(string $clientId, string $clientSecret): array
    {
        $credential = OauthUser::where('client_id', $clientId)->where('is_active','1')->first();

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

        if(!$serviceSlug){
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

    public static function checkAuthUser(){
        
        if(!auth()->check() ){
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }else{
            return redirect()->route('home');
        }
        
    }
}
