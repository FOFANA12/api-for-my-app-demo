<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\MemberStatut;
use App\Models\Civilite;
use App\Models\Locale;
class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customers')->delete();

        $memberStatut = MemberStatut::first()->id;
        $civilite = Civilite::first()->id;
        $locale = Locale::first()->id;

        Customer::create([
            'nom' => 'DIAKITE',
            'prenom' => 'MAMADOU',
            'member_statut' => $memberStatut,
            'civilite' => $civilite,
            'locale' => $locale,
            'email' => 'diakite.mamadou@laposte.net',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
        ]);
    }
}
