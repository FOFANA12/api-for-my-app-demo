<?php

namespace App\Models;

class Role extends \Spatie\Permission\Models\Role
{
    public static function defaultRoles()
    {
        return [
            array('name'=>'Admin','guard_name'=>'api'),
            array('name'=>'Gestionnaire','guard_name'=>'api'),
        ];
    }
}
