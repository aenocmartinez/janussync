<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduledTask;

class ScheduledTaskSeeder extends Seeder
{
    public function run()
    {
        ScheduledTask::create([
            'task_name' => 'Ejemplo Diario',
            'frequency' => 'Diaria',
            'execution_time' => now()->addDay(),
        ]);

        ScheduledTask::create([
            'task_name' => 'Ejemplo Semanal',
            'frequency' => 'Semanal',
            'day_of_week' => 'Lunes',
            'execution_time' => now()->next('Monday'),
        ]);

        // Añade más ejemplos para otras frecuencias...
    }
}

