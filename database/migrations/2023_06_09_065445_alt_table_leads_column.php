<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AltTableLeadsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->bigInteger('web_to_lead_id')->unsigned()->index()->nullable()->after('action_date_time');
            $table->foreign('web_to_lead_id')->references('id')->on('web_to_lead')->onDelete('cascade');
            $table->tinyInteger('is_public')->default(0)->after('first_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign('leads_web_to_lead_id_foreign');
            $table->dropColumn('web_to_lead_id');
            $table->dropColumn('is_public');
        });
    }
}
