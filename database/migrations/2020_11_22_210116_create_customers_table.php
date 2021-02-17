<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('nom',50);//surname
            $table->string('prenom',50);//first name
            $table->string('entreprise',30)->nullable();
            $table->string('adresse',100)->nullable();
            $table->string('member_statut');
            $table->string('email',86)->unique();//100
            $table->string('telephone',51)->unique()->nullable();//15
            $table->string('num_passeport',15)->nullable();
            $table->string('passeport_file')->nullable();
            $table->string('passeport_file_name',100)->nullable();
            $table->string('password');
            $table->string('condition_medical',50)->nullable();
            $table->string('contact_urgence_nom',50)->nullable();
            $table->string('contact_urgence_telephone',15)->nullable();
            $table->string('image')->nullable();
            $table->string('civilite')->nullable();
            $table->string('locale')->nullable();
            $table->boolean('statut')->default(true);
            $table->string('created_by')->nullable()->default(null);
            $table->string('updated_by')->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('member_statut')->references('id')->on('member_statuts')->onDelete('RESTRICT');
            $table->foreign('civilite')->references('id')->on('civilites')->onDelete('RESTRICT');
            $table->foreign('locale')->references('id')->on('locales')->onDelete('RESTRICT');
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
        Schema::dropIfExists('customers');
    }
}
