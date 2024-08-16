<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            'task_name' => $this->task_name,
            'status' => $latestLog ? ($latestLog->was_successful ? 'Completado' : 'Tarea Fallida') : 'Programada',
            'status_boolean' => $latestLog ? $latestLog->was_successful : false,
            'execution_time' => $latestLog ? $latestLog->executed_at : 'N/A', 
            'frequency' => $this->frequency,
            'details' => $latestLog ? $latestLog->details : 'Esta tarea se ejecutará el ' . $date_execution,
            'log_id' => $latestLog ? $latestLog->id : 'N/A',
        ];
    }
    

    public static function getAllLastExecutions()
    {
        return self::all()->map(function ($task) {
            return $task->getLastExecutionDetails();
        });
    }  
}
