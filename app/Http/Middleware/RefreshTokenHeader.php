<?php

namespace App\Http\Middleware;

use App\Models\JWT;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
class RefreshTokenHeader
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
        try {

            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception  $e) {
            Log::info($e->getMessage());
            try
            {
                $refreshed = JWTAuth::refresh(JWTAuth::getToken());
                $request->headers->set('Authorization','Bearer '.$refreshed);

            }catch (JWTException $e){
                return response()->json(['message' => 'Token cannot be refreshed, please Login again'],401);
            }
            $user = JWTAuth::setToken($refreshed)->toUser();
        }catch (JWTException $e)
        {
            return response()->json(['message' => 'Authorization Token not found'], 404);
        }

        //Auth::login($user, false);

        return  $next($request);
    }
}
