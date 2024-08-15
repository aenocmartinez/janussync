<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    use HasFactory;

    protected $fillable = ['task_name', 'frequency', 'execution_time', 'day_of_week', 'day_of_month', 'month'];
}
