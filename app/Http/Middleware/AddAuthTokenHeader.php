<?php

namespace App\Http\Middleware;

use App\Models\JWT;
use Closure;
use Illuminate\Support\Facades\Log;

class AddAuthTokenHeader
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
        $cookie_user = 'token_user';
        $cookie_customer = 'token_customer';

        if (!$request->bearerToken()) {
            $token = '';
            if ($request->hasCookie($cookie_user)) {//for token user
                $token = $request->cookie($cookie_user);
            }if ($request->hasCookie($cookie_customer)) {//for token customer
                $token = $request->cookie($cookie_customer);
            }

            if ($token) {
                $request->headers->add([
                    'Authorization' => 'Bearer '.$token
                ]);
                Log::info('Add token in header');
                Log::info($token);
            }
        }

        return $next($request);
    }
}
