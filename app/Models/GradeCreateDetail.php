<?php

namespace App\Models;

use App\Contracts\HasPartialView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeCreateDetail extends Model implements HasPartialView
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grade_create_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'user_id',
        'grade',        
        'scheduled_task_id',
        'term_number',
    ];    

    public function logTasks()
    {
        return $this->hasMany(LogTask::class, 'scheduled_task_id', 'scheduled_task_id')
                    ->where('scheduled_task_id', $this->scheduled_task_id);
    }
        
    public static function getPartialViewName(): string
    {
        return 'monitoring.partials.grade_details';
    }
}
