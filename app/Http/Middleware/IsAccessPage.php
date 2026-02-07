<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\BusinessInfo;

class IsAccessPage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()){
            $userId = Auth::id();

            $data = BusinessInfo::where('user_id',$userId)->first();
            
            if($data->is_kyc == '0'){
                
                return redirect()->route('open.kyc.page');

            }
            return $next($request);
        }else{
            return redirect()->route('open.unauthrized.page');

            
        }
        
    }
}
