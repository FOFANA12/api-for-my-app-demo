<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemCommandeRepasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_commande_repas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('commande');
            $table->string('menu');
            $table->integer('quantite');
            $table->decimal('prix');
            $table->softDeletes();

            $table->foreign('commande')->references('id')->on('commande_repas')->onDelete('CASCADE');
            $table->foreign('menu')->references('id')->on('menus')->onDelete('RESTRICT');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_commande_repas');
    }
}
