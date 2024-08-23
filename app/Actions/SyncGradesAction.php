<?php

namespace App\Actions;

use App\Contracts\HasModel;
use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\GradeCreateDetail;
use App\Models\UserCreationDetail;
use Exception;
use Illuminate\Support\Facades\Log;

class SyncGradesAction extends SyncActionBase implements HasModel
{
    public static function getModelClass(): string
    {
        return GradeCreateDetail::class;
    }

    public function handle()
    {
        $details = '';
        $failedCount = 0;
        $createdCount = 0;
    
        try {
            if (!$this->verifyConnections([
                Academusoft::class => 'Academusoft',
                BrightSpace::class => 'BrightSpace'
            ])) {
                return;
            }
    
            $grades = BrightSpace::getGrades($this->scheduledTask->term_number);
    
            $userEmailMap = UserCreationDetail::whereIn('email', function($query) use ($grades) {
                $query->select('email')
                      ->from('user_creation_details')
                      ->whereIn('id', $grades->pluck('user_id')->toArray());
            })->pluck('email', 'id')->toArray();
    
            $existingRecords = GradeCreateDetail::whereIn('user_id', $grades->pluck('user_id'))
                ->whereIn('course_id', $grades->pluck('course_id'))
                ->where('term_number', $this->scheduledTask->term_number)
                ->get()
                ->keyBy(function ($item) {
                    return $item['user_id'] . '-' . $item['course_id'] . '-' . $item['term_number'];
                });
    
            $newRecords = [];
    
            foreach ($grades as $grade) {
                if (!isset($userEmailMap[$grade->user_id])) {
                    $details = "No se pudo crear el registro para user_id {$grade->user_id}, 
                                course_id {$grade->course_id}, 
                                term_number {$grade->term_number}: Usuario no encontrado.";
                    $this->logTask(true, $details); // Registra como fallo
                    $failedCount++;
                    continue;
                }
    
                $recordKey = $grade->user_id . '-' . $grade->course_id . '-' . $this->scheduledTask->term_number;
    
                if (isset($existingRecords[$recordKey])) {
                    $details = "No se pudo crear el registro para user_id {$grade->user_id},
                                 course_id {$grade->course_id}, 
                                 term_number {$grade->term_number}: El registro ya existe.";
                    $this->logTask(true, $details); // Registra como fallo
                    $failedCount++;
                    continue;
                }
    
                $newRecords[] = [
                    'course_id' => $grade->course_id,
                    'user_id' => $grade->user_id,
                    'grade' => $grade->grade,
                    'scheduled_task_id' => $this->scheduledTask->id,
                    'term_number' => $this->scheduledTask->term_number,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                $createdCount++;
            }
    
            if (!empty($newRecords)) {
                GradeCreateDetail::insert($newRecords);
            }
    
            $details = "Sincronización de calificaciones completada con éxito. 
                        Registros creados: {$createdCount}. Registros con fallas: {$failedCount}.";
            $this->logTask(true, $details);
    
        } catch (Exception $e) {
            $details = 'Error al sincronizar calificaciones: ' . $e->getMessage();
            Log::info($e->getMessage());
            $this->logTask(false, $details);
        }
    }
        
    // public function handle()
    // {
    //     $details = '';
    //     $failedCount = 0;
    //     $createdCount = 0;

    //     try {
    //         // Verificar conexión con Academusoft y BrightSpace
    //         if (!$this->verifyConnections([
    //             Academusoft::class => 'Academusoft',
    //             BrightSpace::class => 'BrightSpace'
    //         ])) {
    //             return;
    //         }

    //         $grades = BrightSpace::getGrades($this->scheduledTask->term_number);

    //         // Obtener el mapeo de user_id a email desde UserCreationDetail
    //         $userEmailMap = UserCreationDetail::whereIn('email', function($query) use ($grades) {
    //             $query->select('email')
    //                   ->from('user_creation_details')
    //                   ->whereIn('id', $grades->pluck('user_id')->toArray());
    //         })->pluck('email', 'id')->toArray();

    //         $existingRecords = GradeCreateDetail::whereIn('user_id', $grades->pluck('user_id'))
    //             ->whereIn('course_id', $grades->pluck('course_id'))
    //             ->where('term_number', $this->scheduledTask->term_number)
    //             ->get()
    //             ->keyBy(function ($item) {
    //                 return $item['user_id'] . '-' . $item['course_id'] . '-' . $item['term_number'];
    //             });

    //         $newRecords = [];

    //         foreach ($grades as $grade) {
    //             // Verificar si el user_id existe en el mapeo de email
    //             if (!isset($userEmailMap[$grade->user_id])) {
    //                 $details = "No se pudo crear el registro para user_id {$grade->user_id}, 
    //                             course_id {$grade->course_id}, 
    //                             term_number {$grade->term_number}: Usuario no encontrado.";
    //                 $this->logTask(true, $details);
    //                 $failedCount++;
    //                 continue;
    //             }

    //             $recordKey = $grade->user_id . '-' . $grade->course_id . '-' . $this->scheduledTask->term_number;

    //             if (isset($existingRecords[$recordKey])) {
    //                 $details = "No se pudo crear el registro para user_id {$grade->user_id},
    //                              course_id {$grade->course_id}, 
    //                              term_number {$grade->term_number}: El registro ya existe.";
    //                 $this->logTask(true, $details);
    //                 $failedCount++;
    //                 continue;
    //             }

    //             $newRecords[] = [
    //                 'course_id' => $grade->course_id,
    //                 'user_id' => $grade->user_id,
    //                 'grade' => $grade->grade,
    //                 'scheduled_task_id' => $this->scheduledTask->id,
    //                 'term_number' => $this->scheduledTask->term_number,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];

    //             $createdCount++;
    //         }

    //         if (!empty($newRecords)) {
    //             GradeCreateDetail::insert($newRecords);
    //         }

    //         $details = "Sincronización de calificaciones completada con éxito. 
    //                     Registros creados: {$createdCount}. Registros con fallas: {$failedCount}.";
    //         $this->logTask(true, $details);

    //     } catch (Exception $e) {
    //         $details = 'Error al sincronizar calificaciones: ' . $e->getMessage();
    //         Log::info($e->getMessage());
    //         $this->logTask(false, $details);
    //     }
    // }
}
