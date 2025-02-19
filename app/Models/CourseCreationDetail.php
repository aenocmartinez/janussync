<?php

namespace App\Models;

use App\Contracts\HasPartialView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCreationDetail extends Model implements HasPartialView
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'course_creation_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'programa',
        'brightspace_template_id',
        'sede',
        'facultad',
        'nivel_educativo',
        'modalidad',
        'tipo_periodo_id',
        'ubicacion_semestral',
        'scheduled_task_id',
    ];    

    /**
     * Get the log tasks related to this course.
     */
    public function logTasks()
    {
        return $this->hasMany(LogTask::class, 'scheduled_task_id', 'scheduled_task_id');
    }

    /**
     * Get the scheduled task associated with this course.
     */
    public function scheduledTask()
    {
        return $this->belongsTo(ScheduledTask::class, 'scheduled_task_id');
    }

    /**
     * Get the partial view name for rendering.
     *
     * @return string
     */
    public static function getPartialViewName(): string
    {
        return 'monitoring.partials.course_details';
    }
}
