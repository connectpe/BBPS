<?php

namespace App\Helpers;

use App\Models\GlobalService;
use Illuminate\Support\Facades\Hash;

class CommonHelper
{
    
    public static function validateClient(string $clientId, string $clientSecret): array
    {
        $credential = OauthUser::where('client_id', $clientId)->first();

        if (!$credential) {
            return [
                'status'  => false,
                'message' => 'Invalid client_id',
            ];
        }

        
        if (!Hash::check($clientSecret, $credential->client_secret)) {
            return [
                'status'  => false,
                'message' => 'Invalid client_secret',
            ];
        }
        
        $serviceSlug = GlobalService::where('id', $credential->service_id)->select('slug as service')->first();

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
