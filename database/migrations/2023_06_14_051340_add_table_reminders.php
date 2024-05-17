<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableReminders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->string('hash_id',255)->after('id');
            $table->timestamp('reminder_date_time')->after('hash_id');
            $table->enum('repeat_frequently', ['onetime', 'daily', 'monthly', 'weekly', 'yearly'])->after('addedfrom');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropColumn('repeat_frequently');
            $table->dropColumn('hash_id');
            $table->dropColumn('reminder_date_time');
        });
    }
}
