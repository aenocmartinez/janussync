<?php

namespace App\Actions;

use App\DataTransferObjects\CourseDTO;
use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\CourseCreationDetail;
use Exception;

class SyncCoursesAction extends SyncActionBase
{
    public function handle()
    {
        $details = '';
        $createdCoursesCount = 0;

        try {
            // Verificar conexión con Academusoft
            if (!$this->isConnected(Academusoft::class, 'Academusoft')) {  
                $this->logTask(false, "Error de conexión con Academusoft.");
                return;
            }

            // Verificar conexión con BrightSpace
            if (!$this->isConnected(BrightSpace::class, 'BrightSpace')) {
                $this->logTask(false, "Error de conexión con BrightSpace.");
                return;
            }

            $courses = Academusoft::getCourses();
    
            $existingCodes = CourseCreationDetail::whereIn('code', $courses->pluck('code'))->pluck('code')->toArray();
    
            $newCourseDetails = $courses->filter(function (CourseDTO $course) use ($existingCodes) {
                return !in_array($course->templateId, $existingCodes);
            })->map(function (CourseDTO $course) {
                return [
                    'course' => $course->name,
                    'code' => $course->code,
                    'TemplateId' => $course->templateId,
                    'scheduled_task_id' => $this->scheduledTask->id,
                ];
            })->toArray();
    
            if (!empty($newCourseDetails)) {
                CourseCreationDetail::insert($newCourseDetails);
                $createdCoursesCount = count($newCourseDetails);
            }
    
            $details = "Sincronización de cursos completada con éxito. Se crearon {$createdCoursesCount} cursos.";
            $this->logTask(true, $details);

        } catch (Exception $e) {
            $details = 'Error al sincronizar cursos: ' . $e->getMessage();
            $this->logTask(false, $details);
        }
    }
}
