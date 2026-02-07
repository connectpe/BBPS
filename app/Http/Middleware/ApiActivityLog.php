<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\ApiLog;

class ApiActivityLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response

    {
        $startTime = microtime(true);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;

        $Ip =  $request->ip();
        
        // $Ip =  '122.161.161.28';  //$request->ip();

        $location = json_decode(
            file_get_contents("http://ip-api.com/json/{$Ip}"),
            true
        );


        $locationData = [
            'country'     => $location['country']     ?? null,
            'countryCode' => $location['countryCode'] ?? null,
            'region'      => $location['region']      ?? null,
            'regionName'  => $location['regionName']  ?? null,
            'city'        => $location['city']        ?? null,
            'zip'         => $location['zip']         ?? null,
            'lat'         => $location['lat']         ?? null,
            'lon'         => $location['lon']         ?? null,
            'timezone'    => $location['timezone']    ?? null,
            'isp'         => $location['isp']         ?? null,
            'org'         => $location['org']         ?? null,
            'as'          => $location['as']          ?? null,
        ];

        ApiLog::create([
            'user_id'       => Auth::id(),
            'method'        => $request->method(),
            'endpoint'      => $request->path(),
            'request_body'  => json_encode($request->except(['password', 'pin'])),
            'response_body' => json_encode([$response->getContent()]),
            'status_code'   => $response->status(),
            'ip_address'    => $Ip,
            'user_agent'    => $request->userAgent(),
            'execution_time' => $executionTime,
            'location_details' => json_encode($locationData)
        ]);

        return $response;
    }
}
