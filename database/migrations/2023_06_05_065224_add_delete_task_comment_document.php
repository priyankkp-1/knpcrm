<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeleteTaskCommentDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_comments', function($table) {
            $table->softDeletes('deleted_at', 0);
        });

        Schema::table('task_documents', function($table) {
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
        Schema::table('task_comments', function($table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('task_documents', function($table) {
            $table->dropColumn('deleted_at');
        });
    }
}
