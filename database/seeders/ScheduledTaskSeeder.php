<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduledTask;
use App\Models\TaskLog;

class ScheduledTaskSeeder extends Seeder
{
    public function run()
    {
        $tasks = [
            [
                'task_name' => 'Creación de cursos',
                'frequency' => 'Diaria',
                'execution_time' => now()->addDay(),
            ],
            [
                'task_name' => 'Creación de usuarios',
                'frequency' => 'Diaria',
                'execution_time' => now()->addDay(),
            ],
        ];

        foreach ($tasks as $index => $taskData) {
            $scheduledTask = ScheduledTask::create($taskData);

            $comments = "Se presenta un error al intentar crear cursos.";
            $success = false;
            if ($index == 1) {
                $comments = "Fueron creados 15 nuevos usuarios";
                $success = true;
            }

            TaskLog::create([
                'scheduled_task_id' => $scheduledTask->id,
                'executed_at' => now(), 
                'was_successful' => $success, 
                'details' => $comments,
            ]);
        }
    }
}
