<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->string('first_name',200)->nullable();
            $table->string('last_name',200)->nullable();
            $table->string('company',200)->nullable();
            $table->bigInteger('status_id')->unsigned()->index()->nullable();
            $table->foreign('status_id')->references('id')->on('status')->onDelete('cascade');
            $table->string('job_title',200)->nullable();
            $table->bigInteger('assigned')->unsigned()->index()->nullable();
            $table->foreign('assigned')->references('id')->on('admins')->onDelete('cascade');
            $table->string('city',100)->nullable();
            $table->string('state',100)->nullable();
            $table->integer('country_id')->unsigned()->index()->nullable();
            $table->string('zip',10)->nullable();
            $table->longText('address')->nullable();
            $table->longText('address_1')->nullable();
            $table->longText('address_2')->nullable();
            $table->string('email',200)->unique();
            $table->string('country_code',10)->nullable();
            $table->string('phone_number',20)->nullable();
            $table->bigInteger('source_id')->unsigned()->index()->nullable();
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
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
