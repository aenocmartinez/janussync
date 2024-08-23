<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\DataTransferObjects\CourseDTO;
use App\DataTransferObjects\UserDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class Academusoft extends Model
{
    use HasFactory;

    protected static $connectionName = 'mysql_academusoft';

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
            return false;
        }
    }

    public static function getUsers(): Collection
    {
        try {

            $results = DB::connection(self::$connectionName)->table('users')
                ->select('first_name', 'last_name', 'email')
                ->get();
        
            return $results->map(function ($user) {
                $fullName = $user->first_name . ' ' . $user->last_name;
                return new UserDTO($fullName, $user->email);
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
