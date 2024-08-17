<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scheduled_tasks', function (Blueprint $table) {
            $table->string('action')->after('custom_date')->nullable(); // Campo para la acción
        });
    }
    
    public function down()
    {
        Schema::table('scheduled_tasks', function (Blueprint $table) {
            $table->dropColumn('action');
        });
    }    
};
