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

    // public static function getUsers(): Collection
    // {
    //     try {

    //         $results = DB::connection(self::$connectionName)->table('users')
    //             ->select('first_name', 'last_name', 'email')
    //             ->get();
        
    //         return $results->map(function ($user) {
    //             $fullName = $user->first_name . ' ' . $user->last_name;
    //             return new UserDTO($fullName, $user->email);
    //         });
        
    
    //     } catch (Exception $e) {
    //         Log::info($e->getMessage());            
    //         return collect();
    //     }
    // }

    public static function getUsers(): Collection
    {
        try {
            // Ejecutar el query
            $results = DB::connection(self::$connectionName)
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

            // Log::info($results);
    
            return $results->map(function ($user) {
                
                // $fullName = trim($user->PRIMER_NOMBRE . ' ' . $user->SEGUNDO_NOMBRE . ' ' . $user->PRIMER_APELLIDO . ' ' . $user->SEGUNDO_APELLIDO);
                
                return new UserDTO(
                    $user->primer_nombre,
                    $user->primer_apellido,
                    $user->correo_electronico_institucion,
                    $user->identificador_estudiante,
                    $user->codigo_estudiante,
                    $user->tipo_documento,
                    $user->numero_documento,
                    $user->direccion,
                    $user->telefono,
                    $user->sexo,
                    $user->nombre_programa,
                    $user->id_programa,
                    $user->modalidad
                );
            });
        } catch (Exception $e) {
            Log::info("AQUI: " . $e->getMessage());
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
