<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class IsSupport
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role_id = Auth::user()->role_id;
        if(Auth::check()){
            if($role_id == 4){
                return $next($request);
            }

        }else{
            return redirect()->route('unauthrized.page');
        }
        
    }
}
