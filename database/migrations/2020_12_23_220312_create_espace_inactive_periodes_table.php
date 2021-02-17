<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEspaceInactivePeriodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('espace_inactive_periodes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('espace_event');
            $table->string('text',100)->nullable();
            $table->softDeletes();

            $table->foreign('espace_event')->references('id')->on('espace_events')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('espace_inactive_periodes');
    }
}
