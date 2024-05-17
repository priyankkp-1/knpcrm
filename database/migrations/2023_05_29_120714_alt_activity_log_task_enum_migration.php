<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AltActivityLogTaskEnumMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE activity_logs CHANGE COLUMN rel_type rel_type ENUM('lead','customer','staff','other','company_settings','role','email_template','source','customers-groups','currencies','payment-modes','contract-types','expences-categories','taxes','status','announcement','itemgroup','products','custom_fields','web_to_lead','tasks') NOT NULL ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE activity_logs CHANGE COLUMN rel_type rel_type ENUM('lead','customer','staff','other','company_settings','role','email_template','source','customers-groups','currencies','payment-modes','contract-types','expences-categories','taxes','status','announcement','itemgroup','products','custom_fields','web_to_lead') NOT NULL ");
    }
}