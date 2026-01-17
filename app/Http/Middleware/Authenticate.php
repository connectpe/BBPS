<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::check()){
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            return redirect()->route('home');
        }
        return $next($request);
    }
}