<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('client_id');
            $table->string('last_name',200);
            $table->string('country_code',10);
            $table->string('phone_number',20);
            $table->string('last_ip',40)->nullable();
            $table->string('job_title',200)->nullable();
            $table->tinyInteger('is_primary')->default(1);
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('invoice_emails')->default(1);
            $table->tinyInteger('estimate_emails')->default(1);
            $table->tinyInteger('credit_note_emails')->default(1);
            $table->tinyInteger('task_emails')->default(1);
            $table->tinyInteger('invoice_permissions')->default(1);
            $table->tinyInteger('estimate_permissions')->default(1);
            $table->tinyInteger('proposals_permissions')->default(1);
            $table->tinyInteger('support_permissions')->default(1);
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('client_id');
            $table->dropColumn('last_name');
            $table->dropColumn('country_code');
            $table->dropColumn('phone_number');
            $table->dropColumn('last_ip');
            $table->dropColumn('job_title');
            $table->dropColumn('is_primary');
            $table->dropColumn('active');
            $table->dropColumn('invoice_emails');
            $table->dropColumn('estimate_emails');
            $table->dropColumn('credit_note_emails');
            $table->dropColumn('task_emails');
            $table->dropColumn('invoice_permissions');
            $table->dropColumn('estimate_permissions');
            $table->dropColumn('proposals_permissions');
            $table->dropColumn('support_permissions');
            $table->dropColumn('deleted_at');
        });
    }
}
