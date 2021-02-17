<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_invitations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('event');
            $table->string('customer');
            $table->tinyInteger('participation')->default(-1);
            $table->tinyInteger('approbation')->default(-1);
            $table->dateTime('participation_date')->nullable();
            $table->dateTime('approbation_date')->nullable();
            $table->tinyInteger('notify')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event')->references('id')->on('events')->onDelete('CASCADE');
            $table->foreign('customer')->references('id')->on('customers')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_invitations');
    }
}
