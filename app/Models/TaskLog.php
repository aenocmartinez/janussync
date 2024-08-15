<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_task_id',
        'status',
        'completed_at',
        'details',
    ];

    /**
     * Relación con la tarea programada.
     */
    public function scheduledTask()
    {
        return $this->belongsTo(ScheduledTask::class);
    }
}
