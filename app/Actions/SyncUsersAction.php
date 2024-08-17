<?php

namespace App\Actions;

use App\Models\LogTask;
use App\Models\ScheduledTask;
use Carbon\Carbon;
use Exception;

class SyncUsersAction
{
    protected $scheduledTask;

    public function __construct(ScheduledTask $scheduledTask)
    {
        $this->scheduledTask = $scheduledTask;
    }

    public function handle()
    {
        $wasSuccessful = false;
        $details = '';

        try {
            
            sleep(30);

            $wasSuccessful = true;
            $details = 'Sincronización de usuarios completada con éxito.';
        } catch (Exception $e) {
            $wasSuccessful = false;
            $details = 'Error al sincronizar usuarios: ' . $e->getMessage();
        }

        LogTask::create([
            'scheduled_task_id' => $this->scheduledTask->id,
            'executed_at' => Carbon::now(),
            'was_successful' => $wasSuccessful,
            'details' => $details,
        ]);
    }
}
