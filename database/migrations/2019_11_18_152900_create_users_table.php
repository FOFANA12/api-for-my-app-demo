<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('nom',50);
            $table->string('telephone',51)->unique()->nullable();//15
            $table->string('email',86)->unique();//100
            $table->string('password');
            $table->string('image')->nullable();
            $table->string('civilite')->nullable();
            $table->string('locale')->nullable();
            $table->boolean('statut')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('civilite')->references('id')->on('civilites')->onDelete('RESTRICT');
            $table->foreign('locale')->references('id')->on('locales')->onDelete('RESTRICT');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
