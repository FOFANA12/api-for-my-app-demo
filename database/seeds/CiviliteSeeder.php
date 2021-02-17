<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Civilite;
class CiviliteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('civilites')->delete();

        Civilite::create([
            'nom' => 'Monsieur',
            'statut' => true,
        ]);

        Civilite::create([
            'nom' => 'Madame',
            'statut' => true,
        ]);
    }
}
