<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scheduled_tasks', function (Blueprint $table) {
            $table->time('execution_time')->change(); // Cambiar el tipo a TIME
        });
    }
    
    public function down()
    {
        Schema::table('scheduled_tasks', function (Blueprint $table) {
            $table->dateTime('execution_time')->change(); // Revertir al tipo original en caso de rollback
        });
    }
    
};
