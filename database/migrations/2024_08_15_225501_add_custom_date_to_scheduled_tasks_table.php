<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomDateToScheduledTasksTable extends Migration
{
    public function up()
    {
        Schema::table('scheduled_tasks', function (Blueprint $table) {
            $table->date('custom_date')->nullable()->after('frequency');
        });
    }

    public function down()
    {
        Schema::table('scheduled_tasks', function (Blueprint $table) {
            $table->dropColumn('custom_date');
        });
    }
}
