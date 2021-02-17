<?php
/**
 * Created by PhpStorm.
 * User: TOSHIBA
 * Date: 03/08/2019
 * Time: 18:36
 */

namespace App\Helpers;

class CookieHelpers
{
    public static function getCookie($name, $token, $time){
        return cookie(
         $name,
         $token,
         $time,
        '/',
        null,
        env('APP_DEBUG') ? false : true,
        true,
        false,
        'Strict'
        );
    }

}
