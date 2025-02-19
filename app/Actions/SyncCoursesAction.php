<?php

namespace App\Actions;

use App\DataTransferObjects\CourseDTO;
use App\Models\CourseCreationDetail;
use App\Contracts\HasModel;
use App\Models\BrightSpace;
use App\Models\Academusoft;
use Exception;
use Illuminate\Support\Facades\Log;

class SyncCoursesAction extends SyncActionBase implements HasModel
{
    public static function getModelClass(): string
    {
        return CourseCreationDetail::class;
    }

    public function handle()
    {
        $details = '';
        $createdCoursesCount = 0;
        $existingCoursesCount = 0;
        $excludedCoursesCount = 0;
        $excludedCoursesList = [];
    
        try {
            // Verificar conexión con Academusoft y BrightSpace
            if (!$this->verifyConnections([
                Academusoft::class => 'Academusoft',
                BrightSpace::class => 'BrightSpace'
            ])) {
                return;
            }
    
            // Obtener los cursos desde Academusoft
            $courses = Academusoft::getCourses()->unique('materia'); // Evitar duplicados
    
            // Filtrar cursos con materia vacía
            $filteredCourses = $courses->filter(fn(CourseDTO $course) => !empty($course->materia));
    
            // Identificar cursos sin materia
            $excludedCourses = $courses->reject(fn(CourseDTO $course) => !empty($course->materia));
            $excludedCoursesCount = $excludedCourses->count();
            $excludedCoursesList = $excludedCourses->map(fn(CourseDTO $course) => "{$course->facultad} - {$course->materia}")->toArray();
    
            // Registrar en logs los cursos excluidos
            if ($excludedCoursesCount > 0) {
                Log::warning("Cursos excluidos por no tener materia:", $excludedCourses->toArray());
            }
    
            // Obtener cursos existentes por programa
            $existingPrograms = CourseCreationDetail::whereIn('programa', $filteredCourses->pluck('materia'))
                ->pluck('programa')
                ->toArray();
    
            // Transformar cursos a formato de inserción
            $newCourseDetails = $filteredCourses
                ->reject(fn(CourseDTO $course) => in_array($course->materia, $existingPrograms)) // Evitar insertar duplicados
                ->map(function (CourseDTO $course) {
                    return [
                        'programa' => $course->materia,
                        'brightspace_template_id' => $course->templateId ?? 0,
                        'sede' => $course->sede,
                        'facultad' => $course->facultad,
                        'nivel_educativo' => $course->nivelEducativo,
                        'modalidad' => $course->modalidad,
                        'tipo_periodo_id' => $course->tipoPeriodoID,
                        'ubicacion_semestral' => $course->ubicacionSemestralMateria,
                        'scheduled_task_id' => $this->scheduledTask->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->values() // Reindexar para evitar problemas
                ->toArray();
    
            if (!empty($newCourseDetails)) {
                // Llama a la conexión con BrightSpace para crear cursos
                // BrightSpace::createCourses($newCourseDetails);
    
                // Insertar registros en batch para mejorar rendimiento
                CourseCreationDetail::insert($newCourseDetails);
                $createdCoursesCount = count($newCourseDetails);
            }
    
            // Construcción del mensaje de ejecución
            $details = "Sincronización de cursos completada con éxito. Se crearon {$createdCoursesCount} cursos. 
            Cursos ya existentes: " . count($existingPrograms) . ".";
    
            if ($excludedCoursesCount > 0) {
                $excludedCoursesString = implode(', ', array_slice($excludedCoursesList, 0, 5));
                if ($excludedCoursesCount > 5) {
                    $excludedCoursesString .= "... y otros " . ($excludedCoursesCount - 5) . " cursos.";
                }
                $details .= " {$excludedCoursesCount} cursos fueron omitidos porque no tenían materia: {$excludedCoursesString}";
            }
    
            $this->logTask(true, $details);
    
        } catch (Exception $e) {
            $details = 'Error al sincronizar cursos: ' . $e->getMessage();
            $this->logTask(false, $details);
        }
    }
    
}
