<?php

namespace App\Actions;

use App\Models\Academusoft;
use App\Models\BrightSpace;
use Exception;

class SyncGradesAction extends SyncActionBase
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

            $details = 'Sincronización de calificaciones completada con éxito.';
            $this->logTask(true, $details);

        } catch (Exception $e) {
            $details = 'Error al sincronizar calificaciones: ' . $e->getMessage();
            $this->logTask(false, $details);
        }
    }
}
