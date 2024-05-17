<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->enum('field_to',['customer', 'lead']);
            $table->longText('name',255);
            $table->longText('slug');
            $table->tinyInteger('required')->default(0);
            $table->string('type');
            $table->enum('options',['input','hidden','number','textarea','select','multiselect','checkbox','date_picker','date_picker_time','colorpicker','link']);
            $table->integer('field_order');
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('show_on_table')->default(0);
            $table->bigInteger('bs_column');
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
        Schema::dropIfExists('custom_fields');
    }
}
