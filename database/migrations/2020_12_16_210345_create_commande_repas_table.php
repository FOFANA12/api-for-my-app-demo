<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class CreateCommandeRepasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commande_repas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('reference',100)->unique();
            $table->string('customer');
            $table->dateTime('date');
            $table->tinyInteger('statut')->default(0);// -1 = encours, 1 = validée, 0 = refusée
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer')->references('id')->on('customers')->onDelete('RESTRICT');
        });
        Schema::table('commande_repas', function (Blueprint $table) {
           DB::statement('ALTER TABLE commande_repas ADD compteur INT NOT NULL AUTO_INCREMENT AFTER id,  ADD INDEX (compteur)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commande_repas');
    }
}
