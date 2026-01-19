<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OauthUser;
use Illuminate\Support\Facades\DB;
use App\Models\GlobalService;



class UserController extends Controller
{
    public function generateClientCredentials(Request $request)
    {
        $request->validate([
            'service' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

            $service = GlobalService::where('user_id', auth()->id())
            ->where(['slug'=>$request->service,'is_active' => '1'])
            ->select('id')
            ->first();

        try {

            $userId = auth()->id(); 
           
            $clientId = 'RAFI'.strtoupper($request->service) . '_' . Str::random(16);
            $clientSecret = hash('sha256', Str::random(32) . now());

            
            $credential = OauthUser::create([
                'user_id'       => $userId,
                'service_id'     => $service->id,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'is_active'    => '1',
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Client credentials generated successfully',
                'data'    => [
                    'client_id'     => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                ],
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Client credential generation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while generating credentials',
            ], 500);
        }
    }
}
