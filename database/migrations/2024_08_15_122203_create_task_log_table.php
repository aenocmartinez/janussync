<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_task_id')->constrained('scheduled_tasks')->onDelete('cascade');
            $table->enum('status', ['successful', 'failed'])->default('failed');
            $table->timestamp('completed_at')->nullable();
            $table->longText('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_log');
    }
};
