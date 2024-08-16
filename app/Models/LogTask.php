<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_task_id',
        'executed_at',
        'was_successful',
        'details',
    ];

    public function scheduledTask()
    {
        return $this->belongsTo(ScheduledTask::class);
    }
}
