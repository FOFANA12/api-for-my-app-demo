<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEspaceReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('espace_reservations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('espace_event');
            $table->string('customer');
            $table->tinyInteger('statut')->default(1);//1 = attente, 2 == confirmée, 3 == refusée
            $table->softDeletes();

            $table->foreign('espace_event')->references('id')->on('espace_events')->onDelete('CASCADE');
            $table->foreign('customer')->references('id')->on('customers')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('espace_reservations');
    }
}
