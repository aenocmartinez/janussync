<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\DataTransferObjects\UserDTO;
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

    public static function getUsers()
    {
        try {
            $results = DB::connection(self::$connectionName)->table('users as u')
                ->join('partners as p', function ($join) {
                    $join->on('u.email', '=', 'p.email')
                         ->where('p.type_partner', '=', 'PN')
                         ->where('u.status', '=', 'active');
                })
                ->select('u.name', 'u.email')
                ->get();
                
            return $results->map(function ($user) {
                return new UserDTO($user->name, $user->email);
            });
    
        } catch (Exception $e) {
            Log::info($e->getMessage());            
            return collect();
        }
    }
    
}
