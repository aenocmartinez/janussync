<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ScheduledTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'frequency',
        'custom_time',
        'custom_date',
    ];

    /**
     * Obtener los registros del log de ejecuciones asociados a esta tarea programada.
     */
    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class);
    }

    /**
     * Verifica si la tarea está activa.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Obtén la próxima fecha de ejecución basada en la frecuencia.
     */
    public function getNextExecutionDate()
    {
        $now = Carbon::now();

        switch ($this->frequency) {
            case 'Diaria':
                return $now->addDay();
            case 'Semanal':
                return $now->addWeek();
            case 'Mensual':
                return $now->addMonth();
            case 'Anual':
                return $now->addYear();
            case 'Quincenal':
                return $now->addDays(15);
            case 'Personalizada':
                return Carbon::createFromFormat('Y-m-d H:i:s', "{$this->custom_date} {$this->custom_time}");
            default:
                return null;
        }
    }
}
