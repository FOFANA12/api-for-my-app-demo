<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Session\TokenMismatchException;
class CsrfAndRefreshToken
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
        $cookie_name = env('AUTH_COOKIE_NAME');
        if ($request->hasCookie($cookie_name)) {
            $token = $request->cookie($cookie_name);

            Log::info('COOCKIES EXISTE');

            try {
                if(!$request->headers->has('csrf-token')) throw new TokenMismatchException();
                else{
                    Log::info('csrf-token EXISTE');

                }
                //$rawToken = $request->cookie($cookie_name);
                //$token = new Token($rawToken);
                $payload = JWTAuth::decode($token);
                Log::info('*****');
                Log::info($payload);
                if($payload['csrf-token'] != $request->headers->get('csrf-token')) throw new TokenMismatchException();
                //Auth::loginUsingId($payload['sub']);
            } catch(\Exception $e) {
                if( $e instanceof TokenExpiredException) {
                    // If the token is expired, then it will be refreshed and added to the headers
                    try
                    {
                        $refreshed = JWTAuth::refresh($token);
                        //$user = JWTAuth::setToken($refreshed)->toUser();
                        $request->headers->set('Authorization','Bearer '.$refreshed);
                    }catch (\Exception $e){
                        return response()->json(['message' => 'Token cannot be refreshed, please Login again'],401);
                    }
                }
                return response()->json('Unauthorized.', 401);
            }

        }
        return $next($request);
    }
}
