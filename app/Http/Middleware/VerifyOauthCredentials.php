<?php

namespace App\Http\Middleware;

use App\Models\OauthUser;
use Closure;
use Illuminate\Http\Request;

class VerifyOauthCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $clientId = $request->header('username');
        $clientSecret = $request->header('password');

        if (!$clientId || !$clientSecret) {
            return response()->json([
                'status' => false,
                'message' => 'Missing client credentials',
            ], 401);
        }

        $oauthUser = OauthUser::where('client_id', $clientId)
            ->where('is_active', '1')
            ->first();

        if (!$oauthUser) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid client ID',
            ], 401);
        }

        if (!$oauthUser->verifyClientSecret($clientSecret)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid client secret',
            ], 401);
        }

        // Store oauth user in request for later use
        $request->merge(['oauth_user' => $oauthUser]);

        return $next($request);
    }
}
