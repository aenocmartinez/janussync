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
        'term_number',
        'custom_date',
        'action', 
    ];

    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class);
    }   
    
    public function getLastExecutionDetails()
    {
        $latestLog = $this->taskLogs()->latest('executed_at')->first();
        Carbon::setLocale('es');

        // Determinar la ejecución programada o la última ejecución real
        if ($latestLog) {
            $execution_time = Carbon::parse($latestLog->executed_at)
                ->setTimezone('America/Bogota') // Solo para fechas de ejecución real
                ->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i \h\o\r\a\s');
        } elseif ($this->execution_time) {
            $execution_time = Carbon::parse($this->execution_time)
                ->translatedFormat('H:i \h\o\r\a\s'); // No cambiar zona horaria
        } else {
            $execution_time = 'N/A';
        }

        return [
            'task_id' => $this->id,
            'task_name' => $this->task_name,
            'status' => $latestLog ? ($latestLog->was_successful ? 'Completado' : 'Tarea Fallida') : 'Programada',
            'status_boolean' => $latestLog ? $latestLog->was_successful : false,
            'execution_time' => $execution_time,
            'frequency' => $this->frequency,
            'details' => $latestLog ? $latestLog->details : "Esta tarea está programada para ejecutarse a las $execution_time",
            'log_id' => $latestLog ? $latestLog->id : 'N/A',
            'term_number' => $this->term_number,
            'action' => $this->action,
        ];
    }

    public function execute()
    {
        if (!$this->action) {
            return ['success' => false, 'details' => 'No se encontró acción asociada.'];
        }

        $action = app($this->action, ['scheduledTask' => $this]); 
        $result = $action->handle();  

        $this->taskLogs()->create([
            'was_successful' => $result['success'],
            'details' => $result['details'],
            'executed_at' => now(),
        ]);

        return $result;
    }

    public static function getAllLastExecutions()
    {
        return self::with('taskLogs')->get()->map(fn($task) => $task->getLastExecutionDetails());
    }

    public static function countLastExecutionResults()
    {
        $lastLogs = self::with(['taskLogs' => function($query) {
            $query->latest('executed_at');
        }])->get()->map(fn($task) => $task->taskLogs->first())->filter();

        $totalLogs = $lastLogs->count();
        $successfulLogs = $lastLogs->where('was_successful', true)->count();
        $failedLogs = $lastLogs->where('was_successful', false)->count();

        $successPercentage = $totalLogs > 0 ? round(($successfulLogs / $totalLogs) * 100) : 0;
        $failurePercentage = $totalLogs > 0 ? round(($failedLogs / $totalLogs) * 100) : 0;

        // Ajustar si la suma no es 100%
        $difference = 100 - ($successPercentage + $failurePercentage);
        if ($difference) {
            $successPercentage > $failurePercentage ? $successPercentage += $difference : $failurePercentage += $difference;
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

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                if ($reflection->isInstantiable() && $reflection->hasMethod('handle')) {
                    $actions[$className] = Str::headline(Str::replace('Action', '', $file->getFilename()));
                }
            }
        }

        return $actions;
    }

    public static function checkAndRunScheduledTasks()
    {
        $tasks = self::all();
        $now = Carbon::now()->setTimezone('America/Bogota');

        foreach ($tasks as $task) {
            if ($task->shouldRunNow($now)) {
                Log::info("Ejecutando tarea: {$task->task_name}");
                $task->execute();
            } else {
                Log::info("No se ejecutó tarea: {$task->task_name}");
            }
        }
    }

    private function shouldRunNow(Carbon $now)
    {
        $executionTime = Carbon::parse($this->execution_time, 'America/Bogota')->format('H:i');
    
        return match ($this->frequency) {
            'Diaria' => $now->format('H:i') === $executionTime,
            'Semanal' => $now->format('l') === $this->day_of_week && $now->format('H:i') === $executionTime,
            'Mensual' => $now->day == $this->day_of_month && $now->format('H:i') === $executionTime,
            'Personalizada' => $this->custom_date && $now->toDateString() === $this->custom_date && $now->format('H:i') === $executionTime,
            default => false,
        };
    }    

    public function executeImmediately()
    {
        Log::info("Ejecución inmediata de la tarea: {$this->task_name}");
        return $this->execute();
    }  
}
