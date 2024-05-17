<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->longText('description')->nullable();
            $table->enum('rel_type', ['lead', 'customer']);
            $table->bigInteger('rel_id')->unsigned()->nullable();
            $table->tinyInteger('is_notified')->default(0);
            $table->tinyInteger('notify_by_email')->default(0);
            $table->bigInteger('assign_to')->unsigned()->index()->nullable();
            $table->foreign('assign_to')->references('id')->on('admins')->onDelete('cascade');
            $table->bigInteger('addedfrom')->unsigned()->index()->nullable();
            $table->foreign('addedfrom')->references('id')->on('admins')->onDelete('cascade');
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
        Schema::dropIfExists('reminders');
    }
}
