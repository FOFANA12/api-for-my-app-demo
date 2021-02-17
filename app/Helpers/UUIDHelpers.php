<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 19/11/2019
 * Time: 16:33
 */

namespace App\Helpers;

use Ramsey\Uuid\Uuid;

class UUIDHelpers
{

    public static function getUUID(){
        return Uuid::uuid4()->toString();
    }

}
