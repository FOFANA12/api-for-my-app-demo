<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('restaurant');
            $table->string('nom',86)->unique();
            $table->string('description',100)->nullable();//15
            $table->double('prix',15);
            $table->string('image')->nullable();
            $table->boolean('statut')->default(true);
            $table->string('created_by')->nullable()->default(null);
            $table->string('updated_by')->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('restaurant')->references('id')->on('restaurants')->onDelete('RESTRICT');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
}
