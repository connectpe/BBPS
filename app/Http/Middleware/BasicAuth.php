<?php

namespace App\Http\Middleware;

use App\Models\GlobalService;
use App\Models\OauthUser;
use App\Models\IpWhitelist;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ApiResponseHelper;

class BasicAuth
{

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->header('cf-connecting-ip') ?? $request->ip();

        // Content-Type check
        if (!$request->isJson()) {
            return ApiResponseHelper::apiError("Invalid authorization");
        }

        // Basic Auth
        $key = $request->getUser();
        $secret = $request->getPassword();

        if (!$key || !$secret) {
            return ApiResponseHelper::apiError("Invalid authorization");
        }

        // Service check
        $serviceSlug = $request->segment(2);
        $service = GlobalService::where('slug', $serviceSlug)->first();

        // dd($serviceSlug, $service->id);

        if (!$service) {
            return ApiResponseHelper::apiError("Unauthorized service request");
        }

        // OAuth Client check
        $client = OauthUser::where([
            ['client_id', $key],
            ['service_id', $service->id],
            ['is_active', '1']
        ])->first();

        if (!$client) {
            return ApiResponseHelper::apiError("Invalid credentials used");
        }

        // Secret match
        if (!hash_equals(hash('sha512', $secret), $client->client_secret)) {
            return ApiResponseHelper::apiError("Credentials doesn't match our records.");
        }

        // User check
        $user = User::find($client->user_id);

        if (!$user || $user->status != 1) {
            return ApiResponseHelper::apiError("User inactive or not found");
        }

        // Service active check
        if ($service->is_active != 1) {
            return ApiResponseHelper::apiError("Service is disabled");
        }

        // IP Whitelist
        $ipAllowed = IpWhitelist::where([
            ['ip_address', $ip],
            ['service_id', $service->id],
            ['user_id', $client->user_id],
            ['is_active', '1']
        ])->exists();

        if (!$ipAllowed) {
            return ApiResponseHelper::apiError("Unauthorized IP used", [
                "ip" => $ip,
            ]);
        }

        // Attach auth data
        $request->merge([
            'auth_data' => [
                'user_id' => $client->user_id,
                'service_id' => $service->id
            ]
        ]);

        // PASS REQUEST
        return $next($request);
    }
}
