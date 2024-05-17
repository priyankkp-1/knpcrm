<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->string('header_title');
            $table->string('header_color');
            $table->string('header_front_size');
            $table->string('header_background_color');
            $table->string('form_title');
            $table->string('form_front_size');
            $table->string('form_button_color');
            $table->string('form_button_background_color');
            $table->string('footer_title');
            $table->string('footer_color');
            $table->string('footer_front_size');
            $table->string('footer_background_color');
            $table->longText('body');
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
        Schema::dropIfExists('templates');
    }
}
