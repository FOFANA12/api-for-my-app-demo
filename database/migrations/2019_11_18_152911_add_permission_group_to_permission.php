<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionGroupToPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('perm_group');
            $table->string('created_by')->nullable()->default(null);
            $table->string('updated_by')->nullable()->default(null);

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('created_by')->nullable()->default(null);
            $table->string('updated_by')->nullable()->default(null);

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
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('perm_group');

            $table->dropForeign('permissions_created_by_foreign');
            $table->dropForeign('permissions_updated_by_foreign');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign('roles_created_by_foreign');
            $table->dropForeign('roles_updated_by_foreign');

            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
}
