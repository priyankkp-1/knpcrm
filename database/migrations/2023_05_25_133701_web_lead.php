<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebLead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_to_lead', function (Blueprint $table) {
            $table->id();
            $table->string('hash_id',255);
            $table->string('form_name',255);
            $table->longText('form_data');
            $table->bigInteger('status_id')->unsigned()->index()->nullable();
            $table->foreign('status_id')->references('id')->on('status')->onDelete('cascade');
            $table->bigInteger('source_id')->unsigned()->index()->nullable();
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->string('submit_button',100);
            $table->tinyInteger('responsible');
            $table->string('message_after_success',255);
            $table->longText('thank_you_page_link');
            $table->tinyInteger('allow_duplicate_lead_for_entry');
            $table->tinyInteger('mark_as_public');
            $table->tinyInteger('notify_when_lead_import');
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
        Schema::dropIfExists('web_to_lead');
    }
}
