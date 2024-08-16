<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogTasksTable extends Migration
{
    public function up()
    {
        Schema::create('log_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_task_id')->constrained()->onDelete('cascade');
            $table->timestamp('executed_at');
            $table->boolean('was_successful');
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('log_tasks');
    }
}

