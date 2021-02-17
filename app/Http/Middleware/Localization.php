<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Config;

class Localization
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
        if(!$request->header('locale')) {
            app()->setLocale($request->getPreferredLanguage(config('app.locales')));
        }

        if(setting('appTimeZone')){
            Config::set('app.timezone',setting('appTimeZone'));
            date_default_timezone_set(config('app.timezone'));
        }

        app()->setLocale($request->header('locale'));
        return $next($request);
    }
}
