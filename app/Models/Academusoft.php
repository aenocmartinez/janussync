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
            Log::info("Entra a la función getCourses()");
    
            // 1️⃣ Consultar la tabla de estructura (cursos)
            $sqlEstructura = "
                SELECT 
                    SEDE AS sede, 
                    FACULTAD AS facultad, 
                    METODOLOGIA AS metodologia, 
                    NIVEL_EDUCATIVO AS nivel_educativo, 
                    MODALIDAD AS modalidad, 
                    TIPO_PERIODO_ID AS tipo_periodo_id, 
                    UBICACION_SEMESTRAL_MATERIA AS ubicacion_semestral, 
                    MATERIA AS materia
                FROM ACADEMICO.V_BRIGHTSPACE_ESTRUCTURA
            ";
    
            Log::info("Ejecutando consulta de ESTRUCTURA...");
            $estructura = DB::connection(self::$connectionName)->select($sqlEstructura);
            Log::info("Se obtuvieron " . count($estructura) . " registros en ESTRUCTURA.");
    
            // 2️⃣ Consultar la tabla de periodos académicos
            $sqlPeriodos = "
                SELECT 
                    TIPO_PERIODO_ID, 
                    ANNIO || '-' || PERIODO || ' ' || TRIM(TIPO_PERIODO) AS periodo_academico
                FROM ACADEMICO.V_BRIGHTSPACE_PERIODOS
            ";
    
            Log::info("Ejecutando consulta de PERIODOS...");
            $periodos = DB::connection(self::$connectionName)->select($sqlPeriodos);
            Log::info("Se obtuvieron " . count($periodos) . " registros en PERIODOS.");
    
            // 3️⃣ Crear índice de periodos por `TIPO_PERIODO_ID`
            $periodosIndexados = [];
            foreach ($periodos as $p) {
                $periodosIndexados[$p->tipo_periodo_id] = $p->periodo_academico;
            }
    
            // 4️⃣ Unir los resultados en PHP asegurando valores válidos
            $cursos = collect();
            $cursosExcluidos = collect();
    
            foreach ($estructura as $curso) {
                if (!isset($curso->materia) || trim($curso->materia) === '') {
                    $cursosExcluidos->push((array) $curso);
                    continue;
                }
    
                $tipoPeriodoId = isset($curso->tipo_periodo_id) ? (int) $curso->tipo_periodo_id : 0;
                $periodo_academico = isset($periodosIndexados[$tipoPeriodoId]) ? trim($periodosIndexados[$tipoPeriodoId]) : 'SIN PERIODO';
                
                // Verificar que `periodo_academico` nunca sea NULL o vacío antes de crear el DTO
                if (empty($periodo_academico)) {
                    Log::warning("Curso excluido porque `periodo_academico` es vacío o NULL.", (array) $curso);
                    $cursosExcluidos->push((array) $curso);
                    continue;
                }                
    
                $cursos->push(new CourseDTO(
                    6628, 
                    trim($curso->sede ?? 'SIN SEDE'),
                    trim($curso->facultad ?? 'SIN FACULTAD'),
                    trim($curso->metodologia ?? 'SIN METODOLOGIA'),
                    trim($curso->nivel_educativo ?? 'SIN NIVEL EDUCATIVO'),
                    trim($curso->modalidad ?? 'SIN MODALIDAD'),
                    $tipoPeriodoId,
                    trim($curso->ubicacion_semestral ?? 'SIN UBICACIÓN SEMESTRAL'),
                    trim($curso->materia ?? 'SIN MATERIA'),
                    trim($periodo_academico)
                ));
            }
    
            // Log::info("Cursos construidos correctamente.", ['total' => $cursos->count()]);
    
            if ($cursosExcluidos->isNotEmpty()) {
                Log::warning("Cursos excluidos por datos inválidos.", $cursosExcluidos->toArray());
            }
    
            return $cursos;
        } catch (Exception $e) {
            Log::error("Error inesperado en getCourses(): " . $e->getMessage());
            return collect();
        }
    }
           
}
