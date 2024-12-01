<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\DataTransferObjects\CourseDTO;
use App\DataTransferObjects\UserDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class Academusoft extends Model
{
    use HasFactory;

    // protected static $connectionName = 'mysql_academusoft';
    protected static $connectionName = 'oracle_academusoft';

    /**
     * Valida la conexión a la base de datos MySQL de Academusoft.
     *
     * @return bool Retorna true si la conexión es válida, o false en caso de fallo.
     */
    public static function validatedConnection()
    {
        try {           

            DB::connection(self::$connectionName)->getPdo();
            return true;
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
    }

    public static function getUsers(): Collection
    {
        $teachers = self::getTeachers();
        
        $students = self::getStudents();

        $users = $teachers->merge($students);

        return $users;
    }
    
    private static function getStudents(): Collection
    {
        try 
        {
            $registros = DB::connection(self::$connectionName)
                ->table('ACADEMICO.V_BRIGHTSPACE_ESTUDIANTES')
                ->select(
                    'identificador_estudiante',
                    'codigo_estudiante',
                    'primer_nombre',
                    'segundo_nombre',
                    'primer_apellido',
                    'segundo_apellido',
                    'tipo_documento',
                    'numero_documento',
                    'correo_electronico_institucion',
                    'direccion',
                    'telefono',
                    'sexo',
                    'nombre_programa',
                    'id_programa',
                    'modalidad'
                )
                ->get();
    
            return $registros->map(function ($estudiante) {          
                
                return new UserDTO(
                    $estudiante->primer_nombre,
                    $estudiante->primer_apellido,
                    $estudiante->tipo_documento,
                    $estudiante->numero_documento,
                    $estudiante->correo_electronico_institucion,
                    "ESTUDIANTE",
                    $estudiante->identificador_estudiante,
                    $estudiante->codigo_estudiante,
                    $estudiante->direccion,
                    $estudiante->telefono,
                    $estudiante->sexo,
                    $estudiante->nombre_programa,
                    $estudiante->id_programa,
                    $estudiante->modalidad
                );
            });
        } catch (Exception $e) {
            return collect();
        }
    }

    private static function getTeachers(): Collection
    {
        try 
        {
            $registros = DB::connection(self::$connectionName)
                ->table('ACADEMICO.V_BRIGHTSPACE_DOCENTES')
                ->select(
                    'tipo_doc',
                    'documento',
                    'nombres',
                    'apellidos',
                    'mail_institucional'
                )
                ->get();
    
            return $registros->map(function ($docente) {

                return new UserDTO(
                    $docente->nombres,
                    $docente->apellidos,
                    $docente->tipo_doc,
                    $docente->documento,
                    $docente->mail_institucional,
                    "DOCENTE"
                );
            });
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return collect();
        }
    }

    public static function getCourses(): Collection
    {
        try {            
            $results = DB::connection(self::$connectionName)->table('courses')
                ->select('id', 'name', 'code')
                ->get();
            
            return $results->map(function ($course) {
                $templateId = "66" . $course->id;
                return new CourseDTO($templateId, $course->name, $course->code);
            });        
    
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return collect();
        }
    }
       
    
}
