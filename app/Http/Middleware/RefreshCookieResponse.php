<?php

namespace App\Http\Middleware;

use App\Helpers\CookieHelpers;
use App\Models\JWT;
use Closure;
use Illuminate\Support\Facades\Log;

class RefreshCookieResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $cookie_user = 'token_user';
        $cookie_customer = 'token_customer';
        $token = $request->bearerToken();
        if($token && ($request->path() != 'api/user-logout' && $request->path() != 'api/customer-logout')){
            Log::info('reinit coockie');
            Log::info('url = '. $url = $request->path());


            if ($request->hasCookie($cookie_user)) {
                $tokenCookie = CookieHelpers::getCookie($cookie_user, $token, config('jwt.refresh_ttl'));
            }
            if ($request->hasCookie($cookie_customer)) {
                $tokenCookie = CookieHelpers::getCookie($cookie_customer, $token, config('jwt.refresh_ttl'));
            }

            $response->withCookie($tokenCookie);
        }
        return $response;
    }
}
