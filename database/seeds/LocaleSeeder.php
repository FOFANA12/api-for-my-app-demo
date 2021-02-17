<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Locale;
class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('locales')->delete();

        Locale::create([
            'code' => 'fr',
            'libelle' => 'Français',
            'statut' => true,
        ]);

        Locale::create([
            'code' => 'en',
            'libelle' => 'Anglais',
            'statut' => true,
        ]);
    }
}
