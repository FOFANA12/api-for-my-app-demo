<?php

use Illuminate\Database\Seeder;

class AllSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CiviliteSeeder::class);
        $this->call(LocaleSeeder::class);
        $this->call(MemberStatusSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(PermissionSeeder::class);
    }
}
