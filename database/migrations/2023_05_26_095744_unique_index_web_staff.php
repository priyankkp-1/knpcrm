<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UniqueIndexWebStaff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_to_lead_staff_notify_rel', function (Blueprint $table) {
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
