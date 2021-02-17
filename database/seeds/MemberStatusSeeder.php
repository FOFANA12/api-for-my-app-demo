<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MemberStatut;
class MemberStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('member_statuts')->delete();

        MemberStatut::create([
            'nom' => 'Full',
            'statut' => true,
        ]);

        MemberStatut::create([
            'nom' => 'Part-time',
            'statut' => true,
        ]);

        MemberStatut::create([
            'nom' => 'Visitor',
            'statut' => true,
        ]);

        MemberStatut::create([
            'nom' => 'Guest',
            'statut' => true,
        ]);
    }
}
