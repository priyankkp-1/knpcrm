<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomFieldValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_field_value', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->enum('field_to',['customer', 'lead']);
            $table->longText('value');
            $table->bigInteger('rel_column_id')->default(1);
            $table->bigInteger('field_id')->unsigned()->index()->nullable();
            $table->foreign('field_id')->references('id')->on('custom_fields')->onDelete('cascade');
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
        Schema::dropIfExists('custom_field_value');
    }
}
