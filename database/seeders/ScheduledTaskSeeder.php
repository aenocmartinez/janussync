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

        foreach ($tasks as $taskData) {
            $scheduledTask = ScheduledTask::create($taskData);

            TaskLog::create([
                'scheduled_task_id' => $scheduledTask->id,
                'executed_at' => now(), 
                'was_successful' => false, 
                'details' => 'Initial task log entry.',
            ]);
        }
    }
}
