<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

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
}
