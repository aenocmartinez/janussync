<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class ScheduledTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name', 
        'frequency', 
        'execution_time', 
        'day_of_week', 
        'day_of_month', 
        'month',
        'custom_date',
        'action', 
    ];

    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class);
    }   
    
    public function getLastExecutionDetails()
    {
        $latestLog = $this->taskLogs()->orderBy('executed_at', 'desc')->first();

        Carbon::setLocale('es');
        $date_execution = Carbon::parse($this->custom_date)->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i \h\o\r\a\s');
    
        return [
            'task_id' => $this->id,
            'task_name' => $this->task_name,
            'status' => $latestLog ? ($latestLog->was_successful ? 'Completado' : 'Tarea Fallida') : 'Programada',
            'status_boolean' => $latestLog ? $latestLog->was_successful : false,
            'execution_time' => $latestLog ? $latestLog->executed_at : 'N/A', 
            'frequency' => $this->frequency,
            'details' => $latestLog ? $latestLog->details : 'Esta tarea se ejecutará el ' . $date_execution,
            'log_id' => $latestLog ? $latestLog->id : 'N/A',
        ];
    }

    public function execute()
    {
        if ($this->action) {
            $action = app($this->action); // Instancia la acción
            $result = $action->handle();  // Ejecuta la acción

            $this->taskLogs()->create([
                'was_successful' => $result['success'],
                'details' => $result['details'],
                'executed_at' => now(),
            ]);

            return $result;
        }

        return ['success' => false, 'details' => 'No se encontró acción asociada.'];
    }

    public static function getAllLastExecutions()
    {
        return self::all()->map(function ($task) {
            return $task->getLastExecutionDetails();
        });
    }  
    
    public static function countLastExecutionResults()
    {
        $lastLogs = ScheduledTask::with(['taskLogs' => function($query) {
            $query->orderBy('executed_at', 'desc');
        }])
        ->get()
        ->map(function ($task) {
            return $task->taskLogs->first();
        })
        ->filter();

        $totalLogs = $lastLogs->count();
        $successfulLogs = $lastLogs->where('was_successful', true)->count();
        $failedLogs = $lastLogs->where('was_successful', false)->count();
    
        $successPercentage = $totalLogs > 0 ? round(($successfulLogs / $totalLogs) * 100) : 0;
        $failurePercentage = $totalLogs > 0 ? round(($failedLogs / $totalLogs) * 100) : 0;
    
        if ($successPercentage + $failurePercentage !== 100) {
            $difference = 100 - ($successPercentage + $failurePercentage);
            if ($successPercentage > $failurePercentage) {
                $successPercentage += $difference;
            } else {
                $failurePercentage += $difference;
            }
        }
    
        return [
            'total_logs' => $totalLogs,          
            'successful_logs' => $successfulLogs, 
            'failed_logs' => $failedLogs,         
            'success_percentage' => $successPercentage, 
            'failure_percentage' => $failurePercentage, 
        ];
    }

    public static function getAvailableActions()
    {
        $actionsPath = app_path('Actions');
        $actionFiles = File::allFiles($actionsPath);

        $actions = [];
        foreach ($actionFiles as $file) {
            $namespace = 'App\\Actions\\';
            $className = $namespace . Str::replace('.php', '', $file->getFilename());

            $reflection = new ReflectionClass($className);
            if ($reflection->isInstantiable() && $reflection->hasMethod('handle')) {
                $actions[$className] = Str::headline(Str::replace('Action', '', $file->getFilename()));
            }
        }

        return $actions;
    }

    public static function checkAndRunScheduledTasks()
    {
        $tasks = self::all();
        $now = Carbon::now()->setTimezone('America/Bogota');
        
        // Log::info("Running check at: " . $now->toDateTimeString());
    
        $frequencyChecks = [
            'Diaria' => function($task) use ($now) {
                // Log::info("Checking daily task: {$task->task_name} at {$task->execution_time}. Current time: " . $now->format('H:i:s'));
                $shouldRun = $now->format('H:i') === Carbon::parse($task->execution_time)->format('H:i');
                // Log::info("Should run: " . ($shouldRun ? "Yes" : "No"));
                return $shouldRun;
            },
            'Semanal' => function($task) use ($now) {
                // Log::info("Checking weekly task: {$task->task_name} on {$task->day_of_week} at {$task->execution_time}. Current time: " . $now->format('H:i:s'));
                $shouldRun = $now->format('l') === $task->day_of_week && $now->format('H:i') === Carbon::parse($task->execution_time)->format('H:i');
                // Log::info("Should run: " . ($shouldRun ? "Yes" : "No"));
                return $shouldRun;
            },
            'Mensual' => function($task) use ($now) {
                // Log::info("Checking monthly task: {$task->task_name} on day {$task->day_of_month} at {$task->execution_time}. Current time: " . $now->format('H:i:s'));
                $shouldRun = $now->day == $task->day_of_month && $now->format('H:i') === Carbon::parse($task->execution_time)->format('H:i');
                // Log::info("Should run: " . ($shouldRun ? "Yes" : "No"));
                return $shouldRun;
            },
            'Personalizada' => function($task) use ($now) {
                // Log::info("Checking custom task: {$task->task_name} on {$task->custom_date} at {$task->execution_time}. Current time: " . $now->format('H:i:s'));
                $shouldRun = $now->toDateString() === $task->custom_date && $now->format('H:i') === Carbon::parse($task->execution_time)->format('H:i');
                // Log::info("Should run: " . ($shouldRun ? "Yes" : "No"));
                return $shouldRun;
            },
        ];
    
        foreach ($tasks as $task) {
            // Log::info("Evaluating task: {$task->task_name}, Frequency: {$task->frequency}");
            if (isset($frequencyChecks[$task->frequency]) && $frequencyChecks[$task->frequency]($task)) {
                // Log::info("Executing task: {$task->task_name}");
                $actionInstance = app($task->action, ['scheduledTask' => $task]);
                $actionInstance->handle();
            } else {
                // Log::info("No task executed for: {$task->task_name}");
            }
        }
    }
    
    

    public function executeImmediately()
    {
        $actionInstance = app($this->action, ['scheduledTask' => $this]);
        $actionInstance->handle();
    }    
    
}
