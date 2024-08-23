<?php

namespace App\Actions;

use App\Contracts\HasModel;
use App\DataTransferObjects\CourseDTO;
use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\CourseCreationDetail;
use Exception;

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

        try {
            // Verificar conexión con Academusoft y BrightSpace
            if (!$this->verifyConnections([
                Academusoft::class => 'Academusoft',
                BrightSpace::class => 'BrightSpace'
            ])) {
                return;
            }

            $courses = Academusoft::getCourses();
    
            $existingCodes = CourseCreationDetail::whereIn('code', $courses->pluck('code'))->pluck('code')->toArray();
    
            $newCourseDetails = $courses->map(function (CourseDTO $course) use ($existingCodes, &$existingCoursesCount) {
                $isExisting = in_array($course->code, $existingCodes);
                
                if ($isExisting) {
                    $existingCoursesCount++;
                    $this->logTask(false, "El curso con código {$course->code} ya existe y no fue creado nuevamente.");
                }
                
                return [
                    'course' => $course->name,
                    'code' => $course->code,
                    'TemplateId' => $course->templateId,
                    'scheduled_task_id' => $this->scheduledTask->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
            })->toArray();
    
            if (!empty($newCourseDetails)) {

                foreach ($newCourseDetails as $courseDetail) {
                    CourseCreationDetail::updateOrCreate(
                        ['code' => $courseDetail['code']], 
                        $courseDetail
                    );
                }
                $createdCoursesCount = count($newCourseDetails) - $existingCoursesCount;
            }
                
            $details = "Sincronización de cursos completada con éxito. Se crearon {$createdCoursesCount} cursos. 
            Cursos ya existentes (fallos): {$existingCoursesCount}.";
            $this->logTask(true, $details);

        } catch (Exception $e) {
            $details = 'Error al sincronizar cursos: ' . $e->getMessage();
            $this->logTask(false, $details);
        }
    }
}
