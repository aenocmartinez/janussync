<?php

namespace App\Models;

use App\Contracts\LMS;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\DataTransferObjects\GradeDTO;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BrightSpace extends Model implements LMS
{
    use HasFactory;

    protected static $connectionName = 'mysql_brightspace';

    private static $allCourses = [];

    public static function validatedConnection(): bool
    {
        $config = config('brightspace');

        $host = $config['host'];
        $port = $config['port'];
        $scheme = $config['scheme'];
        $appKey = $config['app_key'];
        $appId = $config['app_id'];
        $userKey = $config['user_key'];
        $userId = $config['user_id'];
        $libPath = $config['libpath'];

        require_once $libPath . '/D2LAppContextFactory.php';
        require_once $libPath . '/D2LHostSpec.php';

        $authContextFactory = new \D2LAppContextFactory();
        $authContext = $authContextFactory->createSecurityContext($appId, $appKey);
        $hostSpec = new \D2LHostSpec($host, $port, $scheme);

        try 
        {
            $userContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);
            $uri = $userContext->createAuthenticatedUri("/d2l/api/lp/1.0/users/whoami", "GET");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uri);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) 
            {
                return true; 
            } 
            else 
            {
                Log::error("Error al conectar con BrightSpace: HTTP Code " . $httpCode);
                return false;
            }
        } catch (Exception $e) {
            Log::error("Excepción al conectar con BrightSpace: " . $e->getMessage());
            return false;
        }
    }  
    
    public static function createUsers($users = []) : bool
    {
        $config = config('brightspace');
    
        $host = $config['host'];
        $port = $config['port'];
        $scheme = $config['scheme'];
        $appKey = $config['app_key'];
        $appId = $config['app_id'];
        $userKey = $config['user_key'];
        $userId = $config['user_id'];
        $libPath = $config['libpath'];
        
    
        require_once $libPath . '/D2LAppContextFactory.php';
        require_once $libPath . '/D2LHostSpec.php';
    
        $authContextFactory = new \D2LAppContextFactory();
        $authContext = $authContextFactory->createSecurityContext($appId, $appKey);
        $hostSpec = new \D2LHostSpec($host, $port, $scheme);
    
        try {
            $userContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);
            $urlBase = $userContext->createAuthenticatedUri("/d2l/api/lp/1.0/users/", "POST");
    
            foreach ($users as $user) {
                
                $externalEmail = !empty($user['email']) ? $user['email'] : null;

                $roleId = $config['default_role_id']; //estudiante
                if ($user['role'] == "DOCENTE") 
                {
                    $roleId = '109';
                }
                
    
                $postData = [
                    "OrgDefinedId" => $user['email'], 
                    "FirstName" => trim($user['first_name']), 
                    "MiddleName" => "",
                    "LastName" => trim($user['last_name']), 
                    "ExternalEmail" => $externalEmail, 
                    "UserName" => strtolower($user['first_name'] . '.' . $user['last_name']),
                    "RoleId" => $roleId,
                    "IsActive" => 1,
                    // "SendCreationEmail" => !is_null($externalEmail) // Enviar correo de creación si hay un email válido
                    "SendCreationEmail" => false
                ];

                // Log::info(json_encode($postData));
    
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlBase);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
    
                if ($httpCode != 201) {
                    Log::error("Error al crear el usuario " . $user['email'] . ": HTTP Code " . $httpCode . " - " . $response);
                    continue;
                }
            }
    
            return true;
            
        } catch (Exception $e) {
            Log::error("Excepción al crear usuarios en BrightSpace: " . $e->getMessage());
            return false;
        }
    }    

    // public static function createCourses($courses = []): bool 
    // {
    //     $config = config('brightspace');
    
    //     $host = $config['host'];
    //     $port = $config['port'];
    //     $scheme = $config['scheme'];
    //     $appKey = $config['app_key'];
    //     $appId = $config['app_id'];
    //     $userKey = $config['user_key'];
    //     $userId = $config['user_id'];
    //     $libPath = $config['libpath'];
        
    
    //     require_once $libPath . '/D2LAppContextFactory.php';
    //     require_once $libPath . '/D2LHostSpec.php';
    
    //     $authContextFactory = new \D2LAppContextFactory();
    //     $authContext = $authContextFactory->createSecurityContext($appId, $appKey);
    //     $hostSpec = new \D2LHostSpec($host, $port, $scheme);
    
    //     try {
    //         $userContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);
    //         $urlBase = $userContext->createAuthenticatedUri("/d2l/api/lp/1.43/courses/", "POST");
    
    //         foreach ($courses as $item) {
                
    //             $postData = [
    //                 "Name" => $item['course'], 
    //                 "Code" => $item['code'],
    //                 "Path" => "", 
    //                 "CourseTemplateId" => $item['TemplateId'], 
    //                 "SemesterId" => null, 
    //                 "StartDate" => null, 
    //                 "EndDate" => null,
    //                 "LocaleId" => null, 
    //                 "ForceLocale" => true, 
    //                 "ShowAddressBook" => true, 
    //                 "Description" => null, 
    //                 "CanSelfRegister" => null,
    //             ];

    //             // Log::info(json_encode($postData));
    
    //             $ch = curl_init();
    //             curl_setopt($ch, CURLOPT_URL, $urlBase);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_POST, true);
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    //             $response = curl_exec($ch);
    //             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //             curl_close($ch);
    
    //             if ($httpCode != 200) {
    //                 Log::error("Error el curso " . $item['course'] . ": HTTP Code " . $httpCode . " - " . $response);
    //                 continue;
    //             }
    //         }
    
    //         return true;
            
    //     } catch (Exception $e) {
    //         Log::error("Excepción al crear cursos en BrightSpace: " . $e->getMessage());
    //         return false;
    //     }
    // }   

    public static function createCourses($courses = [], $outputFormat = 'json'): bool
    {
        foreach ($courses as $course) {
            // Organizar los cursos en la estructura deseada
            self::organizeCourses($course);
        }

        // Decidir si generar un archivo JSON o Markmap según el formato
        if ($outputFormat === 'json') {
            return self::createJsonFile();  // Generar archivo JSON
        } else if ($outputFormat === 'markmap') {
            return self::createMarkmapFile();  // Generar archivo Markmap
        }

        // Si no se pasa un formato válido, retorna false
        return false;
    }

    private static function organizeCourses($course)
    {
        // Verifica si $course es un array y convierte a un objeto si es necesario
        if (is_array($course)) {
            $course = (object) $course;  // Convierte el array en un objeto
        }
    
        // Validar y asignar valores por defecto a todos los campos necesarios
        self::validateCourseFields($course);
    
        $jsonStructure = &self::$allCourses; // Referencia al array estático
    
        // Agregar Sede si no existe
        if (!isset($jsonStructure[$course->sede])) {
            $jsonStructure[$course->sede] = [];
        }
    
        // Agregar Facultad si no existe
        if (!isset($jsonStructure[$course->sede][$course->facultad])) {
            $jsonStructure[$course->sede][$course->facultad] = [];
        }
    
        // Agregar Modalidad si no existe
        if (!isset($jsonStructure[$course->sede][$course->facultad][$course->modalidad])) {
            $jsonStructure[$course->sede][$course->facultad][$course->modalidad] = [];
        }
    
        // Agregar Nivel Educativo si no existe
        if (!isset($jsonStructure[$course->sede][$course->facultad][$course->modalidad][$course->nivelEducativo])) {
            $jsonStructure[$course->sede][$course->facultad][$course->modalidad][$course->nivelEducativo] = [];
        }
    
        // Agregar Tipo de Curso si no existe
        if (!isset($jsonStructure[$course->sede][$course->facultad][$course->modalidad][$course->nivelEducativo][$course->tipoPeriodoID])) {
            $jsonStructure[$course->sede][$course->facultad][$course->modalidad][$course->nivelEducativo][$course->tipoPeriodoID] = [];
        }
    
        // Agregar Materia y Periodo (sin el campo 'material')
        $jsonStructure[$course->sede][$course->facultad][$course->modalidad][$course->nivelEducativo][$course->tipoPeriodoID][$course->programa] = [
            $course->periodoAcademico => [
                $course->ubicacion_semestral => []
            ]
        ];
    }        
    
    private static function validateCourseFields($course)
    {
        // Validar campos y asignar valores por defecto si están vacíos
        $defaults = [
            'sede' => 'Sin sede',
            'facultad' => 'Sin facultad',
            'modalidad' => 'Sin modalidad',
            'nivelEducativo' => 'Sin nivel educativo',
            'tipoPeriodoID' => 'Sin tipo de periodo',
            'materia' => 'Sin materia',
            'periodoAcademico' => 'SIN PERIODO',
            'ubicacionSemestralMateria' => 'Sin ubicación semestral',
            'material' => 'material.txt', // Valor por defecto para el material
        ];
    
        // Iterar sobre los campos y asignar valores predeterminados si no existen
        foreach ($defaults as $field => $defaultValue) {
            if (!isset($course->$field) || empty($course->$field)) {
                $course->$field = $defaultValue;
            }
        }
    }
    
    
    // Método para crear el archivo JSON
    public static function createJsonFile(): bool
    {
        // Convertir la estructura acumulada a JSON
        $jsonContent = json_encode(self::$allCourses, JSON_PRETTY_PRINT);

        // Definir la ruta del archivo JSON en la raíz del proyecto
        $filePath = base_path('courses_sync.json'); // base_path() obtiene la raíz del proyecto en Laravel

        try {
            // Escribir el archivo JSON en la raíz del proyecto
            file_put_contents($filePath, $jsonContent);
        } catch (Exception $e) {
            Log::error("Error al crear el archivo JSON: " . $e->getMessage());
            return false;
        }

        // Retornar true si todo salió bien
        return true;
    }

    public static function createMarkmapFile(): bool
    {
        // Generar la estructura Markmap a partir de los datos organizados
        $markmapContent = self::generateMarkmap(self::$allCourses);
    
        // Definir la ruta del archivo Markdown en la raíz del proyecto
        $filePath = base_path('courses_sync.markmap.md'); // base_path() obtiene la raíz del proyecto en Laravel
    
        try {
            // Escribir el archivo Markdown en la raíz del proyecto
            file_put_contents($filePath, $markmapContent);
        } catch (Exception $e) {
            Log::error("Error al crear el archivo Markdown: " . $e->getMessage());
            return false;
        }
    
        // Retornar true si todo salió bien
        return true;
    }
    
    
    private static function generateMarkmap($courses)
    {
        // Iniciar el contenido sin el encabezado de configuración Markmap
        $content = "";
    
        // Agregar los cursos en formato Markdown con la jerarquía hasta el tercer nivel
        foreach ($courses as $sede => $faculties) {
            $content .= "## $sede\n";  // Agregar sede como nodo principal
            foreach ($faculties as $facultad => $modalities) {
                $content .= "  ### $facultad\n";  // Agregar facultad
                foreach ($modalities as $modalidad => $levels) {
                    $content .= "    #### $modalidad\n";  // Agregar modalidad
                }
            }
        }
    
        return $content;
    }    

    public static function getGrades($term = 1): Collection
    {
        try {
            $results = DB::connection(self::$connectionName)
                ->table('grades')
                ->select('id', 'user_id', 'course_id', 'grade', 'grade_type', 'term_number')
                ->where('term_number', '=', $term)
                ->get();

            return $results->map(function ($grade) {
                return GradeDTO::fromObject($grade);
            });

        } catch (Exception $e) {
            Log::info($e->getMessage());
            return collect();
        }
    }
}
