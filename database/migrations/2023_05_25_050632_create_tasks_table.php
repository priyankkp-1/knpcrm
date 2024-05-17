<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->string('name',240);
            $table->enum('priority', ['low', 'medium', 'heigh', 'urgent']);
            $table->enum('status', ['pending', 'inprogress', 'testing', 'waiting_for_feedback', 'complete']);
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('completed_date')->nullable();
            $table->longText('description')->nullable();
            $table->enum('rel_type', ['lead', 'customer']);
            $table->bigInteger('rel_id')->unsigned()->nullable();
            $table->bigInteger('addedfrom')->unsigned()->index()->nullable();
            $table->foreign('addedfrom')->references('id')->on('admins')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
        });


        Schema::create('task_assigned', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('staff_id')->unsigned()->index()->nullable();
            $table->foreign('staff_id')->references('id')->on('admins')->onDelete('cascade');
            $table->bigInteger('task_id')->unsigned()->index()->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });

        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->longText('content')->nullable();
            $table->bigInteger('staff_id')->unsigned()->index()->nullable();
            $table->foreign('staff_id')->references('id')->on('admins')->onDelete('cascade');
            $table->bigInteger('task_id')->unsigned()->index()->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->bigInteger('file_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('task_documents', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->bigInteger('task_id')->unsigned()->index()->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->bigInteger('file_id')->unsigned()->nullable();
            $table->timestamps();
        });
        
        Schema::create('task_checklist', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->longText('description')->nullable();
            $table->bigInteger('task_id')->unsigned()->index()->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->bigInteger('added_from')->unsigned()->index()->nullable();
            $table->foreign('added_from')->references('id')->on('admins')->onDelete('cascade');
            $table->bigInteger('finished_from')->unsigned()->index()->nullable();
            $table->foreign('finished_from')->references('id')->on('admins')->onDelete('cascade');
            $table->integer('list_order')->default(0);
            $table->tinyInteger('is_finished')->default(0);
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
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_assigned');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_documents');
        Schema::dropIfExists('task_checklist');
    }
}
