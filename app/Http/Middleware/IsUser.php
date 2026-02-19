<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if(Auth::check()){
        //     $role_id = Auth::user()->role_id;
        //     if(in_array($role_id, ['2','3'])){

        //         return $next($request);
        //     }
        // }else{
        //     return redirect()->route('unauthrized.page');
        // }

        if (Auth::check() &&   in_array(Auth::user()->role_id, [2, 3])) {
            return $next($request);
        }

        return redirect()->route('unauthrized.page');
    }
}
