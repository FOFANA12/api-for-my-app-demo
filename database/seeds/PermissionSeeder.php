<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::defaultPermissions();

        foreach ($permissions as $groupe => $perms){
            foreach ($perms as $perm){
                Permission::firstOrCreate(['perm_group' => $groupe,'name' => $perm, 'guard_name'=>'api']);
            }
        }
    }
}
