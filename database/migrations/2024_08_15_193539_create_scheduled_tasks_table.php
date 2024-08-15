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
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_name')->unique();
            $table->enum('frequency', ['Diaria', 'Semanal', 'Mensual', 'Semestral', 'Anual', 'Personalizada']);
            $table->dateTime('execution_time')->nullable(); // Solo para Personalizada
            $table->string('day_of_week')->nullable(); // Para frecuencia semanal
            $table->string('day_of_month')->nullable(); // Para frecuencia mensual
            $table->string('month')->nullable(); // Para frecuencia semestral y anual
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
