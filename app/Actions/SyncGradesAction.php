<?php

namespace App\Actions;

use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\GradeCreateDetail;
use Exception;
use Illuminate\Support\Facades\Log;

class SyncGradesAction extends SyncActionBase
{
    public function handle()
    {
        $details = '';
    
        try {

            if (!$this->verifyConnections()) {
                return;
            }
    
            $grades = BrightSpace::getGrades($this->scheduledTask->term_number);
    
            $createdCount = $grades->count();
    
            foreach ($grades as $grade) {
                GradeCreateDetail::create([
                    'course_id' => $grade->course_id,
                    'user_id' => $grade->user_id,
                    'grade' => $grade->grade,
                    'scheduled_task_id' => $this->scheduledTask->id,
                    'term_number' => $this->scheduledTask->term_number,
                ]);
            }
    
            $details = "Sincronización de calificaciones completada con éxito. Registros creados: {$createdCount}.";
            $this->logTask(true, $details);
    
        } catch (Exception $e) {
            $details = 'Error al sincronizar calificaciones: ' . $e->getMessage();
            Log::info($e->getMessage());
            $this->logTask(false, $details);
        }
    }
    
    /**
     * Verifica las conexiones con Academusoft y BrightSpace.
     *
     * @return bool
     */
    private function verifyConnections(): bool
    {
        if (!$this->isConnected(Academusoft::class, 'Academusoft')) {  
            $this->logTask(false, "Error de conexión con Academusoft.");
            return false;
        }

        if (!$this->isConnected(BrightSpace::class, 'BrightSpace')) {
            $this->logTask(false, "Error de conexión con BrightSpace.");
            return false;
        }

        return true;
    }    
}
