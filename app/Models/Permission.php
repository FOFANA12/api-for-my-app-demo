<?php

namespace App\Models;

class Permission extends \Spatie\Permission\Models\Permission
{
    public static function defaultPermissions()
    {
        return [
            'customer' => [
                'view customer',
                'create customer',
                'edit customer',
                'delete customer'
            ],
        ];
    }
}
