<?php

namespace App\Actions;

use App\Models\Academusoft;
use App\Models\BrightSpace;
use Exception;

class SyncUsersAction extends SyncActionBase
{
    public function handle()
    {
        $details = '';

        try {
            // Verificar conexión con Academusoft
            if (!$this->isConnected(Academusoft::class, 'Academusoft')) {
                return;
            }

            // Verificar conexión con BrightSpace
            if (!$this->isConnected(BrightSpace::class, 'BrightSpace')) {
                return;
            }

            $details = 'Sincronización de usuarios completada con éxito.';
            $this->logTask(true, $details);

        } catch (Exception $e) {
            $details = 'Error al sincronizar usuarios: ' . $e->getMessage();
            $this->logTask(false, $details);
        }
    }
}
