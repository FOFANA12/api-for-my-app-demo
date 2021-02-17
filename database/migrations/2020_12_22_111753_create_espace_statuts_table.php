<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEspaceStatutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('espace_statuts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('espace');
            $table->string('statut');
            $table->softDeletes();

            $table->foreign('espace')->references('id')->on('espaces')->onDelete('CASCADE');
            $table->foreign('statut')->references('id')->on('member_statuts')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('espace_statuts');
    }
}
