<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduledTask;
use Illuminate\Support\Carbon;

class ScheduledTasksSeeder extends Seeder
{
    public function run()
    {
        $tasks = [
            [
                'name' => 'Backup Diario de Base de Datos',
                'status' => 'active',
                'frequency' => 'Diaria',
            ],
            [
                'name' => 'Reporte Semanal de Ventas',
                'status' => 'active',
                'frequency' => 'Semanal',
            ],
            [
                'name' => 'Mantenimiento Mensual del Sistema',
                'status' => 'inactive',
                'frequency' => 'Mensual',
            ],
            [
                'name' => 'Revisión Anual de Seguridad',
                'status' => 'active',
                'frequency' => 'Anual',
            ],
            [
                'name' => 'Actualización Quincenal de Inventario',
                'status' => 'active',
                'frequency' => 'Quincenal',
            ],
            [
                'name' => 'Notificación de Cumpleaños Personalizada',
                'status' => 'active',
                'frequency' => 'Personalizada',
                'custom_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'custom_time' => '08:00:00',
            ],
            [
                'name' => 'Envío de Boletín Mensual',
                'status' => 'active',
                'frequency' => 'Mensual',
            ],
            [
                'name' => 'Generación de Reporte de Rendimiento',
                'status' => 'inactive',
                'frequency' => 'Semanal',
            ],
            [
                'name' => 'Backup Anual Completo',
                'status' => 'active',
                'frequency' => 'Anual',
            ],
            [
                'name' => 'Limpieza Quincenal de Logs',
                'status' => 'inactive',
                'frequency' => 'Quincenal',
            ],
        ];

        foreach ($tasks as $task) {
            ScheduledTask::create($task);
        }
    }
}
