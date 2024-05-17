<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins_permissions', function (Blueprint $table) {
            $table->bigInteger('admin_id')->unsigned();
            $table->bigInteger('permission_id')->unsigned();

            //FOREIGN KEY
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            //PRIMARY KEYS
            $table->primary(['admin_id','permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins_permissions');
    }
}
