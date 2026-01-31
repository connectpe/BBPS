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

        ApiLog::create([
            'user_id'       => Auth::id(),
            'method'        => $request->method(),
            'endpoint'      => $request->path(),
            'request_body'  => json_encode($request->except(['password','pin'])),
            'response_body' => json_encode([]),
            'status_code'   => $response->status(),
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'execution_time'=> $executionTime
        ]);

        return $response;
    }

    
}
