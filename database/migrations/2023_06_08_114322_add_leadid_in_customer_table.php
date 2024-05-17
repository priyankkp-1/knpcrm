<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeadidInCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->bigInteger('lead_id')->unsigned()->index()->nullable();
            $table->foreign('lead_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('date_customer_converted')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('lead_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('date_customer_converted');
        });
        
    }
}
