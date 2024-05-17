<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWebLeadStaffRel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_to_lead_staff_notify_rel', function (Blueprint $table) {
            $table->bigInteger('web_to_lead_id')->unsigned()->index()->nullable();
            $table->foreign('web_to_lead_id')->references('id')->on('web_to_lead')->onDelete('cascade');
            $table->bigInteger('staff_id')->unsigned()->index()->nullable();
            $table->foreign('staff_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique(['web_to_lead_id', 'staff_id'], 'unique_index_web_staff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_to_lead_staff_notify_rel');
    }
}
