<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactColumnToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `users` CHANGE `last_name` `last_name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, CHANGE `country_code` `country_code` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, CHANGE `phone_number` `phone_number` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, CHANGE `is_primary` `is_primary` TINYINT NOT NULL DEFAULT '0', CHANGE `credit_note_emails` `credit_note_emails` TINYINT NOT NULL DEFAULT '0', CHANGE `task_emails` `task_emails` TINYINT NOT NULL DEFAULT '0'");

        Schema::table('users', function (Blueprint $table) {

            $table->bigInteger('customers_id')->unsigned()->index()->nullable()->after('is_primary');
            $table->foreign('customers_id')->references('id')->on('customers')->onDelete('cascade');
            $table->bigInteger('profile_img')->unsigned()->index()->nullable()->after('is_primary');
            $table->string('first_name',200)->nullable()->after('is_primary');
            $table->tinyInteger('is_active')->default(1)->after('is_primary');
            $table->tinyInteger('project_emails')->default(1)->after('is_primary');
            $table->tinyInteger('contract_emails')->default(1)->after('is_primary');
            $table->tinyInteger('proposals_emails')->default(1)->after('is_primary');
            $table->tinyInteger('support_emails')->default(1)->after('is_primary');
            $table->tinyInteger('project_permissions')->default(1)->after('is_primary');
            $table->tinyInteger('contract_permissions')->default(1)->after('is_primary');
            $table->timestamp('last_login')->nullable()->after('is_primary');
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
            $table->dropColumn('customers_id');
            $table->dropColumn('profile_img');
            $table->dropColumn('is_active');
            $table->dropColumn('project_emails');
            $table->dropColumn('contract_emails');
            $table->dropColumn('proposals_emails');
            $table->dropColumn('support_emails');
            $table->dropColumn('project_permissions');
            $table->dropColumn('contract_permissions');
            $table->dropColumn('last_login');
        });
    }
}
