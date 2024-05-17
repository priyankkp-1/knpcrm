<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Product extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->string('name',240);
            $table->longText('description')->nullable();
            $table->decimal('rate',13,2)->nullable();
            $table->string('unit',40)->nullable();
            $table->bigInteger('tax_id')->unsigned()->index()->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('cascade');
            $table->bigInteger('tax2_id')->unsigned()->index()->nullable();
            $table->foreign('tax2_id')->references('id')->on('taxes')->onDelete('cascade');
            $table->bigInteger('itemgroup_id')->unsigned()->index()->nullable();
            $table->foreign('itemgroup_id')->references('id')->on('item_groups')->onDelete('cascade');
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
        Schema::dropIfExists('products');
    }
}
