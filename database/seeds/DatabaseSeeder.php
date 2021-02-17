<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->call('cache:clear');
        $this->command->call('config:cache');

        $this->command->info('Exécution de migration');

        $this->command->call('migrate:refresh');
        $this->command->warn("Données effacées.");

        $this->call(AllSeeder::class);

        // Seed the default roles
        $roles = Role::defaultRoles();
        foreach ($roles as $item) {
            $role = Role::firstOrCreate([
                'name' => $item['name'],
                'guard_name' => $item['guard_name'],
            ]);

            if( $item['name'] == 'Admin') {
                $role->syncPermissions(Permission::all());
            } else {
                // for others by default only read access
                $role->syncPermissions(Permission::where('name', 'LIKE', 'view%')->get());
            }
        }

        $this->command->info('Rôles par défaut ajoutées.');

        $Admin = \App\Models\User::defaultUser();
        $Admin->save();

        $roleAdmin = Role::where('name','Admin')->first();
        $Admin->assignRole($roleAdmin);

        $this->command->info('Info de Admin : ');
        $this->command->warn($Admin->email);
        $this->command->warn('Mot de passe est 123456');

        $this->command->call('jwt:secret');
    }
}
