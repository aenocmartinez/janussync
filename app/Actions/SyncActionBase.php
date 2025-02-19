<?php

namespace App\Actions;

use App\Models\LogTask;
use App\Models\ScheduledTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class SyncActionBase
{
    protected $scheduledTask;

    public function __construct(ScheduledTask $scheduledTask)
    {
        $this->scheduledTask = $scheduledTask;
    }

    /**
     * Verifica la conexión con un servicio y registra un log en caso de falla.
     *
     * @param string $serviceClass
     * @param string $serviceName
     * @return bool
     */
    protected function isConnected(string $serviceClass, string $serviceName): bool
    {
        if (!$serviceClass::validatedConnection()) {
            $this->logTask(false, "Conexión fallida con {$serviceName}");
            return false;
        }
        return true;
    }

    /**
     * Verifica las conexiones con múltiples servicios y registra log en caso de falla.
     *
     * @param array $services Array de servicios en formato ['ServiceClass' => 'ServiceName']
     * @return bool
     */
    protected function verifyConnections(array $services): bool
    {
        foreach ($services as $serviceClass => $serviceName) {
            if (!$this->isConnected($serviceClass, $serviceName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Registra el log de la tarea.
     *
     * @param bool $wasSuccessful
     * @param string $details
     * @return void
     */
    protected function logTask(bool $wasSuccessful, string $details): void
    {
        $details = Str::limit($details, 2000, '... (mensaje truncado)');
            
        LogTask::create([
            'scheduled_task_id' => $this->scheduledTask->id,
            'executed_at' => Carbon::now(),
            'was_successful' => $wasSuccessful,
            'details' => $details,
        ]);
    }
}
