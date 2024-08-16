<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledTask;
use App\Models\LogTask;
use Carbon\Carbon;

class RunScheduledTasks extends Command
{
    protected $signature = 'tasks:run';

    protected $description = 'Run scheduled tasks';

    public function handle()
    {
        $tasks = ScheduledTask::all();

        foreach ($tasks as $task) {
            if ($this->shouldRunTask($task)) {
                $this->runTask($task);
            }
        }
    }

    protected function shouldRunTask(ScheduledTask $task)
    {
        $now = Carbon::now();
    
        switch ($task->frequency) {
            case 'daily':
                return $this->shouldRunDaily($task, $now);
    
            case 'weekly':
                return $this->shouldRunWeekly($task, $now);
    
            case 'monthly':
                return $this->shouldRunMonthly($task, $now);
    
            case 'custom_date':
                return $this->shouldRunCustomDate($task, $now);
    
            default:
                return false;
        }
    }
    
    protected function shouldRunDaily(ScheduledTask $task, Carbon $now)
    {
        // Comprueba si la hora actual coincide con la hora de ejecución programada
        return $now->format('H:i') === $task->execution_time;
    }
    
    protected function shouldRunWeekly(ScheduledTask $task, Carbon $now)
    {
        // Comprueba si el día de la semana y la hora coinciden con los programados
        return $now->isSameDayOfWeek($task->day_of_week) && $now->format('H:i') === $task->execution_time;
    }
    
    protected function shouldRunMonthly(ScheduledTask $task, Carbon $now)
    {
        // Comprueba si el día del mes y la hora coinciden con los programados
        return $now->day === (int)$task->day_of_month && $now->format('H:i') === $task->execution_time;
    }
    
    protected function shouldRunCustomDate(ScheduledTask $task, Carbon $now)
    {
        // Comprueba si la fecha personalizada coincide con la fecha y hora actuales
        return $now->toDateTimeString() === Carbon::parse($task->custom_date)->toDateTimeString();
    }

    protected function runTask(ScheduledTask $task)
    {
        $wasSuccessful = false;
        $details = '';

        try {
            // Verifica si la tarea es diaria
            if ($task->frequency === 'daily') {
                // Escribe en el archivo cron.log indicando que la tarea diaria se ejecutó
                file_put_contents('/Applications/MAMP/htdocs/janussync/cron.log', "Tarea diaria ejecutada: " . $task->task_name . " a las " . Carbon::now()->toDateTimeString() . "\n", FILE_APPEND);
            }

            // Aquí va la lógica para ejecutar la tarea
            // Puedes agregar la lógica real que la tarea debe realizar aquí

            $wasSuccessful = true;
        } catch (\Exception $e) {
            $details = $e->getMessage();
        }

        // Loguea la ejecución de la tarea en la base de datos
        LogTask::create([
            'scheduled_task_id' => $task->id,
            'executed_at' => Carbon::now(),
            'was_successful' => $wasSuccessful,
            'details' => $details,
        ]);
    }


    // protected function runTask(ScheduledTask $task)
    // {
    //     $wasSuccessful = false;
    //     $details = '';

    //     try {
    //         // Aquí va la lógica para ejecutar la tarea
    //         // Por ejemplo, podrías usar `exec()` para ejecutar comandos del sistema
    //         echo "Se ejecuta correctamente el CRON";

    //         $wasSuccessful = true;
    //     } catch (\Exception $e) {
    //         $details = $e->getMessage();
    //     }

    //     // Loguea la ejecución de la tarea
    //     LogTask::create([
    //         'scheduled_task_id' => $task->id,
    //         'executed_at' => Carbon::now(),
    //         'was_successful' => $wasSuccessful,
    //         'details' => $details,
    //     ]);
    // }
}
